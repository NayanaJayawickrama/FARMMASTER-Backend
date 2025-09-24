<?php
/**
 * Gmail SMTP Test Script
 * This will test if your Gmail credentials are working
 */

require_once 'services/EmailService.php';

echo "🔧 Testing Gmail SMTP Configuration\n";
echo "==================================\n\n";

try {
    $emailService = new EmailService();
    
    echo "📧 Attempting to send test email...\n";
    
    // Send test email to your own Gmail
    $result = $emailService->sendTestEmail('radeeshapraneeth531@gmail.com');
    
    if ($result) {
        echo "✅ SUCCESS! Test email sent successfully!\n";
        echo "📬 Check your Gmail inbox for the test email\n";
        echo "🎉 Gmail SMTP is now configured and working!\n\n";
        
        echo "🔄 Now testing password reset email format...\n";
        
        // Test password reset email format
        $testUser = [
            'email' => 'radeeshapraneeth531@gmail.com',
            'first_name' => 'Radeesha',
            'last_name' => 'Praneeth'
        ];
        
        $testResetUrl = 'http://localhost:5173/reset-password?token=test123456789';
        
        $resetResult = $emailService->sendPasswordResetEmail($testUser, $testResetUrl);
        
        if ($resetResult) {
            echo "✅ Password reset email sent successfully!\n";
            echo "📬 Check your Gmail for the password reset email\n";
            echo "🔗 Test reset URL: $testResetUrl\n";
        } else {
            echo "❌ Password reset email failed\n";
        }
        
    } else {
        echo "❌ FAILED to send test email\n";
        echo "Check your Gmail credentials and internet connection\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nPossible issues:\n";
    echo "1. Check your Gmail App Password is correct\n";
    echo "2. Ensure 2-factor authentication is enabled on Gmail\n";
    echo "3. Check your internet connection\n";
    echo "4. Verify PHPMailer is properly installed\n";
}

echo "\n==================================\n";
echo "Gmail Configuration Summary:\n";
echo "- Email: radeeshapraneeth531@gmail.com\n";
echo "- SMTP: smtp.gmail.com:587\n";
echo "- Security: STARTTLS\n";
echo "- Status: " . ($result ?? false ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
?>