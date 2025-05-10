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

// Obtengo métodos MFA activos
$mfa_methods = [];
try {
    $stmt = $conn->prepare("
      SELECT tipo_metodo 
      FROM metodos_mfa 
      WHERE estado = 1 
        AND tipo_metodo IN ('QR','Reconocimiento facial')
    ");
    $stmt->execute();
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mfa_methods[$r['tipo_metodo']] = true;
    }
} catch (PDOException $e) {
    // silencio
}

$csrf_token = generateCSRFToken();
// Aquí deberías obtener la “semilla” de usuario para el QR
// $user_secret = getUserSecret(...);
// Función de ejemplo para generar QR en Base64
function generarQR($secret)
{
    return '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login MFA</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.min.css">
    <!-- Bootstrap Icons + FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css" rel="stylesheet">
    <!-- Tu CSS -->
    <link href="../dist/css/app.css" rel="stylesheet" id="app-style">
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
                                        <span class="input-group-text"><i class="bi bi-eye-fill"></i></span>
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
                                </div>

                                <div class="d-flex justify-content-center mb-4">
                                    <img src="data:image/png;base64,<?php echo generarQR($user_secret); ?>"
                                        alt="Código QR" class="border p-2">
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" id="btnStep2Back" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Regresar
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        Iniciar sesión
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
    <script>
        $(function() {
            const $form = $('#loginForm'),
                $step1 = $('#step1'),
                $step2 = $('#step2'),
                $indicator = $('.step-indicator li'),
                $mainTitle = $('#mainTitle'),
                $subTitle = $('#subTitle');

            function goToStep(n) {
                // mostrar/ocultar bloques
                $step1.toggle(n === 1);
                $step2.toggle(n === 2);
                // actualizar indicador
                $indicator.each(function() {
                    const $li = $(this);
                    $li.toggleClass('active', +$li.data('step') === n);
                });
                // actualizar textos
                if (n === 1) {
                    $mainTitle.text('Inicia Sesión');
                    $subTitle.text('Ingresa tu correo y contraseña para acceder al sistema');
                } else {
                    $mainTitle.text('Verificación MFA');
                    $subTitle.text('Escanea el código QR con tu aplicación');
                }
            }

            $('#btnStep1Next').click(function() {
                if (!$form[0].checkValidity()) {
                    $form.addClass('was-validated');
                    return;
                }
                goToStep(2);
            });
            $('#btnStep2Back').click(() => goToStep(1));

            // iniciar en paso 1
            goToStep(1);
        });
    </script>
</body>

</html>