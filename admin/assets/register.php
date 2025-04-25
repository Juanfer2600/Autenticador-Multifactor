<?php

include 'session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $apellido_usuario = $_POST['apellido_usuario'] ?? '';
    $correo_usuario = $_POST['correo_usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($nombre_usuario) || empty($apellido_usuario) || empty($correo_usuario) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Por favor complete todos los campos'
        ]);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM Usuario WHERE correo_usuario = ?");
    $stmt->bind_param("s", $correo_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El correo ya está registrado'
        ]);
        exit();
    } else {
        $nombre_usuario = $conn->real_escape_string($nombre_usuario);
        $apellido_usuario = $conn->real_escape_string($apellido_usuario);
        $correo_usuario = $conn->real_escape_string($correo_usuario);
        $password = $conn->real_escape_string($password);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $metodos_mfa = 'Token OTP';
        $tipo_usuario = '2';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store user data in session for use after verification
        $_SESSION['temp_user_data'] = [
            'nombre_usuario' => $nombre_usuario,
            'apellido_usuario' => $apellido_usuario,
            'correo_usuario' => $correo_usuario,
            'password_hash' => $password_hash,
            'metodos_mfa' => $metodos_mfa,
            'tipo_usuario' => $tipo_usuario
        ];

        $_SESSION['verification_email'] = $correo_usuario;

        echo json_encode([
            'success' => true,
            'message' => 'Para completar su registro hacer verificación de dos pasos.',
            'redirect' => 'two-step.php'
        ]);
        exit();
    }
}
