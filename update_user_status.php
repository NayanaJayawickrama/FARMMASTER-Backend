<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->status)) {
    echo json_encode(["error" => "user_id or status missing."]);
    exit;
}

$user_id = $data->user_id;
$is_active = ($data->status === "active") ? 1 : 0;

$stmt = $conn->prepare("UPDATE user SET is_active = ? WHERE user_id = ?");
$stmt->bind_param("ii", $is_active, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to update user status."]);
}
?>
