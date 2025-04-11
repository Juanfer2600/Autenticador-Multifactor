<?php 
session_start();
include 'assets/header.php'; 
?>

<body>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"> </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a class="d-inline-block auth-logo">
                                    <img src="assets/images/logo-light.png" alt="" height="20">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4 card-bg-fill">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">¡Welcome back!</h5>
                                </div>
                                <div class="p-2 mt-4">
                                    <form id="loginForm" onsubmit="return handleLogin(event)">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Correo electrónico</label>
                                            <input type="email" class="form-control" id="username" name="username" placeholder="Enter email" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Password</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control password-input" name="password" placeholder="Enter password" id="password-input" required>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Iniciar sesión</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="mb-0">No tienes cuenta ? <a href="signup.php" class="fw-semibold text-primary text-decoration-underline"> Registrate! </a> </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <?php include 'assets/footer.php'; ?>

    </div>
    <?php include 'assets/scripts.php'; ?>
    
    <script>
    function handleLogin(e) {
        e.preventDefault();
        
        let formData = new FormData(document.getElementById('loginForm'));
        
        fetch('assets/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error inesperado',
                showConfirmButton: false,
                timer: 1500
            });
        });
        
        return false;
    }
    </script>

</body>

</html>