<?php
require_once 'config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // For testing, let's activate Landowner role for user 32
    echo "Activating Landowner role for user 32...\n";
    $stmt = $db->prepare("UPDATE user_roles SET is_active = 1 WHERE user_id = 32 AND role = 'Landowner'");
    $result = $stmt->execute();
    
    if ($result) {
        echo "Success!\n";
    } else {
        echo "Failed to activate role\n";
    }
    
    // Also activate Buyer role for user 33 for testing
    echo "Activating Buyer role for user 33...\n";
    $stmt = $db->prepare("UPDATE user_roles SET is_active = 1 WHERE user_id = 33 AND role = 'Buyer'");
    $result = $stmt->execute();
    
    if ($result) {
        echo "Success!\n";
    } else {
        echo "Failed to activate role\n";
    }
    
    // Check the updated data
    echo "\nUpdated user_roles:\n";
    $stmt = $db->query("SELECT * FROM user_roles WHERE user_id IN (32, 33) ORDER BY user_id, role");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($roles);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>