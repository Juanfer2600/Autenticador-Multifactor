<?php
// Archivo: controllers/register.php
header('Content-Type: application/json');

// Incluir archivos requeridos
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/security_functions.php';
require_once __DIR__ . '/../../config/db_conn.php';

// Inicializar respuesta
$response = array(
    'success' => false,
    'message' => '',
    'redirect' => ''
);

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de solicitud no válido';
    echo json_encode($response);
    exit();
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $response['message'] = 'Token de seguridad no válido';
    echo json_encode($response);
    exit();
}

// Validar que todos los campos requeridos estén presentes
$required_fields = ['nombre_usuario', 'apellido_usuario', 'correo_usuario', 'password', 'confirm_password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $response['message'] = 'Todos los campos son obligatorios';
        echo json_encode($response);
        exit();
    }
}

// Capturar datos del formulario
$nombre = trim($_POST['nombre_usuario']);
$apellido = trim($_POST['apellido_usuario']);
$correo = trim($_POST['correo_usuario']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validar formato de correo electrónico
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'El formato del correo electrónico no es válido';
    echo json_encode($response);
    exit();
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    $response['message'] = 'La contraseña debe tener al menos 6 caracteres';
    echo json_encode($response);
    exit();
}

// Verificar que las contraseñas coincidan
if ($password !== $confirm_password) {
    $response['message'] = 'Las contraseñas no coinciden';
    echo json_encode($response);
    exit();
}

try {
    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo_usuario = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['message'] = 'El correo electrónico ya está registrado';
        echo json_encode($response);
        exit();
    }
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar el nuevo usuario en la base de datos
    $stmt = $conn->prepare("INSERT INTO usuario (nombre_usuario, apellido_usuario, correo_usuario, password_usuario, fecha_registro, estado_usuario) 
                           VALUES (:nombre, :apellido, :correo, :password, NOW(), 1)");
    
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->execute();
    
    // Obtener el ID del usuario recién insertado
    $user_id = $conn->lastInsertId();
    
    // Asignar rol de usuario por defecto (asumiendo que existe una tabla de roles y un rol por defecto)
    $stmt = $conn->prepare("INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (:id_usuario, 2)"); // 2 = rol usuario normal
    $stmt->bindParam(':id_usuario', $user_id);
    $stmt->execute();
    
    // Generar token para verificación de dos pasos
    $verification_code = generateRandomCode(6, 'numeric'); // Genera un código de 6 dígitos
    $token_expires = date('Y-m-d H:i:s', strtotime('+15 minutes')); // El token expira en 15 minutos
    
    // Almacenar el token en la base de datos
    $stmt = $conn->prepare("INSERT INTO token_otp (id_usuario, token, fecha_expiracion, estado_token) 
                           VALUES (:id_usuario, :token, :fecha_expiracion, 1)");
    
    $stmt->bindParam(':id_usuario', $user_id);
    $stmt->bindParam(':token', $verification_code);
    $stmt->bindParam(':fecha_expiracion', $token_expires);
    $stmt->execute();
    
    // Enviar correo con código de verificación
    $to = $correo;
    $subject = "Código de Verificación - Sistema MFA";
    $message = "Hola $nombre $apellido,\n\n";
    $message .= "Tu código de verificación es: $verification_code\n\n";
    $message .= "Este código expirará en 15 minutos.\n\n";
    $message .= "Si no solicitaste este código, por favor ignora este mensaje.\n\n";
    $message .= "Saludos,\nEquipo de Sistema MFA";
    
    // Registrar los datos de verificación en la sesión
    $_SESSION['verification_email'] = $correo;
    $_SESSION['verification_user_id'] = $user_id;
    
    // Aquí iría el código para enviar el correo utilizando la librería que uses (PHPMailer, mail() nativo, etc.)
    // Por ahora, simularemos que el correo se envió correctamente
    
    // Confirmar la transacción
    $conn->commit();
    
    // Respuesta exitosa
    $response['success'] = true;
    $response['message'] = 'Registro exitoso. Por favor completa la verificación de dos pasos.';
    $response['redirect'] = 'verify-code.php'; // Redireccionar a la página de verificación
    
} catch (PDOException $e) {
    // Revertir la transacción en caso de error
    $conn->rollBack();
    $response['message'] = 'Error en el registro: ' . $e->getMessage();
    
    // En producción, mejor no mostrar el mensaje de error específico
    // $response['message'] = 'Error en el registro. Por favor, inténtelo de nuevo más tarde.';
}

// Enviar respuesta
echo json_encode($response);
exit();

/**
 * Genera un código aleatorio
 * @param int $length Longitud del código
 * @param string $type Tipo de código: 'numeric', 'alpha', 'alphanumeric'
 * @return string El código generado
 */
function generateRandomCode($length = 6, $type = 'numeric') {
    switch ($type) {
        case 'alpha':
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'alphanumeric':
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'numeric':
        default:
            $characters = '0123456789';
            break;
    }
    
    $code = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, $max)];
    }
    
    return $code;
}