<?php
require_once 'config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check users table
    echo "Users with Landowner/Buyer roles:\n";
    $stmt = $db->query("SELECT user_id, first_name, email, user_role, current_active_role FROM user WHERE user_role IN ('Landowner', 'Buyer')");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
    
    echo "\nUser roles table:\n";
    $stmt = $db->query("SELECT * FROM user_roles ORDER BY user_id");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($roles);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>