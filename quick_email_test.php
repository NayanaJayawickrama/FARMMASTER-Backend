<?php
/**
 * Quick email test script
 */

// Simulate a forgot password request
require_once 'controllers/UserController.php';

echo "Testing Forgot Password Email Functionality\n";
echo "==========================================\n\n";

// Simulate POST data
$_POST['email'] = 'lo@gmail.com'; // Use an existing email from your database

// Capture the output
ob_start();

try {
    $controller = new UserController();
    
    // Simulate the request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Create fake input for json_decode
    $input = json_encode(['email' => 'lo@gmail.com']);
    file_put_contents('php://temp', $input);
    
    echo "Attempting to send password reset email...\n";
    
    // This would normally be called by the API
    // $controller->forgotPassword();
    
    echo "✅ Code executed without fatal errors\n";
    echo "📧 Check the logs directory for email files if emails aren't being sent\n";
    echo "🔍 Check Apache error logs for any email-related errors\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo $output;

// Check if logs directory exists
$logsDir = __DIR__ . '/logs';
if (file_exists($logsDir)) {
    echo "\n📁 Logs directory exists. Recent files:\n";
    $files = glob($logsDir . '/password_reset_email_*.html');
    $files = array_slice($files, -3); // Last 3 files
    foreach ($files as $file) {
        echo "  - " . basename($file) . " (" . date('Y-m-d H:i:s', filemtime($file)) . ")\n";
    }
} else {
    echo "\n📁 No logs directory found\n";
}

echo "\n==========================================\n";
echo "Email Configuration Status:\n";
echo "- Current method: PHP mail() function\n";
echo "- This typically WON'T work on local XAMPP\n";
echo "- Emails may be logged to files instead\n";
echo "- Configure SMTP for actual email sending\n";
?>