<?php
require_once 'config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check user 32's password hash
    $stmt = $db->prepare("SELECT user_id, first_name, email, user_role, password FROM user WHERE user_id = 32");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "User 32 data:\n";
    print_r($user);
    
    // Test common passwords
    $passwords = ['password', 'password123', '123456', 'admin', 'test'];
    
    echo "\nTesting common passwords:\n";
    foreach ($passwords as $pass) {
        if (password_verify($pass, $user['password'])) {
            echo "Password '$pass' matches!\n";
            break;
        } else {
            echo "Password '$pass' does not match\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>