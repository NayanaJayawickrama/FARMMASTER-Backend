<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["error" => "No data received."]);
    exit;
}

$first_name = $data->first_name;
$last_name = $data->last_name;
$email = $data->email;
$password = password_hash($data->password, PASSWORD_DEFAULT);
$phone = $data->phone;
$user_role = $data->user_role;

$stmt = $conn->prepare("INSERT INTO user (first_name, last_name, email, password, phone, user_role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssis", $first_name, $last_name, $email, $password, $phone, $user_role);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "User added successfully."]);
} else {
    echo json_encode(["error" => "Failed to add user."]);
}
?>
