<?php
session_start();
include 'conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Por favor complete todos los campos'
        ]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, nombre_usuario, apellido_usuario, correo_usuario, password, tipo_usuario FROM Usuario WHERE correo_usuario = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre_usuario'];
            $_SESSION['apellido'] = $user['apellido_usuario'];
            $_SESSION['email'] = $user['correo_usuario'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
            $_SESSION['last_activity'] = time();
            $_SESSION['created'] = time();
            $_SESSION['success_message'] = 'Bienvenido ' . $user['nombre_usuario'] . ' ' . $user['apellido_usuario'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Bienvenido ' . $user['nombre_usuario'] . ' ' . $user['apellido_usuario'],
                'redirect' => 'home.php'
            ]);
            exit();
        }
    }

    echo json_encode([
        'success' => false,
        'message' => 'Usuario o contraseña incorrectos'
    ]);
    exit();
}

echo json_encode([
    'success' => false,
    'message' => 'Método no permitido'
]);
exit();
