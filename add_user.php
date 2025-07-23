<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include 'database.php';

$data = json_decode(file_get_contents("php://input"));

if (
    !isset($data->first_name) ||
    !isset($data->last_name) ||
    !isset($data->email) ||
    !isset($data->password) ||
    !isset($data->phone) ||
    !isset($data->account_type)
) {
    echo json_encode(["error" => "Missing fields."]);
    exit;
}

$first_name = $data->first_name;
$last_name = $data->last_name;
$email = $data->email;
$password = password_hash($data->password, PASSWORD_DEFAULT);
$phone = $data->phone;
$account_type = $data->account_type;
$is_active = 1; // By default

$stmt = $conn->prepare("INSERT INTO user (first_name, last_name, email, password, phone, account_type, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssi", $first_name, $last_name, $email, $password, $phone, $account_type, $is_active);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "User added successfully."]);
} else {
    echo json_encode(["error" => "Failed to add user."]);
}
?>
