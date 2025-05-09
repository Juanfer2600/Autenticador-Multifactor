<!-- Navbar -->
<?php
$navbarClass = $user['color_mode'] == "dark" ? "navbar-dark" : "navbar-white navbar-light";
echo "<nav class='main-header navbar navbar-expand $navbarClass'>";
?>
<!-- Left navbar links -->
<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link toggle-sidebar" data-widget="pushmenu" href="#" role="button"><i class="fa fa-duotone fa-bars"></i></a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-widget="fullscreen" role="button">
      <i class="fa fa-duotone fa-expand-arrows-alt"></i>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link recargar">
      <i class="fa-duotone fa-solid fa-arrows-rotate-reverse"></i>
    </a>
  </li>
</ul>

<?php
// Check if OTP token is configured in user's MFA methods
$hasOTP = false;
$notificationCount = 0;
$notificationClass = "danger"; // Default to danger (red) for security issues
$notificationMessage = "Favor verificar tu cuenta";
$notificationLink = "two-step.php";
$notificationIcon = "fas fa-shield-alt";
$notificationTime = "Ahora";

if (isset($user['metodos_mfa']) && !empty($user['metodos_mfa'])) {
  if (strpos($user['metodos_mfa'], 'Token OTP') !== false) {
    $hasOTP = true;
    $notificationClass = "success";
    $notificationMessage = "Verificación de dos pasos configurada";
    $notificationIcon = "fas fa-check-circle";
  }
}

// Set notification count if OTP is not configured
if (!$hasOTP) {
  $notificationCount = 1;
}
?>

<!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
  <li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
      <i class="far fa-bell fa-lg"></i>
      <?php if (!$hasOTP): ?>
        <span class="badge badge-<?php echo $notificationClass; ?> navbar-badge" style="font-size: 0.6rem; right: 3px; top: 3px;">
          <?php if ($notificationCount > 0) echo $notificationCount; ?>
        </span>
      <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">
      <span class="dropdown-item dropdown-header">Notificaciones de Seguridad</span>
      <div class="dropdown-divider"></div>
      <?php if (!$hasOTP): ?>
        <a href="<?php echo $notificationLink; ?>" class="dropdown-item">
          <p class="text-sm"><i class="<?php echo $notificationIcon; ?> mr-2 text-<?php echo $notificationClass; ?>"></i><?php echo $notificationMessage; ?></p>
          <span class="float-right text-muted text-sm"><?php echo $notificationTime; ?></span>
        </a>
      <?php else: ?>
        <a href="#" class="dropdown-item">
          <p class="text-sm"><i class="<?php echo $notificationIcon; ?> mr-2 text-<?php echo $notificationClass; ?>"></i><?php echo $notificationMessage; ?></p>
          <span class="float-right text-muted text-sm"><?php echo $notificationTime; ?></span>
        </a>
      <?php endif; ?>
    </div>
  </li>
  <li class="nav-item dropdown user-menu">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown">
      <img src="<?php echo $photoSrc; ?>" class="user-image img-circle" alt="User Image">
    </a>
    <ul class="dropdown-menu dropdown-menu-md dropdown-menu-right">
      <!-- User image -->
      <li class="user-header">
        <img src="<?php echo $photoSrc; ?>" class="img-circle">
        <p>
          <?php echo $user['user_firstname'] . ' ' . $user['user_lastname']; ?>

          <small>
            Usuario
          </small>
        </p>
      </li>
      <!-- Menu Footer-->
      <li class="user-footer">
        <div class="row">
          <div class="col-lg-6">
            <a href="#profile" data-toggle="modal" class="btn btn-primary btn-sm btn-block" id="admin_profile">Perfil</a>
          </div>
          <div class="col-lg-6">
            <a href="logout.php" class="btn btn-primary btn-sm btn-block">Cerrar Sesión</a>
          </div>
        </div>
      </li>
    </ul>
  </li>
</ul>
</nav>
<!-- /.navbar -->