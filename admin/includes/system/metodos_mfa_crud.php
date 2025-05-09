<?php
include '../session.php';
include '../../controllers/system/metodos_mfa.php';

$metodoController = new MetodoMFAController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crud = $_POST['crud'];

    switch ($crud) {
        case 'create':
            $result = $metodoController->createMetodo($_POST);
            echo json_encode($result);
            break;
        case 'edit':
            $result = $metodoController->updateMetodo($_POST);
            echo json_encode($result);
            break;
        case 'get':
            $result = $metodoController->getMetodo($_POST['id']);
            echo json_encode($result);
            break;
    }
} elseif (isset($_GET['crud']) && $_GET['crud'] === 'fetch') {
    $result = $metodoController->getAllMetodos();
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
