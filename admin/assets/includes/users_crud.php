<?php
include_once dirname(__FILE__) . '/../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crud = $_POST['crud'];
    switch ($crud) {
        case 'create':
            $nombre_usuario = $conn->real_escape_string($_POST['nombre_usuario']);
            $apellido_usuario = $conn->real_escape_string($_POST['apellido_usuario']);
            $correo_usuario = $conn->real_escape_string($_POST['correo_usuario']);
            $password = $conn->real_escape_string($_POST['password']);
            $metodos_mfa = isset($_POST['metodos_mfa']) && is_array($_POST['metodos_mfa']) ? $conn->real_escape_string(implode(', ', $_POST['metodos_mfa'])) : '';
            $tipo_usuario = $conn->real_escape_string($_POST['tipo_usuario']);
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuario (nombre_usuario, apellido_usuario, correo_usuario, password, metodos_mfa, tipo_usuario) VALUES ('$nombre_usuario', '$apellido_usuario', '$correo_usuario', '$password_hashed', '$metodos_mfa', '$tipo_usuario')";
            if ($conn->query($sql)) {
                echo json_encode(['status' => true, 'message' => 'Usuario creado exitosamente.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Error al crear el usuario: ' . $conn->error]);
            }
            break;

        case 'edit':
            $id = $conn->real_escape_string($_POST['id']);
            $sql_user = "SELECT * FROM usuario WHERE id='$id'";
            $result_user = $conn->query($sql_user);
            if ($result_user->num_rows === 0) {
                echo json_encode(['status' => false, 'message' => 'Usuario no encontrado.']);
                exit;
            }
            $urow = $result_user->fetch_assoc();

            $nombre_usuario = $conn->real_escape_string($_POST['nombre_usuario']);
            $apellido_usuario = $conn->real_escape_string($_POST['apellido_usuario']);
            $correo_usuario = $conn->real_escape_string($_POST['correo_usuario']);
            $new_password = $conn->real_escape_string($_POST['password']);
            $metodos_mfa = isset($_POST['metodos_mfa']) && is_array($_POST['metodos_mfa']) ? $conn->real_escape_string(implode(', ', $_POST['metodos_mfa'])) : '';
            $tipo_usuario = $conn->real_escape_string($_POST['tipo_usuario']);

            if ($new_password == $urow['password']) {
                $password_hashed = $urow['password'];
            } else {
                $password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql = "UPDATE usuario SET nombre_usuario='$nombre_usuario', apellido_usuario='$apellido_usuario', correo_usuario='$correo_usuario', password='$password_hashed', metodos_mfa='$metodos_mfa', tipo_usuario='$tipo_usuario' WHERE id='$id'";
            if ($conn->query($sql)) {
                echo json_encode(['status' => true, 'message' => 'Usuario actualizado exitosamente.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Error al actualizar el usuario: ' . $conn->error]);
            }
            break;

        case 'delete':
            $id = $conn->real_escape_string($_POST['id']);
            $sql = "DELETE FROM usuario WHERE id='$id'";
            if ($conn->query($sql)) {
                echo json_encode(['status' => true, 'message' => 'Usuario eliminado exitosamente.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Error al eliminar el usuario: ' . $conn->error]);
            }
            break;

        case 'get':
            $id = $conn->real_escape_string($_POST['id']);
            $sql = "SELECT * FROM usuario WHERE id='$id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo json_encode($result->fetch_assoc());
            } else {
                echo json_encode(['status' => false, 'message' => 'Usuario no encontrado.']);
            }
            break;
    }
}

if (isset($_GET['crud']) && $_GET['crud'] === 'fetch') {
    $sql = "SELECT * FROM usuario";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $actions = '<button class="btn btn-sm btn-primary edit" data-id="' . $row['id'] . '"><i class="bx bx-edit"></i></button> ';
        $actions .= '<button class="btn btn-sm btn-danger delete" data-id="' . $row['id'] . '"><i class="bx bx-trash"></i></button>';
        $data[] = [
            'id' => $row['id'],
            'nombre_usuario' => $row['nombre_usuario'],
            'apellido_usuario' => $row['apellido_usuario'],
            'correo_usuario' => $row['correo_usuario'],
            'metodos_mfa' => $row['metodos_mfa'],
            'tipo_usuario' => $row['tipo_usuario'],
            'actions' => $actions
        ];
    }
    echo json_encode(['data' => $data]);
}
