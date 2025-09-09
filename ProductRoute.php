<?php
require_once 'ProductController.php';

$controller = new ProductController();
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

switch ($action) {
    case 'getProducts':
        $result = $controller->getProducts();
        echo json_encode($result);
        break;
    case 'addProduct':
        $result = $controller->addProduct($input);
        echo json_encode($result);
        break;
    case 'updateProduct':
        $result = $controller->updateProduct($input);
        echo json_encode($result);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>