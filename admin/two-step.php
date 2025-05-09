<?php
require_once 'includes/session_config.php';
require_once 'includes/security_functions.php';
require_once 'includes/functions/mail_functions.php'; // Replace include 'enviar_correo.php' with this line
require_once '../config/db_conn.php'; // Make sure database connection is included

if (isset($_SESSION['admin'])) {
    // Verificar si el admin tiene configurado un método MFA
    $admin_id = $_SESSION['admin'];    $stmt = $conn->prepare("SELECT metodos_mfa FROM admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        if (!empty($row['metodos_mfa'])) {
            // Si ya tiene método MFA configurado, redirigir a home
            header('location:home.php');
            exit();
        }
        // Si no tiene método MFA configurado, continuar en esta página para configurarlo
    } else {
        // Si no se encuentra el admin, cerrar la sesión
        session_unset();
        session_destroy();
        header('location:login.php');
        exit();
    }
}

// Verificar si existe el archivo .env, si no, redirigir al inicializador
if (!file_exists(__DIR__ . '/../.env')) {
    header('location:../init.php');
    exit();
}

// Debug variables
$email_status = '';
$debug_info = [];
$verification_code = '';

// Make sure we have the email in session, or get it from the logged-in user
if (!isset($_SESSION['verification_email']) || empty($_SESSION['verification_email'])) {
    // Si estamos logueados, usar el correo del admin
    if (isset($_SESSION['admin'])) {
        $admin_id = $_SESSION['admin'];
        // Consultar el correo electrónico del admin desde la base de datos
        $stmt = $conn->prepare("SELECT username FROM admin WHERE id = ?");
        $stmt->execute([$admin_id]);
        
        if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['verification_email'] = $row['username']; // This is correct as per your schema
        }
    }
}
$verification_email = $_SESSION['verification_email'] ?? '';

// Check if resend was requested
if (isset($_GET['resend']) && $_GET['resend'] == 'true') {
    // Force resend by unsetting the verification code
    unset($_SESSION['verification_code']);
}

// Only send the email if it hasn't been sent yet or resend was requested
if (!isset($_SESSION['verification_code']) || empty($_SESSION['verification_code'])) {
    if (!empty($verification_email)) {        $destinatarios = [$verification_email];
        $asunto = "Verificación de correo electrónico";
        $codigo_verificacion = '';
        for ($i = 0; $i < 6; $i++) {
            $codigo_verificacion .= rand(1, 9);
        }
        $cuerpo = "Por favor, ingresa el siguiente código de verificación: <strong>$codigo_verificacion</strong>";
        $archivosAdjuntos = []; // Parámetro requerido por la función

        // Try to send the email
        try {
            // Corregir el orden de los parámetros según la definición de la función
            $send_result = enviarCorreo($asunto, $cuerpo, $destinatarios, $archivosAdjuntos);
            
            if ($send_result) {
                $email_status = "Correo enviado exitosamente";
                // Store verification code in session for later validation
                $_SESSION['verification_code'] = $codigo_verificacion;
                $verification_code = $codigo_verificacion;
            } else {
                $email_status = "Error al enviar el correo: " . ($_SESSION['error'] ?? 'Error desconocido');
            }
            
            // Debug info
            $debug_info = [
                'destinatario' => $verification_email,
                'asunto' => $asunto,
                'codigo' => $codigo_verificacion,
                'resultado' => $send_result ? 'Enviado' : 'Fallido',
                'smtp_debug' => $_SESSION['smtp_debug'] ?? [],
                'config_debug' => $_SESSION['debug_info'] ?? []
            ];
        } catch (Exception $e) {
            $email_status = "Error de excepción: " . $e->getMessage();
            $debug_info = [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    } else {
        $email_status = "Error: No se encontró dirección de correo electrónico para verificación";
    }
} else {
    $verification_code = $_SESSION['verification_code'];
    $email_status = "Utilizando código previamente enviado";
    // Debug info for existing code
    $debug_info = [
        'destinatario' => $verification_email,
        'codigo' => $verification_code,
        'resultado' => 'Código existente'
    ];
}

$csrf_token = generateCSRFToken();
// Disable debugging for production - setting to false hides the debug panel
$show_debug = false;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de Correo Electrónico</title>
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
</head>

<body class="authentication-bg position-relative">
    <?php /* Debug panel is now commented out
    <?php if ($show_debug): ?>
    <!-- Debug information panel -->
    <div class="alert <?php echo isset($_SESSION['verification_code']) ? 'alert-success' : 'alert-danger'; ?> mb-0">
        <h5>Debug: Estado del correo electrónico</h5>
        <p><strong>Estado:</strong> <?php echo $email_status; ?></p>
        <p><strong>Email:</strong> <?php echo $verification_email; ?></p>
        <p><strong>Código:</strong> <?php echo $verification_code; ?></p>
        <?php if (!empty($debug_info)): ?>
        <div>
            <p><strong>Detalles:</strong></p>
            <pre><?php echo print_r($debug_info, true); ?></pre>
        </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <p><strong>Error:</strong> <?php echo $_SESSION['error']; ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    */ ?>    <!-- auth page content -->
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-8 col-lg-9">
                    <div class="card">
                        <!-- Logo -->
                        <div class="card-header text-center bg-dark">
                            <span><img src="../images/logo2.png" height="100"></span>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Verificación de Correo Electrónico</h4>
                                <p class="text-muted mb-4">Por favor ingresa el código de 6 dígitos enviado a <span class="fw-semibold"><?php echo htmlspecialchars($verification_email); ?></span></p>
                            </div>

                            <form id="verificationForm" autocomplete="off">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <div class="row justify-content-center">
                                    <div class="col-2">
                                        <div class="mb-3">
                                            <label for="digit1-input" class="visually-hidden">Dígito 1</label>
                                            <input type="text" class="form-control form-control-lg text-center" onkeyup="moveToNext(1, event)" maxLength="1" id="digit1-input">
                                        </div>
                                    </div><!-- end col -->

                                    <div class="col-2">
                                        <div class="mb-3">
                                            <label for="digit2-input" class="visually-hidden">Dígito 2</label>
                                            <input type="text" class="form-control form-control-lg text-center" onkeyup="moveToNext(2, event)" maxLength="1" id="digit2-input">
                                        </div>
                                    </div><!-- end col -->

                                    <div class="col-2">
                                        <div class="mb-3">
                                            <label for="digit3-input" class="visually-hidden">Dígito 3</label>
                                            <input type="text" class="form-control form-control-lg text-center" onkeyup="moveToNext(3, event)" maxLength="1" id="digit3-input">
                                        </div>
                                    </div><!-- end col -->

                                    <div class="col-2">
                                        <div class="mb-3">
                                            <label for="digit4-input" class="visually-hidden">Dígito 4</label>
                                            <input type="text" class="form-control form-control-lg text-center" onkeyup="moveToNext(4, event)" maxLength="1" id="digit4-input">
                                        </div>
                                    </div><!-- end col -->

                                    <div class="col-2">
                                        <div class="mb-3">
                                            <label for="digit5-input" class="visually-hidden">Dígito 5</label>
                                            <input type="text" class="form-control form-control-lg text-center" onkeyup="moveToNext(5, event)" maxLength="1" id="digit5-input">
                                        </div>
                                    </div><!-- end col -->

                                    <div class="col-2">
                                        <div class="mb-3">
                                            <label for="digit6-input" class="visually-hidden">Dígito 6</label>
                                            <input type="text" class="form-control form-control-lg text-center" onkeyup="moveToNext(6, event)" maxLength="1" id="digit6-input">
                                        </div>
                                    </div><!-- end col -->
                                </div>
                            
                <div class="mt-3 text-center">
                    <button type="button" id="verifyButton" class="btn btn-secondary w-100">
                        <i class="fa-duotone fa-solid fa-check-circle fa-lg me-1"></i> Confirmar
                    </button>
                </div>
                                
                                <div id="error-message" class="text-danger text-center mt-2" style="display: none;">
                                    El código de verificación es incorrecto. Por favor intenta nuevamente.
                                </div>
                            </form>                            <div class="mt-4 text-center">
                                <p class="mb-0">¿No recibiste el código? <a href="<?php echo $_SERVER['PHP_SELF']; ?>?resend=true" class="fw-semibold text-primary text-decoration-underline">Reenviar</a></p>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end auth page content -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>  <script src="../dist/js/vendor.min.js"></script>
  <script src="../dist/js/app.js"></script>
  <script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
  <script>
        function moveToNext(fieldIndex, event) {
            if (event.key === "Backspace" && fieldIndex > 1) {
                document.getElementById('digit' + (fieldIndex - 1) + '-input').focus();
                return;
            }
            
            const digit = document.getElementById('digit' + fieldIndex + '-input').value;
            if (digit && fieldIndex < 6) {
                document.getElementById('digit' + (fieldIndex + 1) + '-input').focus();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Focus the first input field when the page loads
            document.getElementById('digit1-input').focus();
            
            document.getElementById('verifyButton').addEventListener('click', function() {
                let code = '';
                for (let i = 1; i <= 6; i++) {
                    code += document.getElementById('digit' + i + '-input').value;
                }
                
                // Make sure we have a 6-digit code
                if (code.length !== 6) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor ingresa los 6 dígitos del código.'
                    });
                    return;
                }
                
                // Mostrar indicador de carga en el botón
                const $button = $('#verifyButton');
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verificando...');
                
                // Send the code to the server for verification
                $.ajax({
                    url: 'verify-code.php',
                    type: 'POST',
                    data: {code: code},
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            // Show success message and auto-redirect
                            Swal.fire({
                                title: '¡Éxito!',
                                text: data.message || '¡Tu cuenta ha sido registrada exitosamente!',
                                icon: 'success',
                                timer: 2000,  // Auto close after 2 seconds
                                timerProgressBar: true,
                                showConfirmButton: false  // Remove the confirm button
                            }).then(() => {
                                // This will execute after the alert is closed (timer or dismiss)
                                window.location.href = 'index.php';
                            });
                        } else {
                            // Restaurar el botón
                            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check-circle fa-lg me-1"></i> Confirmar');
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'El código de verificación es incorrecto. Por favor intenta nuevamente.'
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                        // Restaurar el botón
                        $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check-circle fa-lg me-1"></i> Confirmar');
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error. Por favor intenta nuevamente.'
                        });
                    }
                });
            });
            
            // También permitir al usuario presionar Enter para enviar el código
            const inputs = document.querySelectorAll('#verificationForm input');
            inputs.forEach(function(input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        document.getElementById('verifyButton').click();
                    }
                });
            });
        });    </script>
</body>

<footer class="footer footer-alt fw-medium">
    <span>
      <script>
        document.write(new Date().getFullYear())
      </script> - MFA
    </span>
</footer>

</html>