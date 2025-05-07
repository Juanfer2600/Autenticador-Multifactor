const express = require("express");
const dotenv = require("dotenv");
const bcrypt = require("bcrypt");
const { body, validationResult } = require("express-validator");
const jwt = require("jsonwebtoken");
const pool = require("./config/db");
const qrcode = require("qrcode");
const { authenticator } = require("otplib");
const rateLimit = require("express-rate-limit");
const expressSanitizer = require("express-sanitizer");

// Carga .env desde la raíz del proyecto
dotenv.config();

// Verificar variables de entorno
const requiredEnvVars = ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME', 'DB_PORT', 'JWT_SECRET'];
const missingEnvVars = requiredEnvVars.filter((varName) => !process.env[varName]);
if (missingEnvVars.length > 0) {
    console.error(`Error: Faltan las siguientes variables de entorno: ${missingEnvVars.join(', ')}`);
    process.exit(1);
}

const app = express();
app.use(express.json());
app.use(expressSanitizer()); // Middleware para sanitización

// Middleware para verificar el token JWT
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers["authorization"];
    const token = authHeader && authHeader.split(" ")[1];

    if (token == null) return res.status(401).json({ message: "Token no proporcionado" });

    jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
        if (err) {
            console.error("Error al verificar el token:", err);
            return res.status(403).json({ message: "Token inválido" });
        }
        req.user = user;
        next();
    });
};

// Middleware para verificar la contraseña
const verifyPassword = async (req, res, next) => {
    const { password } = req.body;
    if (!password) {
        return res.status(400).json({ message: "La contraseña es requerida para esta operación" });
    }

    try {
        const [users] = await pool.query(
            "SELECT password FROM usuario WHERE correo_usuario = ?",
            [req.user.correo_usuario]
        );
        if (users.length === 0 || !(await bcrypt.compare(password, users[0].password))) {
            return res.status(401).json({ message: "Contraseña incorrecta" });
        }
        next();
    } catch (err) {
        console.error("Error al verificar la contraseña:", err);
        res.status(500).json({ message: "Error en el servidor: " + err.message });
    }
};

// Validaciones para el endpoint POST /api/register
const validateUsuario = [
    body("nombre_usuario").notEmpty().withMessage("El nombre es requerido"),
    body("apellido_usuario").notEmpty().withMessage("El apellido es requerido"),
    body("correo_usuario").isEmail().withMessage("El correo debe ser válido"),
    body("password")
        .isLength({ min: 6 })
        .withMessage("La contraseña debe tener al menos 6 caracteres"),
    body("metodo_mfa")
        .optional()
        .isInt({ min: 1 })
        .withMessage("El ID del método MFA debe ser un número válido")
        .custom(async (value) => {
            if (value) {
                const [methods] = await pool.query("SELECT id_metodo FROM metodos_mfa WHERE id_metodo = ?", [value]);
                if (methods.length === 0) {
                    throw new Error("El ID del método MFA no existe");
                }
            }
            return true;
        }),
    body("tipo_usuario")
        .notEmpty()
        .withMessage("El tipo de usuario es requerido"),
];

// Validaciones para el endpoint POST /api/login
const validateLogin = [
    body("correo_usuario").isEmail().withMessage("El correo debe ser válido"),
    body("password").notEmpty().withMessage("La contraseña es requerida"),
];

// Validaciones para el endpoint POST /api/login_qr
const validateLoginQR = [
    body("correo_usuario").isEmail().withMessage("El correo debe ser válido"),
    body("otp_code").notEmpty().withMessage("El código OTP es requerido"),
    body("metodo_mfa")
        .isInt({ min: 1 })
        .withMessage("El ID del método MFA debe ser un número válido")
        .custom(async (value) => {
            const [methods] = await pool.query("SELECT id_metodo FROM metodos_mfa WHERE id_metodo = ?", [value]);
            if (methods.length === 0) {
                throw new Error("El ID del método MFA no existe");
            }
            return true;
        }),
];

// Aplicar el límite de velocidad al endpoint de inicio de sesión
const loginLimiter = rateLimit({
    windowMs: 60 * 60 * 1000, // 1 hora
    max: 5, // Límite a 5 intentos por hora
    message: "Demasiados intentos de inicio de sesión. Por favor, inténtalo de nuevo después de una hora.",
    statusCode: 429,
    keyGenerator: (req) => req.body.correo_usuario || req.ip,
});

app.use("/api/login", loginLimiter);
app.use("/api/login_qr", loginLimiter);

//--------------------------------------------LISTAR USUARIOS--------------------------------------------//
app.get("/api/usuario", async (req, res) => {
    try {
        const [rows] = await pool.query("SELECT id, nombre_usuario, apellido_usuario, correo_usuario, tipo_usuario, mfa_secret FROM usuario");
        res.json(rows);
    } catch (err) {
        console.error("Error al obtener usuarios:", err);
        res.status(500).json({ message: "Error en el servidor" });
    }
});

//--------------------------------------------REGISTRAR Y HASHEAR LA PASSWORD--------------------------------------------//
app.post("/api/register", validateUsuario, async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
    }

    const {
        nombre_usuario,
        apellido_usuario,
        correo_usuario,
        password,
        metodo_mfa,
        tipo_usuario,
    } = req.body;

    // Sanitizar entradas
    const sanitizedData = {
        nombre_usuario: req.sanitize(nombre_usuario),
        apellido_usuario: req.sanitize(apellido_usuario),
        correo_usuario: req.sanitize(correo_usuario),
        password,
        metodo_mfa: metodo_mfa ? parseInt(req.sanitize(metodo_mfa)) : null,
        tipo_usuario: req.sanitize(tipo_usuario),
    };

    try {
        // Verificar si el usuario ya existe
        const [existingUser] = await pool.query(
            "SELECT * FROM usuario WHERE correo_usuario = ?",
            [sanitizedData.correo_usuario]
        );
        if (existingUser.length > 0) {
            return res.status(400).json({ message: "El usuario ya existe" });
        }

        // Hashear la contraseña
        const hashedPassword = await bcrypt.hash(sanitizedData.password, 10);

        // Insertar el nuevo usuario (sin metodos_mfa)
        const [result] = await pool.query(
            `INSERT INTO usuario(nombre_usuario, apellido_usuario, correo_usuario, password, tipo_usuario)
            VALUES (?, ?, ?, ?, ?)`,
            [
                sanitizedData.nombre_usuario,
                sanitizedData.apellido_usuario,
                sanitizedData.correo_usuario,
                hashedPassword,
                sanitizedData.tipo_usuario,
            ]
        );

        // Generar un token JWT
        const token = jwt.sign(
            { id: result.insertId, correo_usuario: sanitizedData.correo_usuario },
            process.env.JWT_SECRET,
            { expiresIn: "1h" }
        );

        res.status(201).json({
            message: "Usuario creado correctamente",
            id: result.insertId,
            token,
        });
    } catch (err) {
        console.error("Error al insertar usuario:", err);
        res.status(500).json({ message: "Error al crear el usuario: " + err.message });
    }
});

//--------------------------------------------GENERAR QR PARA MFA Y GUARDAR MFA_SECRET--------------------------------------------//
app.get("/api/qr", authenticateToken, async (req, res) => {
    const correo = req.user.correo_usuario;
    const servicio = "AutenticadorMFA";

    try {
        // Verificar si el usuario ya tiene la MFA configurada
        const [existingSecret] = await pool.query(
            "SELECT mfa_secret FROM usuario WHERE correo_usuario = ?",
            [correo]
        );

        if (existingSecret[0].mfa_secret) {
            return res.status(409).json({
                message: "La MFA ya está configurada para este usuario. Use /api/qr/regenerate para obtener un nuevo código.",
            });
        }

        // Generar clave secreta
        const secret = authenticator.generateSecret();

        // Generar URI TOTP
        const otpauth = authenticator.keyuri(correo, servicio, secret);

        // Generar imagen QR en base64
        const qrDataUrl = await qrcode.toDataURL(otpauth);

        // Actualizar la base de datos del usuario con la clave secreta
        const [updateResult] = await pool.query(
            "UPDATE usuario SET mfa_secret = ? WHERE correo_usuario = ?",
            [secret, correo]
        );

        if (updateResult.affectedRows === 0) {
            return res.status(404).json({ message: "Usuario no encontrado para guardar el mfa_secret" });
        }

        // Enviar QR como JSON
        res.json({ qrCode: qrDataUrl, secret });
    } catch (err) {
        console.error("Error generando QR y mfa_secret:", err);
        res.status(500).json({ message: "Error generando el QR y mfa_secret: " + err.message });
    }
});

//--------------------------------------------REGENERAR QR PARA MFA--------------------------------------------//
app.get("/api/qr/regenerate", authenticateToken, verifyPassword, async (req, res) => {
    const correo = req.user.correo_usuario;
    const servicio = "AutenticadorMFA";

    try {
        // Generar nueva clave secreta
        const newSecret = authenticator.generateSecret();

        // Generar URI TOTP
        const otpauth = authenticator.keyuri(correo, servicio, newSecret);

        // Generar imagen QR en base64
        const qrDataUrl = await qrcode.toDataURL(otpauth);

        // Actualizar la base de datos del usuario con la nueva clave secreta
        const [updateResult] = await pool.query(
            "UPDATE usuario SET mfa_secret = ? WHERE correo_usuario = ?",
            [newSecret, correo]
        );

        if (updateResult.affectedRows === 0) {
            return res.status(404).json({ message: "Usuario no encontrado para regenerar el mfa_secret" });
        }

        res.json({
            qrCode: qrDataUrl,
            secret: newSecret,
            message: "QR regenerado exitosamente. mfa_secret anterior reemplazada.",
        });
    } catch (err) {
        console.error("Error regenerando QR:", err);
        res.status(500).json({ message: "Error regenerando el QR: " + err.message });
    }
});

//--------------------------------------------DESHABILITAR MFA--------------------------------------------//
app.post("/api/mfa/disable", authenticateToken, verifyPassword, async (req, res) => {
    const correo = req.user.correo_usuario;

    try {
        // Eliminar la clave secreta de la base de datos
        const [updateResult] = await pool.query(
            "UPDATE usuario SET mfa_secret = ? WHERE correo_usuario = ?",
            [null, correo]
        );

        if (updateResult.affectedRows === 0) {
            return res.status(404).json({ message: "Usuario no encontrado para deshabilitar mfa_secret" });
        }

        res.json({ message: "MFA deshabilitada exitosamente" });
    } catch (err) {
        console.error("Error al deshabilitar MFA:", err);
        res.status(500).json({ message: "Error al deshabilitar MFA: " + err.message });
    }
});

//--------------------------------------------LOGIN CON CREDENCIALES--------------------------------------------//
app.post("/api/login", validateLogin, async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
    }

    const { correo_usuario, password } = req.body;

    // Sanitizar entradas
    const sanitizedCorreo = req.sanitize(correo_usuario);

    try {
        // Buscar al usuario por correo
        const [users] = await pool.query(
            "SELECT id, correo_usuario, password, mfa_secret FROM usuario WHERE correo_usuario = ?",
            [sanitizedCorreo]
        );

        if (users.length === 0) {
            return res.status(401).json({ message: "Credenciales inválidas" });
        }

        const user = users[0];

        // Comparar la contraseña
        const passwordMatch = await bcrypt.compare(password, user.password);
        if (!passwordMatch) {
            return res.status(401).json({ message: "Credenciales inválidas" });
        }

        // Si el usuario tiene MFA configurado (mfa_secret no es null), requerir OTP
        if (user.mfa_secret) {
            return res.status(400).json({ message: "Inicio de sesión con QR requerido en /api/login_qr" });
        }

        // Generar un token JWT
        const token = jwt.sign(
            { id: user.id, correo_usuario: user.correo_usuario },
            process.env.JWT_SECRET,
            { expiresIn: "1h" }
        );

        res.json({
            message: "Inicio de sesión exitoso",
            token,
        });
    } catch (err) {
        console.error("Error al iniciar sesión:", err);
        res.status(500).json({ message: "Error en el servidor: " + err.message });
    }
});

//--------------------------------------------LOGIN CON QR Y VERIFICACIÓN OTP--------------------------------------------//
app.post("/api/login_qr", validateLoginQR, async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
    }

    const { correo_usuario, otp_code, metodo_mfa } = req.body;

    // Sanitizar entradas
    const sanitizedCorreo = req.sanitize(correo_usuario);
    const sanitizedOtpCode = req.sanitize(otp_code);
    const sanitizedMetodoMfa = parseInt(req.sanitize(metodo_mfa));

    try {
        // Buscar al usuario por correo
        const [users] = await pool.query(
            "SELECT id, correo_usuario, mfa_secret FROM usuario WHERE correo_usuario = ?",
            [sanitizedCorreo]
        );

        if (users.length === 0) {
            return res.status(401).json({ message: "Credenciales inválidas" });
        }

        const user = users[0];

        // Verificar si la MFA está configurada
        if (!user.mfa_secret) {
            return res.status(400).json({ message: "El usuario no tiene MFA configurado" });
        }

        // Verificar que el método MFA sea QR
        const [methods] = await pool.query(
            "SELECT tipo_metodo FROM metodos_mfa WHERE id_metodo = ?",
            [sanitizedMetodoMfa]
        );

        if (methods.length === 0 || methods[0].tipo_metodo !== "QR") {
            return res.status(400).json({ message: "El método MFA especificado no es válido" });
        }

        // Verificar el código OTP
        const isOTPValid = authenticator.verify({
            token: sanitizedOtpCode,
            secret: user.mfa_secret,
        });

        if (!isOTPValid) {
            return res.status(401).json({ message: "Código QR inválido" });
        }

        // Generar un token JWT
        const token = jwt.sign(
            { id: user.id, correo_usuario: user.correo_usuario },
            process.env.JWT_SECRET,
            { expiresIn: "1h" }
        );

        res.json({
            message: "Inicio de sesión exitoso con QR",
            token,
        });
    } catch (err) {
        console.error("Error al iniciar sesión con QR:", err);
        res.status(500).json({ message: "Error en el servidor: " + err.message });
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Servidor corriendo en puerto ${PORT}`));