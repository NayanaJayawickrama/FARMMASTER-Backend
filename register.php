<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_master#"; // change to your DB name if needed

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

$first_name = trim($data['first_name'] ?? '');
$last_name = trim($data['last_name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$plain_password = $data['password'] ?? '';
$account_type = trim($data['account_type'] ?? '');

// Validate required fields
if (!$first_name || !$last_name || !$email || !$plain_password || !$account_type) {
    echo json_encode(["status" => "error", "message" => "All required fields must be filled."]);
    exit();
}

if (!in_array($account_type, ['Landowner', 'Buyer'])) {
    echo json_encode(["status" => "error", "message" => "Invalid account type."]);
    exit();
}

// Check if email already exists
$email_check = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
$email_check->bind_param("s", $email);
$email_check->execute();
$email_check->store_result();

if ($email_check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered."]);
    $email_check->close();
    $conn->close();
    exit();
}
$email_check->close();

// Hash the password
$hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

// Insert user (without user_id, assuming it is auto-increment numeric)
$stmt = $conn->prepare("INSERT INTO user (first_name, last_name, email, phone, password, user_role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $hashed_password, $account_type);

if ($stmt->execute()) {
    $inserted_id = $conn->insert_id;  // Get auto-increment ID

    // Generate custom user ID with prefix and padded number
    $prefix = $account_type === 'Landowner' ? 'L-' : 'B-';
    $custom_user_id = $prefix . str_pad($inserted_id, 3, '0', STR_PAD_LEFT);

    echo json_encode([
        "status" => "success",
        "message" => "Registration successful.",
        "user_id" => $custom_user_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed. Please try again."]);
}

$stmt->close();
$conn->close();
?>
