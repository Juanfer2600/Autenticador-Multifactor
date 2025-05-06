<?php include 'assets/header.php'; ?>

<body>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"> </div>
        </div>

        <div class="auth-page-content">
            <div class="container">

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4 card-bg-fill">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Crea una nueva cuenta</h5>
                                </div>
                                <div class="p-2 mt-4">
                                    <form action="assets/register.php" method="POST">
                                        <div class="mb-3">
                                            <label for="nombre_usuario" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="apellido_usuario" class="form-label">Apellido</label>
                                            <input type="text" class="form-control" id="apellido_usuario" name="apellido_usuario" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="correo_usuario" class="form-label">Correo</label>
                                            <input type="email" class="form-control" id="correo_usuario" name="correo_usuario" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Password</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control password-input" id="password-input" name="password" required>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" 
                                                    type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Registrarse</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <p class="mb-0">Ya tienes cuenta ? <a href="index.php" class="fw-semibold text-primary text-decoration-underline"> Inicia sesión! </a> </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'assets/footer.php'; ?>

    </div>
    <?php include 'assets/scripts.php'; ?>
    
    <script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: "POST",
                url: "assets/register.php",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Registro Exitoso!',
                            text: 'Para completar su registro hacer verificación de dos pasos.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            localStorage.setItem('verification_email', $('#correo_usuario').val());
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            if (response.message === 'El correo ya está registrado') {
                                $('form')[0].reset();
                            }
                        });
                    }
                },
                error: function() {
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