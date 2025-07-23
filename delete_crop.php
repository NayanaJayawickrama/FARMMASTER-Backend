<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->crop_id)) {
    echo json_encode(["error" => "No crop_id provided."]);
    exit;
}

$crop_id = $data->crop_id;

$stmt = $conn->prepare("DELETE FROM crop_inventory WHERE crop_id = ?");
$stmt->bind_param("i", $crop_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to delete crop."]);
}
?>
