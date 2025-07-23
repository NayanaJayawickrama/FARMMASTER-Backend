<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["error" => "No data received."]);
    exit;
}

$crop_name = $data->crop_name ?? null;
$crop_duration = $data->crop_duration ?? null;
$quantity = $data->quantity ?? null;

if (!$crop_name || !$crop_duration || !$quantity) {
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO crop_inventory (crop_name, crop_duration, quantity) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode(["error" => "Prepare statement failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssi", $crop_name, $crop_duration, $quantity);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Crop added successfully."]);
} else {
    echo json_encode(["error" => "Failed to add crop: " . $stmt->error]);
}
?>
