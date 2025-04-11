<?php
include '../session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crud = $_POST['crud'];

    switch ($crud) {
        case 'create':
            $nombre = $conn->real_escape_string($_POST['nombre']);
            $sql = "INSERT INTO user_type (nombre) VALUES ('$nombre')";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => true, 'message' => 'Rol creado exitosamente.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Error al crear el rol: ' . $conn->error]);
            }
            break;

        case 'edit':
            $id = $conn->real_escape_string($_POST['id']);
            $nombre = $conn->real_escape_string($_POST['nombre']);
            $sql = "UPDATE user_type SET nombre='$nombre' WHERE id='$id'";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => true, 'message' => 'Rol actualizado exitosamente.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Error al actualizar el rol: ' . $conn->error]);
            }
            break;

        case 'delete':
            $id = $conn->real_escape_string($_POST['id']);
            $sql = "DELETE FROM user_type WHERE id='$id'";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => true, 'message' => 'Rol eliminado exitosamente.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Error al eliminar el rol: ' . $conn->error]);
            }
            break;

        case 'get':
            $id = $conn->real_escape_string($_POST['id']);
            $sql = "SELECT * FROM user_type WHERE id='$id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo json_encode($result->fetch_assoc());
            } else {
                echo json_encode(['status' => false, 'message' => 'Rol no encontrado.']);
            }
            break;
    }
}

if (isset($_GET['crud']) && $_GET['crud'] === 'fetch') {
    $sql = "SELECT * FROM user_type";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $actions = '<button class="btn btn-sm btn-primary edit" data-id="' . $row['id'] . '"><i class="bx bx-edit"></i></button> ';
        $actions .= '<button class="btn btn-sm btn-danger delete" data-id="' . $row['id'] . '"><i class="bx bx-trash"></i></button>';
        $data[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'actions' => $actions
        ];
    }
    echo json_encode(['data' => $data]);
}
