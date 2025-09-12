<?php
// filepath: c:\xampp\htdocs\FarmMaster\farm_master_backend\reset_password.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include "database.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$token = trim($data['token'] ?? '');
$newPassword = trim($data['password'] ?? '');

if (!$email || !$token || !$newPassword) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}
if (strlen($newPassword) < 6) {
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters."]);
    exit;
}

// Check token
$stmt = $conn->prepare("SELECT expires_at FROM password_resets WHERE email = ? AND token = ?");
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$stmt->bind_result($expires_at);
if ($stmt->fetch()) {
    if (strtotime($expires_at) < time()) {
        echo json_encode(["status" => "error", "message" => "Token expired."]);
        exit;
    }
    $stmt->close();

    // Update password
    $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed, $email);
    $stmt->execute();

    // Delete token
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Password reset successful."]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid token or email."]);
}
$conn->close();
?>