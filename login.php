<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include "database.php";

// Parse POST data
$data = json_decode(file_get_contents("php://input"), true);

$email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = trim($data['password'] ?? '');
$user_role_input = trim($data['user_role'] ?? '');

// Validate input
if (!$email || !$password || !$user_role_input) {
    echo json_encode(["status" => "error", "message" => "Please provide email, password, and role."]);
    exit();
}

// Normalize role for database enum
$frontendToDbRole = [
    'Landowner' => 'Landowner',
    'Supervisor' => 'Supervisor',
    'Buyer' => 'Buyer',
    'Operational Manager' => 'Operational_Manager',
    'Financial Manager' => 'Financial_Manager'
];

$dbToFrontendRole = array_flip($frontendToDbRole);
$dbRole = $frontendToDbRole[$user_role_input] ?? null;

if (!$dbRole) {
    echo json_encode(["status" => "error", "message" => "Invalid user role."]);
    exit();
}

// Fetch user with role
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, password, user_role, is_active FROM user WHERE email = ? AND user_role = ?");
$stmt->bind_param("ss", $email, $dbRole);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid login credentials."]);
    $stmt->close();
    $conn->close();
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if account is inactive
if ((int)$user['is_active'] === 0) {
    echo json_encode(["status" => "error", "message" => "Your account is inactive. Please contact admin."]);
    $conn->close();
    exit();
}

// Validate password
if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "error", "message" => "Invalid login credentials."]);
    $conn->close();
    exit();
}

// Respond with success
echo json_encode([
    "status" => "success",
    "message" => "Login successful.",
    "user_id" => $user['user_id'],
    "user_role" => $dbToFrontendRole[$user['user_role']],
    "first_name" => $user['first_name'],
    "last_name" => $user['last_name'],
    "email" => $user['email']
]);

$conn->close();
?>
