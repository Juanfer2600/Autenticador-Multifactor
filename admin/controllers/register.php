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
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '0'; // 0 para Masculino (default), 1 para Femenino

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
    $stmt = $conn->prepare("SELECT id FROM admin WHERE username = :correo");
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
    
    // Insertar el nuevo usuario en la tabla admin
    $stmt = $conn->prepare("INSERT INTO admin (username, password, user_firstname, user_lastname, photo, created_on, admin_gender, admin_estado) 
                           VALUES (:correo, :password, :nombre, :apellido, '', NOW(), :gender, 0)");
    
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':gender', $gender);
    $stmt->execute();
    
    // Obtener el ID del usuario recién insertado
    $user_id = $conn->lastInsertId();
    
    // Asignar rol de usuario por defecto
    $default_role = "2"; // Rol usuario normal
    $stmt = $conn->prepare("UPDATE admin SET roles_ids = :roles_ids WHERE id = :id_usuario");
    $stmt->bindParam(':roles_ids', $default_role);
    $stmt->bindParam(':id_usuario', $user_id);
    $stmt->execute();
    
    // Usar el ID de sesión de PHP como token
    $session_token = session_id();
    $token_expires = date('Y-m-d', strtotime('+7 days')); // La cookie expira en 7 días
    
    // Almacenar el token de sesión en la tabla sesion
    $stmt = $conn->prepare("INSERT INTO sesion (id_usuario, token_sesion, tiempo_expiracion) 
                           VALUES (:id_usuario, :token_sesion, :tiempo_expiracion)");
    
    $stmt->bindParam(':id_usuario', $user_id);
    $stmt->bindParam(':token_sesion', $session_token);
    $stmt->bindParam(':tiempo_expiracion', $token_expires);
    $stmt->execute();
    
    // Registrar los datos del usuario en la sesión
    $_SESSION['verification_email'] = $correo;
    $_SESSION['verification_user_id'] = $user_id;
    
    // Confirmar la transacción
    $conn->commit();
    
    // Respuesta exitosa
    $response['success'] = true;
    $response['message'] = 'Registro exitoso. Por favor inicia sesión.';
    $response['redirect'] = 'index.php';
    
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