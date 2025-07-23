<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_master#";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

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

// Fetch user
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, password, user_role FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid login credentials."]);
    $stmt->close();
    $conn->close();
    exit();
}

$user = $result->fetch_assoc(); // ✅ You forgot this line in your original code

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid login credentials."]);
    $stmt->close();
    $conn->close();
    exit();
}

if ($user['user_role'] !== $dbRole) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid login credentials."]);
    $stmt->close();
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

$stmt->close();
$conn->close();
?>
