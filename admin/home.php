<?php 
session_start();
include 'assets/header.php'; 
?>

<body>

    <div id="layout-wrapper">
        <?php include 'assets/topbar.php'; ?>
        <?php include 'assets/menubar.php'; ?>

        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">
                    <!-- Contenido de la pagina -->
                </div>
            </div>
        </div>
    </div>
    <?php include 'assets/scripts.php'; ?>
    <?php include 'assets/footer.php'; ?>

    <script>
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '" . $_SESSION['success_message'] . "',
                showConfirmButton: false,
                timer: 1500
            });";
            unset($_SESSION['success_message']);
        }
        ?>
    </script>
</body>

</html>