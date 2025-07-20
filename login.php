<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "farm_master#"; // Adjust if needed

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

// Get POSTed JSON
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$plain_password = $data['password'] ?? '';
$user_role = trim($data['user_role'] ?? '');

// Validate
if (!$email || !$plain_password || !$user_role) {
    echo json_encode(["status" => "error", "message" => "Please provide email, password, and role."]);
    exit();
}

// Check if user exists
$stmt = $conn->prepare("SELECT user_id, password, user_role FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "No user found with this email."]);
    $stmt->close();
    $conn->close();
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($plain_password, $user['password'])) {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
    $conn->close();
    exit();
}

if ($user['user_role'] !== $user_role) {
    echo json_encode(["status" => "error", "message" => "Account type mismatch."]);
    $conn->close();
    exit();
}

// Success
echo json_encode([
    "status" => "success",
    "message" => "Login successful.",
    "user_id" => $user['user_id'],
    "user_role" => $user['user_role']
]);

$conn->close();
?>
