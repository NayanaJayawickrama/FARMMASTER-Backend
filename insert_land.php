<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_master#";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "DB connection failed."]);
  exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id'] ?? 0);
$size = trim($data['size'] ?? '');
$location = trim($data['location'] ?? '');

if (!$user_id || !$size || !$location) {
  echo json_encode(["status" => "error", "message" => "Missing required fields."]);
  exit();
}

$stmt = $conn->prepare("INSERT INTO land (user_id, size, location) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $size, $location);

if ($stmt->execute()) {
  echo json_encode(["status" => "success", "message" => "Land data inserted successfully."]);
} else {
  echo json_encode(["status" => "error", "message" => "Failed to insert data."]);
}

$stmt->close();
$conn->close();
?>
