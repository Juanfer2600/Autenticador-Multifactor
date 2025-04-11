<?php
include 'assets/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure we have the email in session, or set a default value
$verification_email = $_SESSION['verification_email'] ?? '';

?>

<body>
    <!-- Debug console output -->
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

                                <form autocomplete="off">
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
                                </form>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-success w-100">Confirm</button>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                    <!-- <div class="mt-4 text-center">
                        <p class="mb-0">Didn't receive a code ? <a href="auth-pass-reset-basic.html" class="fw-semibold text-primary text-decoration-underline">Resend</a> </p>
                    </div> -->

                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end auth page content -->
    <?php include 'assets/scripts.php'; ?>

</body>

</html>