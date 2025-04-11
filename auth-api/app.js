const express = require('express');
const dotenv = require('dotenv');
const bcrypt = require('bcrypt');
const { body, validationResult } = require('express-validator');
const jwt = require('jsonwebtoken');
const pool = require('./config/db');

dotenv.config();

const app = express();
app.use(express.json());

// Validaciones para el endpoint POST /api/register
const validateUsuario = [
  body('nombre_usuario').notEmpty().withMessage('El nombre es requerido'),
  body('apellido_usuario').notEmpty().withMessage('El apellido es requerido'),
  body('correo_usuario').isEmail().withMessage('El correo debe ser válido'),
  body('password')
    .isLength({ min: 6 }).withMessage('La contraseña debe tener al menos 6 caracteres'),
  body('metodos_mfa').optional().isString().withMessage('Métodos MFA debe ser una cadena'),
  body('tipo_usuario').notEmpty().withMessage('El tipo de usuario es requerido'),
];

// Validaciones para el endpoint POST /api/login
const validateLogin = [
  body('correo_usuario').isEmail().withMessage('El correo debe ser válido'),
  body('password').notEmpty().withMessage('La contraseña es requerida'),
];

//--------------------------------------------LISTAR USUARIOS--------------------------------------------//
// GET /api/usuario
app.get('/api/usuario', async (req, res) => {
  try {
    const [rows] = await pool.query('SELECT * FROM usuario');
    res.json(rows);
  } catch (err) {
    console.error('Error al obtener usuarios:', err);
    res.status(500).json({ message: 'Error en el servidor' });
  }
});

//--------------------------------------------REGISTRAR Y HASHEAR LA PASSWORD--------------------------------------------//
// POST /api/usuario
app.post('/api/usuario', validateUsuario, async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  const {
    nombre_usuario,
    apellido_usuario,
    correo_usuario,
    password,
    metodos_mfa,
    tipo_usuario,
  } = req.body;

  try {
    // Verificar si el usuario ya existe
    const [existingUser] = await pool.query(
      'SELECT * FROM usuario WHERE correo_usuario = ?',
      [correo_usuario]
    );
    if (existingUser.length > 0) {
      return res.status(400).json({ message: 'El usuario ya existe' });
    }

    // Hashear la contraseña
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insertar el nuevo usuario
    const [result] = await pool.query(
      `INSERT INTO usuario(nombre_usuario, apellido_usuario, correo_usuario, password, metodos_mfa, tipo_usuario)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [nombre_usuario, apellido_usuario, correo_usuario, hashedPassword, metodos_mfa, tipo_usuario]
    );

    // Generar un token JWT
    const token = jwt.sign(
      { id: result.insertId, correo_usuario },
      process.env.JWT_SECRET || 'your_jwt_secret',
      { expiresIn: '1h' }
    );

    res.status(201).json({
      message: 'Usuario creado correctamente',
      id: result.insertId,
      token,
    });
  } catch (err) {
    console.error('Error al insertar usuario:', err);
    res.status(500).json({ message: 'Error al crear el usuario' });
  }
});

//--------------------------------------------LOGIN--------------------------------------------//
// POST /api/login
app.post('/api/login', validateLogin, async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  const { correo_usuario, password } = req.body;

  try {
    // Buscar al usuario por correo
    const [users] = await pool.query(
      'SELECT id, correo_usuario, password FROM usuario WHERE correo_usuario = ?',
      [correo_usuario]
    );

    if (users.length === 0) {
      return res.status(401).json({ message: 'Credenciales inválidas' });
    }

    const user = users[0];

    // Comparar la contraseña
    const passwordMatch = await bcrypt.compare(password, user.password);
    if (!passwordMatch) {
      return res.status(401).json({ message: 'Credenciales inválidas' });
    }

    // Generar un token JWT
    const token = jwt.sign(
      { id: user.id, correo_usuario: user.correo_usuario },
      process.env.JWT_SECRET || 'clave_secreta',
      { expiresIn: '1h' } // Tiempo de expiracion del token
    );

    res.json({
      message: 'Inicio de sesión exitoso',
      token,
    });
  } catch (err) {
    console.error('Error al iniciar sesión:', err);
    res.status(500).json({ message: 'Error en el servidor' });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Servidor corriendo en puerto ${PORT}`));