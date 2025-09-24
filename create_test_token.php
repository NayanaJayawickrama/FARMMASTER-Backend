<?php
require_once 'models/PasswordResetModel.php';
require_once 'models/UserModel.php';

echo "Creating fresh password reset token for testing...\n";

try {
    $passwordResetModel = new PasswordResetModel();
    $userModel = new UserModel();
    
    // Check if lo@gmail.com exists
    $user = $userModel->getUserByEmail('lo@gmail.com');
    
    if ($user) {
        echo "✅ User found: {$user['email']} (ID: {$user['user_id']})\n";
        
        // Generate fresh token
        $token = PasswordResetModel::generateToken();
        echo "Generated token: $token\n";
        
        // Create token (1 hour expiry)
        $result = $passwordResetModel->createResetToken('lo@gmail.com', $token, 3600);
        
        if ($result) {
            echo "✅ Fresh token created successfully!\n\n";
            echo "=== TEST INFORMATION ===\n";
            echo "Email: lo@gmail.com\n";
            echo "Token: $token\n";
            echo "Expires: " . date('Y-m-d H:i:s', time() + 3600) . "\n";
            echo "Reset URL: http://localhost:5173/reset-password?token=$token\n\n";
            echo "=== COPY THIS TOKEN FOR TESTING ===\n";
            echo "$token\n";
        } else {
            echo "❌ Failed to create token\n";
        }
        
    } else {
        echo "❌ User lo@gmail.com not found in database\n";
        echo "Available users:\n";
        $users = $userModel->getAllUsers();
        foreach (array_slice($users, 0, 5) as $u) {
            echo "  - {$u['email']} (ID: {$u['user_id']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>