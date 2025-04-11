<?php
include 'assets/session.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = $_POST['code'] ?? '';
    $stored_code = $_SESSION['verification_code'] ?? '';
    $verification_email = $_SESSION['verification_email'] ?? '';
    
    // Check if the code matches
    if ($submitted_code === $stored_code) {
        // Retrieve user data from session or temporary storage
        $temp_user_data = $_SESSION['temp_user_data'] ?? null;
        
        if (!$temp_user_data) {
            // Try to get the user data from the database as this might be a re-verification
            $stmt = $conn->prepare("SELECT * FROM Usuario WHERE correo_usuario = ? AND status = 0");
            $stmt->bind_param("s", $verification_email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // User exists but needs verification
                $row = $result->fetch_assoc();
                $user_id = $row['id_usuario'];
                
                // Update user status to active (1)
                $update_stmt = $conn->prepare("UPDATE Usuario SET status = 1 WHERE id_usuario = ?");
                $update_stmt->bind_param("i", $user_id);
                
                if ($update_stmt->execute()) {
                    // Clear verification session data
                    unset($_SESSION['verification_code']);
                    unset($_SESSION['verification_email']);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Email verified successfully. Your account is now active.'
                    ]);
                    exit();
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to activate your account. Please try again.'
                    ]);
                    exit();
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'User information not found. Please register again.'
                ]);
                exit();
            }
        } else {
            // Complete the registration with the stored user data
            $nombre_usuario = $temp_user_data['nombre_usuario'];
            $apellido_usuario = $temp_user_data['apellido_usuario'];
            $correo_usuario = $temp_user_data['correo_usuario'];
            $password_hash = $temp_user_data['password_hash'];
            $metodos_mfa = $temp_user_data['metodos_mfa'];
            $tipo_usuario = $temp_user_data['tipo_usuario'];
            
            // Corrected SQL query - include status=1 in the VALUES directly
            $sql = "INSERT INTO Usuario (nombre_usuario, apellido_usuario, correo_usuario, password, metodos_mfa, tipo_usuario, status) VALUES (?, ?, ?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nombre_usuario, $apellido_usuario, $correo_usuario, $password_hash, $metodos_mfa, $tipo_usuario);
            
            if ($stmt->execute()) {
                // Clear verification session data
                unset($_SESSION['verification_code']);
                unset($_SESSION['verification_email']);
                unset($_SESSION['temp_user_data']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful! You can now login.'
                ]);
                exit();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to complete registration. Error: ' . $conn->error
                ]);
                exit();
            }
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect verification code. Please try again.'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}
