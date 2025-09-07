<?php

putenv('SMTP_USER=radeeshapraneeth531@gmail.com');
putenv('SMTP_PASS=nilbgvvrladdtfzk');
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include "database.php";

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents("php://input"), true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$frontendUrl = isset($data['frontendUrl']) ? $data['frontendUrl'] : 'http://localhost:5174'; // fallback

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid email."]);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found."]);
    $stmt->close();
    exit;
}
$stmt->close();

// Generate token and expiry (1 hour)
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", time() + 3600);

// Store token
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expires);
$stmt->execute();
$stmt->close();

// Prepare reset link using the provided frontend URL
$resetLink = "$frontendUrl/reset-password?token=$token&email=$email";

// Debug: Check if SMTP_USER is loaded
error_log('SMTP_USER: ' . getenv('SMTP_USER'));

// Send email using PHPMailer
$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Set your SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');   // SMTP password (App Password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    error_log('PHPMailer setFrom: "' . getenv('SMTP_USER') . '"');
    $mail->setFrom(getenv('SMTP_USER'), 'Farm Master');    
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Farm Master Password Reset';
    $mail->Body    = "Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a><br>This link will expire in 1 hour.";

    $mail->send();
    echo json_encode([
        "status" => "success",
        "message" => "Password reset link sent to your email."
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Could not send email. Mailer Error: {$mail->ErrorInfo}"
    ]);
}

$conn->close();
?>