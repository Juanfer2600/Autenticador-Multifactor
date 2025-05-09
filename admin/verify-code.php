<?php
// Se requiere este archivo para la conexión a la base de datos
require_once 'includes/session_config.php';
require_once 'includes/security_functions.php';
require_once '../config/db_conn.php'; // Make sure database connection is included

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = $_POST['code'] ?? '';
    $stored_code = $_SESSION['verification_code'] ?? '';
    $verification_email = $_SESSION['verification_email'] ?? '';
    $admin_id = $_SESSION['admin'] ?? null; // Obtener ID del admin de la sesión actual
    
    // Check if the code matches
    if ($submitted_code === $stored_code) {
        // Verificar si hay una sesión activa de admin
        if ($admin_id) {
            // Actualizar el campo metodos_mfa del admin autenticado
            $update_stmt = $conn->prepare("UPDATE admin SET metodos_mfa = 'Token OTP' WHERE id = ?");
            $result = $update_stmt->execute([$admin_id]);
            
            if ($result) {
                // Limpiar datos de verificación de la sesión
                unset($_SESSION['verification_code']);
                unset($_SESSION['verification_email']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Método MFA configurado correctamente.',
                    'redirect' => 'home.php'
                ]);
                exit();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el método MFA. Por favor intente nuevamente.'
                ]);
                exit();
            }
        } else {
            // Si no hay una sesión activa, verificar si existe el usuario por email
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND admin_estado = 0");
            $stmt->execute([$verification_email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                // Admin existe pero necesita verificación
                $admin_id = $row['id'];
                
                // Actualizar estado del admin a activo (1) y configurar el método MFA
                $update_stmt = $conn->prepare("UPDATE admin SET metodos_mfa = 'Token OTP' WHERE id = ?");
                $result = $update_stmt->execute([$admin_id]);
                
                if ($result) {
                    // Limpiar datos de verificación de la sesión
                    unset($_SESSION['verification_code']);
                    unset($_SESSION['verification_email']);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Email verificado correctamente. Su cuenta ahora está activa y el método MFA ha sido configurado.',
                        'redirect' => 'login.php'
                    ]);
                    exit();
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al activar su cuenta. Por favor intente nuevamente.'
                    ]);
                    exit();
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró información del usuario. Por favor regístrese nuevamente.'
                ]);
                exit();
            }
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Código de verificación incorrecto. Por favor intente nuevamente.'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método de solicitud inválido.'
    ]);
    exit();
}
