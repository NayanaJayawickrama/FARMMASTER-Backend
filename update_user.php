<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id)) {
    echo json_encode(["error" => "No user_id provided."]);
    exit;
}

$user_id = $data->user_id;
$first_name = $data->first_name;
$last_name = $data->last_name;
$email = $data->email;
$phone = $data->phone;
$user_role = $data->user_role;

$stmt = $conn->prepare("UPDATE user SET first_name=?, last_name=?, email=?, phone=?, user_role=? WHERE user_id=?");
$stmt->bind_param("sssisi", $first_name, $last_name, $email, $phone, $user_role, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to update user."]);
}
?>
