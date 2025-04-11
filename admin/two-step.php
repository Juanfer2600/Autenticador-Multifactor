<?php
include 'assets/header.php';
include 'enviar_correo.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug variables
$email_status = '';
$debug_info = [];
$verification_code = '';

// Make sure we have the email in session, or set a default value
$verification_email = $_SESSION['verification_email'] ?? '';

// Check if resend was requested
if (isset($_GET['resend']) && $_GET['resend'] == 'true') {
    // Force resend by unsetting the verification code
    unset($_SESSION['verification_code']);
}

// Only send the email if it hasn't been sent yet or resend was requested
if (!isset($_SESSION['verification_code']) || empty($_SESSION['verification_code'])) {
    if (!empty($verification_email)) {
        $destinatarios = [$verification_email];
        $asunto = "Verificación de correo electrónico";
        $codigo_verificacion = '';
        for ($i = 0; $i < 6; $i++) {
            $codigo_verificacion .= rand(1, 9);
        }
        $cuerpo = "Por favor, ingresa el siguiente código de verificación: <strong>$codigo_verificacion</strong>";

        // Try to send the email
        try {
            // Fix the order of parameters: destinatarios, asunto, cuerpo
            $send_result = enviarCorreo($destinatarios, $asunto, $cuerpo);
            
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

// Disable debugging for production - setting to false hides the debug panel
$show_debug = false;
?>

<body>
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
    */ ?>
    
    <!-- Debug console output is also commented out -->
    <!-- <script>    
    document.addEventListener('DOMContentLoaded', function() {
        const emailSpan = document.querySelector('.fw-semibold');
        if (!emailSpan.textContent.trim()) {
            const localEmail = localStorage.getItem('verification_email');
            if (localEmail) {
                console.log("Using email from localStorage:", localEmail);
                emailSpan.textContent = localEmail;
            } else {
                console.log("No email found in localStorage either");
            }
        } else {
            console.log("Using email from PHP session:", emailSpan.textContent);
        }
    });
    </script> -->

    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <div>
                            <a href="index.html" class="d-inline-block auth-logo">
                                <img src="assets/images/logo-light.png" alt="" height="20">
                            </a>
                        </div>
                        <p class="mt-3 fs-15 fw-medium"></p>
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-6">
                    <div class="card mt-4 card-bg-fill">

                        <div class="card-body p-4">
                            <div class="mb-4">
                                <div class="avatar-lg mx-auto">
                                    <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                                        <i class="ri-mail-line"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="p-2 mt-4">
                                <div class="text-muted text-center mb-4 mx-lg-3">
                                    <h4>Verify Your Email</h4>
                                    <p>Please enter the 6 digit code sent to <span class="fw-semibold"><?php echo htmlspecialchars($verification_email); ?></span></p>
                                </div>

                                <form id="verificationForm" autocomplete="off">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="mb-3">
                                                <label for="digit1-input" class="visually-hidden">Digit 1</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-light text-center" onkeyup="moveToNext(1, event)" maxLength="1" id="digit1-input">
                                            </div>
                                        </div><!-- end col -->

                                        <div class="col-2">
                                            <div class="mb-3">
                                                <label for="digit2-input" class="visually-hidden">Digit 2</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-light text-center" onkeyup="moveToNext(2, event)" maxLength="1" id="digit2-input">
                                            </div>
                                        </div><!-- end col -->

                                        <div class="col-2">
                                            <div class="mb-3">
                                                <label for="digit3-input" class="visually-hidden">Digit 3</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-light text-center" onkeyup="moveToNext(3, event)" maxLength="1" id="digit3-input">
                                            </div>
                                        </div><!-- end col -->

                                        <div class="col-2">
                                            <div class="mb-3">
                                                <label for="digit4-input" class="visually-hidden">Digit 4</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-light text-center" onkeyup="moveToNext(4, event)" maxLength="1" id="digit4-input">
                                            </div>
                                        </div><!-- end col -->

                                        <div class="col-2">
                                            <div class="mb-3">
                                                <label for="digit4-input" class="visually-hidden">Digit 5</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-light text-center" onkeyup="moveToNext(5, event)" maxLength="1" id="digit5-input">
                                            </div>
                                        </div><!-- end col -->

                                        <div class="col-2">
                                            <div class="mb-3">
                                                <label for="digit4-input" class="visually-hidden">Digit 6</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-light text-center" onkeyup="moveToNext(6, event)" maxLength="1" id="digit6-input">
                                            </div>
                                        </div><!-- end col -->
                                    </div>
                                
                                    <div class="mt-3">
                                        <button type="button" id="verifyButton" class="btn btn-success w-100">Confirm</button>
                                    </div>
                                    
                                    <div id="error-message" class="text-danger text-center mt-2" style="display: none;">
                                        The verification code is incorrect. Please try again.
                                    </div>
                                </form>

                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                    <div class="mt-4 text-center">
                        <p class="mb-0">Didn't receive a code? <a href="two-step.php?resend=true" class="fw-semibold text-primary text-decoration-underline">Resend</a> </p>
                    </div>

                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end auth page content -->
    <?php include 'assets/scripts.php'; ?>

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
            document.getElementById('verifyButton').addEventListener('click', function() {
                let code = '';
                for (let i = 1; i <= 6; i++) {
                    code += document.getElementById('digit' + i + '-input').value;
                }
                
                // Make sure we have a 6-digit code
                if (code.length !== 6) {
                    document.getElementById('error-message').style.display = 'block';
                    return;
                }
                
                // Send the code to the server for verification
                fetch('verify-code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'code=' + code
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message and auto-redirect
                        Swal.fire({
                            title: 'Success!',
                            text: 'Your account has been registered successfully!',
                            icon: 'success',
                            timer: 2000,  // Auto close after 2 seconds
                            timerProgressBar: true,
                            showConfirmButton: false  // Remove the confirm button
                        }).then(() => {
                            // This will execute after the alert is closed (timer or dismiss)
                            window.location.href = 'index.php';
                        });
                    } else {
                        // Show error message
                        document.getElementById('error-message').style.display = 'block';
                        document.getElementById('error-message').textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('error-message').style.display = 'block';
                    document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
                });
            });
        });
    </script>
</body>

</html>