<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id)) {
    echo json_encode(["error" => "No user_id provided."]);
    exit;
}

$user_id = $data->user_id;
$first_name = $data->first_name ?? null;
$last_name = $data->last_name ?? null;
$email = $data->email ?? null;
$phone = $data->phone ?? null;
$user_role = $data->user_role ?? null;

// New: Accept status (Active/Inactive) and convert to is_active 1/0
$status = $data->status ?? "Active";
$is_active = ($status === "Active") ? 1 : 0;

// Validate required fields (optional but recommended)
if (!$first_name || !$last_name || !$email || !$user_role) {
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

$stmt = $conn->prepare("UPDATE user SET first_name = ?, last_name = ?, email = ?, phone = ?, user_role = ?, is_active = ? WHERE user_id = ?");
$stmt->bind_param("sssssii", $first_name, $last_name, $email, $phone, $user_role, $is_active, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to update user."]);
}
?>
