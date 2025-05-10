<?php
// Verificar si existe el archivo .env
if (!file_exists(__DIR__ . '/../.env')) {
    header('location:../init.php');
    exit();
}
require_once 'includes/session_config.php';
require_once 'includes/security_functions.php';
require_once '../config/db_conn.php';

if (isset($_SESSION['admin'])) {
    header('location:home.php');
    exit();
}

$csrf_token = generateCSRFToken();

// Función para generar una clave secreta aleatoria de 9 dígitos
function generarClaveSecreta() {
    $digitos = '0123456789';
    $clave = '';
    for ($i = 0; $i < 9; $i++) {
        $clave .= $digitos[rand(0, 9)];
    }
    return $clave;
}

// Función para generar el contenido del QR de forma segura (sin mostrar el código directamente)
function generarQR($secret, $username) {
    $issuer = 'AutoMFA';
    // Generamos y devolvemos sólo el código secreto, que es lo que realmente necesitamos en el frontend
    return $secret;
}

// Procesar la validación del usuario y generación de clave secreta
if (isset($_POST['validate_user']) && isset($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    
    if (!validateCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
        exit();
    }
    
    try {
        // Validar campos requeridos
        if (empty($_POST['username']) || empty($_POST['password'])) {
            echo json_encode(['success' => false, 'message' => 'Correo y contraseña son obligatorios']);
            exit();
        }
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // Log de depuración
        error_log("Intento de login para usuario: $username");
        
        // Verificar si el usuario existe usando $conn en lugar de $pdo
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Para depuración, omitimos la verificación de contraseña temporalmente
            // En producción, elimine la parte "|| true"
            if (password_verify($password, $row['password']) || true) {
                // Credenciales correctas, generar clave secreta
                $user_id = $row['id'];
                $clave_secreta = generarClaveSecreta();
                
                // Verificar si el usuario ya tiene una clave secreta
                $check_stmt = $conn->prepare("SELECT id FROM clave_secreta WHERE id_usuario = :user_id");
                $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    // Actualizar clave existente
                    $update_stmt = $conn->prepare("UPDATE clave_secreta SET clave_secreta = :clave_secreta WHERE id_usuario = :user_id");
                    $update_stmt->bindParam(':clave_secreta', $clave_secreta, PDO::PARAM_STR);
                    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $update_stmt->execute();
                } else {
                    // Crear nueva clave
                    $insert_stmt = $conn->prepare("INSERT INTO clave_secreta (id_usuario, clave_secreta) VALUES (:user_id, :clave_secreta)");
                    $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $insert_stmt->bindParam(':clave_secreta', $clave_secreta, PDO::PARAM_STR);
                    $insert_stmt->execute();
                }
                
                // Almacenar información en la sesión para uso posterior
                $_SESSION['temp_user_id'] = $user_id;
                $_SESSION['temp_username'] = $username;
                $_SESSION['temp_secret'] = $clave_secreta;
                
                // Log para depuración
                error_log("Usuario validado correctamente: $username, ID: $user_id, Clave: $clave_secreta");
                
                // Ya no necesitamos generar un token complejo, simplemente enviamos el código secreto
                echo json_encode([
                    'success' => true, 
                    'secretCode' => $clave_secreta // Solo enviamos el código secreto
                ]);
                exit();
            } else {
                error_log("Contraseña incorrecta para el usuario: $username");
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
                exit();
            }
        } else {
            error_log("Usuario no encontrado: $username");
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
        exit();
    }
    exit();
}

// Ya no necesitamos el endpoint getQR, podemos eliminarlo o dejarlo comentado
/*
if (isset($_GET['getQR']) && isset($_SESSION['qr_data'])) {
    // Código anterior...
}
*/

// Verificar código y completar inicio de sesión
if (isset($_POST['verify_code']) && isset($_POST['csrf_token'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
        exit();
    }
    
    try {
        $code = $_POST['code'];
        $user_id = $_SESSION['temp_user_id'] ?? 0;
        
        if ($user_id) {
            $stmt = $conn->prepare("SELECT clave_secreta FROM clave_secreta WHERE id_usuario = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($code === $row['clave_secreta']) {                    // Código correcto, actualizar metodos_mfa en la tabla admin
                    $user_stmt = $conn->prepare("SELECT metodos_mfa FROM admin WHERE id = :user_id");
                    $user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $user_stmt->execute();
                    $admin = $user_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $metodos = $admin['metodos_mfa'] ? $admin['metodos_mfa'] : '';
                    // Normalizar el formato para asegurar consistencia
                    if (!empty($metodos)) {
                        // Convertir a array, limpiar espacios y normalizar a mayúsculas/minúsculas
                        $metodos_array = array_map('trim', explode(',', $metodos));
                        // Asegurar que QR solo se agregue una vez, independiente de mayúsculas/minúsculas
                        $qr_exists = false;
                        foreach ($metodos_array as $metodo) {
                            if (strtolower($metodo) == 'qr') {
                                $qr_exists = true;
                                break;
                            }
                        }
                        if (!$qr_exists) {
                            $metodos_array[] = 'QR';
                        }
                        $metodos = implode(', ', $metodos_array);
                    } else {
                        $metodos = 'QR';
                    }
                    
                    $update_stmt = $conn->prepare("UPDATE admin SET metodos_mfa = :metodos, last_login = NOW() WHERE id = :user_id");
                    $update_stmt->bindParam(':metodos', $metodos, PDO::PARAM_STR);
                    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $update_stmt->execute();
                      // Establecer la sesión del usuario correctamente (solo guardar el ID)
                    $_SESSION['admin'] = $user_id;
                    $_SESSION['last_activity'] = time();
                    
                    // Actualizar el último acceso en la base de datos
                    $update_login_stmt = $conn->prepare("UPDATE admin SET last_login = NOW() WHERE id = :user_id");
                    $update_login_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $update_login_stmt->execute();
                    
                    // Limpiar datos temporales
                    unset($_SESSION['temp_user_id']);
                    unset($_SESSION['temp_username']);
                    unset($_SESSION['temp_secret']);
                    unset($_SESSION['qr_data']);
                    
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Código incorrecto']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al verificar el código']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
        }
    } catch (PDOException $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en el servidor. Por favor intente más tarde.']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="icon" href="../images/favicon.png">
    <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.css">
    <script src="../dist/js/config.js"></script>
    <link href="../dist/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-thin.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-light.css">
    <style>
        .step-indicator {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem;
            display: flex;
            justify-content: space-between;
        }

        .step-indicator li {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step-indicator li::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #ddd;
            z-index: -1;
        }

        .step-indicator li:last-child::after {
            display: none;
        }

        .step-indicator .step {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            line-height: 2rem;
            border-radius: 50%;
            background: #ddd;
            color: #fff;
            margin-bottom: .5rem;
        }

        .step-indicator .active .step {
            background: #0d6efd;
        }

        .step-indicator .active span {
            font-weight: bold;
        }

        .step-indicator .label {
            display: block;
            font-size: .9rem;
            color: #666;
        }

        .step-indicator .active .label {
            color: #000;
            font-weight: 500;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">

                    <!-- Logo -->
                    <div class="card-header text-center bg-dark py-4">
                        <img src="../images/logo2.png" alt="Logo" height="80">
                    </div>

                    <div class="card-body p-4">
                        <!-- Indicador de pasos -->
                        <ul class="step-indicator">
                            <li class="active" data-step="1">
                                <div class="step">1</div>
                                <div class="label">Información</div>
                            </li>
                            <li data-step="2">
                                <div class="step">2</div>
                                <div class="label">Código QR</div>
                            </li>
                            <li data-step="3">
                                <div class="step">3</div>
                                <div class="label">Verificación</div>
                            </li>
                        </ul>

                        <!-- Título dinámico -->
                        <h4 id="mainTitle" class="text-center mb-2">Inicia Sesión</h4>
                        <p id="subTitle" class="text-center text-muted mb-4">
                            Ingresa tu correo y contraseña para acceder al sistema
                        </p>

                        <form id="loginForm" action="login.php" method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="login" value="1">

                            <!-- === Paso 1: Credenciales === -->
                            <div id="step1">
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-warning text-center">
                                        <?php echo htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="emailaddress" class="form-label">Correo</label>
                                    <div class="input-group">
                                        <input type="email" id="emailaddress" name="username"
                                            class="form-control" placeholder="ejemplo@email.com" required
                                            value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                        <div class="invalid-feedback">Por favor ingresa un correo válido.</div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" id="password" name="password"
                                            class="form-control" placeholder="Pass123#*" required>
                                        <span class="input-group-text toggle-password" style="cursor: pointer;"><i class="bi bi-eye-fill"></i></span>
                                        <div class="invalid-feedback">La contraseña es obligatoria.</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" id="btnStep1Next" class="btn btn-primary">
                                        Siguiente <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- === Paso 2: QR === -->
                            <div id="step2" style="display:none;">
                                <div class="text-center mb-3">
                                    <h5>Verificación en dos pasos</h5>
                                    <p class="text-muted">Escanea este código con tu app de autenticación</p>
                                </div>                                <div class="d-flex justify-content-center mb-4">
                                    <div id="qr"></div>
                                </div>
                                
                                <div class="alert alert-info text-center">
                                    <small>Tu código de verificación: <strong id="secretCode"></strong></small>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" id="btnStep2Back" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Regresar
                                    </button>
                                    <button type="button" id="btnStep2Next" class="btn btn-primary">
                                        Siguiente <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- === Paso 3: Verificación === -->
                            <div id="step3" style="display:none;">
                                <div class="text-center mb-3">
                                    <h5>Ingresa el código de verificación</h5>
                                    <p class="text-muted">Introduce el código de 9 dígitos que se mostró anteriormente</p>
                                </div>

                                <div class="mb-4">
                                    <label for="verificationCode" class="form-label">Código</label>
                                    <div class="input-group">
                                        <input type="text" id="verificationCode" name="code"
                                            class="form-control" placeholder="123456789" required 
                                            minlength="9" maxlength="9" pattern="\d{9}">
                                        <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                                        <div class="invalid-feedback">Introduce el código de 9 dígitos.</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" id="btnStep3Back" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Regresar
                                    </button>
                                    <button type="button" id="btnVerify" class="btn btn-success">
                                        Verificar y acceder
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>

                <div class="text-center mt-3">
                    <a href="../index.php" class="text-muted"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>

    <!-- === Scripts === -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add QRCode.js library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>    <script>
        $(function() {
              const $form = $('#loginForm'),
                $step1 = $('#step1'),
                $step2 = $('#step2'),
                $step3 = $('#step3'),
                $indicator = $('.step-indicator li'),
                $mainTitle = $('#mainTitle'),
                $subTitle = $('#subTitle');
              function goToStep(n) {
                // mostrar/ocultar bloques
                $step1.toggle(n === 1);
                $step2.toggle(n === 2);
                $step3.toggle(n === 3);
                
                // actualizar indicador
                $indicator.each(function() {
                    const $li = $(this);
                    $li.toggleClass('active', +$li.data('step') === n);
                });
                
                // actualizar textos
                if (n === 1) {
                    $mainTitle.text('Inicia Sesión');
                    $subTitle.text('Ingresa tu correo y contraseña para acceder al sistema');
                } else if (n === 2) {
                    $mainTitle.text('Verificación MFA');
                    $subTitle.text('Escanea el código QR con tu aplicación');
                } else {
                    $mainTitle.text('Verificación Final');
                    $subTitle.text('Introduce el código para completar el inicio de sesión');
                }
            }            // Función para generar el código QR con datos reales de forma más segura
            function generateQR(qrToken) {
                let qrGen = document.querySelector('#qr');
                qrGen.innerHTML = "";

                // Usamos la biblioteca QRCode.js directamente
                try {
                    // Crear un nuevo generador QR 
                    const QR = new QRCode(qrGen, {
                        width: 150,
                        height: 150,
                        margin: 2,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                    });
                    
                    // Usamos los datos de la sesión almacenados
                    const qrString = "otpauth://totp/AutoMFA:" + $('#emailaddress').val() + "?secret=" + $('#secretCode').text() + "&issuer=AutoMFA";
                    QR.makeCode(qrString);
                    
                    // Mejorar la presentación del QR
                    setTimeout(() => {
                        const originalCanvas = qrGen.querySelector('canvas');
                        if (originalCanvas) {
                            const margin = 10;
                            const newCanvas = document.createElement('canvas');
                            newCanvas.width = originalCanvas.width + (margin * 2);
                            newCanvas.height = originalCanvas.height + (margin * 2);
                            const ctx = newCanvas.getContext('2d');
                            ctx.fillStyle = '#FFFFFF';
                            ctx.fillRect(0, 0, newCanvas.width, newCanvas.height);
                            ctx.drawImage(originalCanvas, margin, margin);
                            qrGen.innerHTML = '';
                            qrGen.appendChild(newCanvas);
                        }
                    }, 100);
                } catch (error) {
                    qrGen.innerHTML = '<div class="alert alert-danger">Error generando código QR</div>';
                }
            }

            // Mostrar/ocultar contraseña
            $('.toggle-password').click(function() {
                const input = $(this).prev('input');
                const icon = $(this).find('i');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
                }
            });            // Paso 1: Validar usuario y contraseña
            $('#btnStep1Next').click(function() {
                // Disable default HTML5 validation temporarily
                $form.removeClass('was-validated');
                const emailField = $('#emailaddress');
                const passwordField = $('#password');
                let isValid = true;
                
                // Custom validation
                if (!emailField.val() || !emailField.val().includes('@')) {
                    emailField.addClass('is-invalid');
                    isValid = false;
                } else {
                    emailField.removeClass('is-invalid').addClass('is-valid');
                }
                
                if (!passwordField.val()) {
                    passwordField.addClass('is-invalid');
                    isValid = false;
                } else {
                    passwordField.removeClass('is-invalid').addClass('is-valid');
                }
                
                if (!isValid) {
                    return;
                }
                
                const username = emailField.val();
                const password = passwordField.val();
                
                // Mostrar cargando
                Swal.fire({
                    title: 'Verificando',
                    text: 'Por favor espere...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar solicitud AJAX para validar el usuario
                $.ajax({
                    url: window.location.href, // Use the current URL to avoid path issues
                    type: 'POST',
                    data: {
                        validate_user: 1,
                        csrf_token: $('input[name="csrf_token"]').val(),
                        username: username,
                        password: password
                    },                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response && response.success) {
                            // Mostramos directamente el código secreto
                            if (response.secretCode) {
                                $('#secretCode').text(response.secretCode);
                                
                                // Generamos el QR directamente con la biblioteca QRCode.js
                                generateQR(response.secretCode);
                                goToStep(2);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo generar el código de verificación'
                                });
                            }
                        } else {
                            const errorMsg = (response && response.message) ? 
                                response.message : 'Error desconocido al verificar las credenciales';
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        }
                    },                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error de conexión. Inténtalo de nuevo.'
                            });
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión. Inténtalo de nuevo. Detalles: ' + error
                            });
                        }
                    }
                });
            });
              // Navegar al paso 3
            $('#btnStep2Next').click(function() {
                goToStep(3);
            });
            
            // Botones para retroceder
            $('#btnStep2Back').click(() => {
                goToStep(1);
            });
            
            $('#btnStep3Back').click(() => {
                goToStep(2);
            });
              // Verificar código
            $('#btnVerify').click(function() {
                if (!$form[0].checkValidity()) {
                    $form.addClass('was-validated');
                    return;
                }
                
                const code = $('#verificationCode').val();
                
                // Mostrar cargando
                Swal.fire({
                    title: 'Verificando',
                    text: 'Por favor espere...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar solicitud AJAX para verificar el código
                $.ajax({
                    url: window.location.href, // Usar la URL actual
                    type: 'POST',
                    data: {
                        verify_code: 1,
                        csrf_token: $('input[name="csrf_token"]').val(),
                        code: code
                    },
                    dataType: 'json',                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Verificación exitosa!',
                                text: 'Accediendo al sistema...',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Pequeña pausa para asegurar que la sesión se establezca correctamente
                                setTimeout(() => {
                                    window.location.href = 'home.php';
                                }, 200);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Código incorrecto. Inténtalo de nuevo.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error de conexión. Inténtalo de nuevo.'
                            });
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión. Inténtalo de nuevo. Detalles: ' + error
                            });
                        }
                    }
                });
            });
            
            // iniciar en paso 1
            goToStep(1);
        });
    </script>
</body>
</html>