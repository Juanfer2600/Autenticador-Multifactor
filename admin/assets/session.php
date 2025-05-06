<?php include 'conn.php';

function checkSession()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }

    $max_lifetime = 30 * 24 * 60 * 60;
    $current_time = time();

    // Verificar tiempo de inactividad
    if (
        isset($_SESSION['last_activity']) &&
        ($current_time - $_SESSION['last_activity']) > $max_lifetime
    ) {
        session_unset();
        session_destroy();
        header("Location: index.php?error=session_expired");
        exit();
    }
    
    $_SESSION['last_activity'] = $current_time;

    // Regenerar ID de sesión periódicamente
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
