<?php
include '../../includes/session.php';
$roles_ids = isset($user['roles_ids']) ? explode(',', $user['roles_ids']) : [];

// Fetch statistics for admin users
// Use the existing database connection from session.php
// No need to create a new PDO connection

// Count active users
$activeUsers = 0;
$stmt = $conn->query("SELECT COUNT(*) as count FROM admin WHERE admin_estado = 0");
if ($stmt) {
    $activeUsers = $stmt->fetchColumn();
}

// Count users by MFA methods
$totalOTP = 0;
$totalQR = 0;
$totalFacial = 0;

$stmt = $conn->query("SELECT metodos_mfa FROM admin WHERE metodos_mfa IS NOT NULL");
if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['metodos_mfa'])) {
            $methods = explode(', ', $row['metodos_mfa']);
            if (in_array('Token OTP', $methods)) $totalOTP++;
            if (in_array('QR', $methods)) $totalQR++;
            if (in_array('Facial', $methods)) $totalFacial++;
        }
    }
}
?>


<section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"></div>
        </div>
    </div>

    <?php if (in_array("1", $roles_ids)) { ?>
        <div class="row">
            <div class="col-lg-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $activeUsers; ?></h3>
                        <p>Usuarios Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="#" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalOTP; ?></h3>
                        <p>Usuarios con Token OTP</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <a href="#" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $totalQR; ?></h3>
                        <p>Usuarios con QR</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <a href="#" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $totalFacial; ?></h3>
                        <p>Usuarios con Reconocimiento Facial</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <a href="#" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
</section>