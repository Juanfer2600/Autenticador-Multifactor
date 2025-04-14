<?php include 'conn.php';

function checkSession()
{
    global $conn;
    
    // Verificar si existe una sesión de usuario
    if (!isset($_SESSION['user_id'])) {
        header('location: ../index.php');
        exit();
    }
    
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
    
    // Actualizar last_login en la base de datos (una vez cada hora para no sobrecargar la DB)
    if (!isset($_SESSION['last_db_update']) || ($current_time - $_SESSION['last_db_update']) > 3600) {
        updateLastLogin($_SESSION['user_id']);
        $_SESSION['last_db_update'] = $current_time;
    }

    // Regenerar el ID de sesión periódicamente (cada 30 minutos)
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Función para actualizar el campo last_login en la base de datos
function updateLastLogin($user_id) {
    global $conn;
    $current_datetime = date('Y-m-d H:i:s');
    
    $query = "UPDATE usuario SET last_login = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $current_datetime, $user_id);
    $stmt->execute();
}

// Verificar si hay una sesión activa
if (isset($_SESSION['user_id'])) {
    checkSession();
}
