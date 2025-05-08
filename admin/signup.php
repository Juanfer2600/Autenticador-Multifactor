<?php
require_once 'includes/session_config.php';
require_once 'includes/security_functions.php';

// Si ya hay una sesión activa, redirigir al home
if (isset($_SESSION['admin'])) {
    header('location:home.php');
    exit();
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Usuario</title>
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
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Changed from col-xxl-4 col-lg-5 to col-xxl-8 col-lg-9 for a wider card -->
                <div class="col-xxl-8 col-lg-9">
                    <div class="card">
                        <!-- Logo -->
                        <div class="card-header text-center bg-dark">
                            <span><img src="../images/logo2.png" height="100"></span>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Crea una nueva cuenta</h4>
                                <p class="text-muted mb-4">Ingresa tus datos para registrarte en el sistema</p>
                            </div>

                            <form action="controllers/register.php" method="POST" id="registerForm" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <div class="row">
                                    <!-- Primera columna -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nombre_usuario" class="form-label">Nombre</label>
                                            <div class="input-group input-group-merge">
                                                <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                                                <div class="input-group-text">
                                                    <i class="fa-duotone fa-solid fa-user fa-lg"></i>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Por favor ingrese su nombre
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Segunda columna -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="apellido_usuario" class="form-label">Apellido</label>
                                            <div class="input-group input-group-merge">
                                                <input type="text" class="form-control" id="apellido_usuario" name="apellido_usuario" required>
                                                <div class="input-group-text">
                                                    <i class="fa-duotone fa-solid fa-user fa-lg"></i>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Por favor ingrese su apellido
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Correo en su propia fila, pero ocupando todo el ancho -->
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="correo_usuario" class="form-label">Correo</label>
                                            <div class="input-group input-group-merge">
                                                <input type="email" class="form-control" id="correo_usuario" name="correo_usuario" required placeholder="ejemplo@email.com">
                                                <div class="input-group-text">
                                                    <i class="fa-duotone fa-solid fa-envelope fa-lg"></i>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Por favor ingrese un correo válido
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Primera columna para contraseña -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Contraseña</label>
                                            <div class="input-group input-group-merge">
                                                <input type="password" class="form-control" id="password-input" name="password" required placeholder="Mínimo 6 caracteres">
                                                <div class="input-group-text" data-password="false">
                                                    <i class="fa-duotone fa-solid fa-eye fa-lg"></i>
                                                </div>
                                                <div class="invalid-feedback">
                                                    La contraseña debe tener al menos 6 caracteres
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Segunda columna para confirmar contraseña -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="confirm-password">Confirmar Contraseña</label>
                                            <div class="input-group input-group-merge">
                                                <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                                                <div class="input-group-text" data-password="false">
                                                    <i class="fa-duotone fa-solid fa-eye fa-lg"></i>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Las contraseñas no coinciden
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nuevo campo de género en su propia fila -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Género</label>
                                            <div class="d-flex gap-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gender" id="gender-male" value="0" required>
                                                    <label class="form-check-label" for="gender-male">
                                                        Masculino
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gender" id="gender-female" value="1" required>
                                                    <label class="form-check-label" for="gender-female">
                                                        Femenino
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="invalid-feedback d-block" id="gender-feedback" style="display: none !important;">
                                                Por favor seleccione su género
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 mb-0 text-center">
                                    <button class="btn btn-secondary" type="submit" id="registerButton">
                                        <i class="fa-duotone fa-solid fa-user-plus fa-lg me-1"></i> Registrarse
                                    </button>
                                </div>
                            </form>
                            <p class="text-black-50 text-center mt-3">¿Ya tienes cuenta? <a href="index.php" class="fw-medium text-primary"> Inicia sesión</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt fw-medium">
        <span>
            <script>
                document.write(new Date().getFullYear())
            </script> - MFA
        </span>
    </footer>

    <script src="../dist/js/vendor.min.js"></script>
    <script src="../dist/js/app.js"></script>
    <script src="../plugins/sweetalert2/sweetalert2.min.js"></script>

    <script>
        // Validación de formulario
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Funciones de validación
        function validateEmail(email) {
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailPattern.test(email);
        }

        function validatePassword(password) {
            return password.length >= 6; // Mínimo 6 caracteres
        }

        function passwordsMatch() {
            return $('#password-input').val() === $('#confirm-password').val();
        }

        // Validación en tiempo real
        $('#correo_usuario').on('input', function() {
            if (validateEmail($(this).val())) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });

        $('#password-input').on('input', function() {
            if (validatePassword($(this).val())) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });

        $('#confirm-password').on('input', function() {
            if ($(this).val() === $('#password-input').val()) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });

        // Toggle password visibility - Implementación corregida
        $(document).ready(function() {
            $('.password-toggle').on('click', function() {
                const input = $(this).prev('input');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });

        // Ajax form submission
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                const email = $('#correo_usuario').val();
                const password = $('#password-input').val();
                const confirmPassword = $('#confirm-password').val();

                if (!validateEmail(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor ingrese un correo electrónico válido'
                    });
                    return false;
                }

                if (!validatePassword(password)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La contraseña debe tener al menos 6 caracteres'
                    });
                    return false;
                }

                if (password !== confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden'
                    });
                    return false;
                }

                const $button = $('#registerButton');
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Procesando...');

                $.ajax({
                    type: "POST",
                    url: "controllers/register.php",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: '¡Registro Exitoso!',
                                text: 'Su cuenta ha sido creada correctamente.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        } else {
                            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-user-plus fa-lg me-1"></i> Registrarse');
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                        }
                    },
                    error: function() {
                        $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-user-plus fa-lg me-1"></i> Registrarse');
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error durante el proceso de registro.',
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>