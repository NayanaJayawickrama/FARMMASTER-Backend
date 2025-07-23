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

$allowedCrops = ['Carrot', 'Leeks', 'Tomatoe', 'Cabbage'];

$crop_id = (int)$data->crop_id;
$crop_name = $data->crop_name ?? '';
$crop_duration = $data->crop_duration ?? '';
$quantity = (int)($data->quantity ?? 0);

if (!in_array($crop_name, $allowedCrops)) {
    echo json_encode(["error" => "Invalid crop name."]);
    exit;
}

if (empty($crop_duration)) {
    echo json_encode(["error" => "Crop duration is required."]);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(["error" => "Quantity must be greater than zero."]);
    exit;
}

$stmt = $conn->prepare("UPDATE crop_inventory SET crop_name=?, crop_duration=?, quantity=? WHERE crop_id=?");
if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssii", $crop_name, $crop_duration, $quantity, $crop_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
}
