<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->crop_id)) {
    echo json_encode(["success" => false, "error" => "No crop_id provided."]);
    exit;
}

$allowedCrops = ['Carrot', 'Leeks', 'Tomato', 'Cabbage'];

$crop_id = (int)$data->crop_id;
$crop_name = $data->crop_name ?? '';
$quantity = (float)($data->quantity ?? 0);

// Validate crop name
if (!in_array($crop_name, $allowedCrops)) {
    echo json_encode(["success" => false, "error" => "Invalid crop name."]);
    exit;
}

// Validate quantity
if ($quantity <= 0) {
    echo json_encode(["success" => false, "error" => "Quantity must be greater than zero."]);
    exit;
}

// Check if crop exists
$checkStmt = $conn->prepare("SELECT crop_id FROM crop_inventory WHERE crop_id = ?");
if (!$checkStmt) {
    echo json_encode(["success" => false, "error" => "Database prepare failed: " . $conn->error]);
    exit;
}

$checkStmt->bind_param("i", $crop_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Crop not found."]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Update only quantity (crop name stays the same to prevent duplicates)
$stmt = $conn->prepare("UPDATE crop_inventory SET quantity = ? WHERE crop_id = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("di", $quantity, $crop_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true, 
            "message" => "Crop quantity updated successfully.",
            "updated_quantity" => $quantity
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "No changes made to the crop."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>