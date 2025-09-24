<?php
/**
 * Simple test script for Forgot Password functionality
 * Run this script to test the backend implementation
 */

require_once 'controllers/UserController.php';
require_once 'models/PasswordResetModel.php';
require_once 'services/EmailService.php';

echo "FARMMASTER Forgot Password Test Script\n";
echo "=====================================\n\n";

try {
    // Test 1: Check if PasswordResetModel works
    echo "Test 1: Testing PasswordResetModel...\n";
    $passwordResetModel = new PasswordResetModel();
    
    // Generate a test token
    $testToken = PasswordResetModel::generateToken();
    echo "Generated token: " . $testToken . "\n";
    
    // Test token creation
    $result = $passwordResetModel->createResetToken('test@example.com', $testToken, 3600);
    if ($result) {
        echo "✅ Token creation successful\n";
    } else {
        echo "❌ Token creation failed\n";
    }
    
    // Test token retrieval
    $retrievedToken = $passwordResetModel->findValidToken($testToken);
    if ($retrievedToken) {
        echo "✅ Token retrieval successful\n";
        echo "Token data: " . json_encode($retrievedToken) . "\n";
    } else {
        echo "❌ Token retrieval failed\n";
    }
    
    // Clean up test token
    $passwordResetModel->deleteToken($testToken);
    echo "🧹 Test token cleaned up\n\n";
    
    // Test 2: Check if EmailService works
    echo "Test 2: Testing EmailService...\n";
    $emailService = new EmailService();
    
    // Test with dummy user data
    $testUser = [
        'email' => 'test@example.com',
        'first_name' => 'Test',
        'last_name' => 'User'
    ];
    
    $testResetUrl = 'http://localhost:5173/reset-password?token=' . $testToken;
    
    echo "Attempting to send test email...\n";
    $emailResult = $emailService->sendPasswordResetEmail($testUser, $testResetUrl);
    
    if ($emailResult) {
        echo "✅ Email sending successful (check your mail configuration)\n";
    } else {
        echo "⚠️  Email sending may have issues (check error logs)\n";
    }
    
    echo "\nTest 3: Database Connection...\n";
    $database = Database::getInstance();
    $connection = $database->getConnection();
    
    if ($connection) {
        echo "✅ Database connection successful\n";
        
        // Check if password_resets table exists
        $stmt = $connection->prepare("SHOW TABLES LIKE 'password_resets'");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            echo "✅ password_resets table exists\n";
        } else {
            echo "❌ password_resets table does NOT exist\n";
            echo "Please create the table manually or run the SQL commands.\n";
        }
    } else {
        echo "❌ Database connection failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=====================================\n";
echo "Test completed. Check the output above for any issues.\n";
echo "If all tests pass, your forgot password feature should work!\n";
?>