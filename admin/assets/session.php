<?php include 'conn.php';

function checkSession()
{
    $max_lifetime = 30 * 24 * 60 * 60; // 30 días en segundos
    $current_time = time();

    if (
        isset($_SESSION['last_activity']) &&
        ($current_time - $_SESSION['last_activity']) > $max_lifetime
    ) {
        // La sesión ha expirado
        session_unset();
        session_destroy();
        header('location: ../index.php');
        exit();
    }

    // Renovar el tiempo de la sesión
    $_SESSION['last_activity'] = $current_time;

    // Regenerar el ID de sesión periódicamente (cada 30 minutos)
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

checkSession();
