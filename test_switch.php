<?php
require_once 'config/Database.php';
require_once 'models/UserModel.php';
require_once 'controllers/UserController.php';
require_once 'utils/SessionManager.php';
require_once 'utils/Response.php';
require_once 'utils/Validator.php';

// Simulate a login session for user 32 (Landowner with Buyer role available)
session_start();
$_SESSION['user_id'] = 32;
$_SESSION['user_role'] = 'Buyer'; // Currently active as Buyer
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();

try {
    $userModel = new UserModel();
    
    echo "User 32 available roles:\n";
    $availableRoles = $userModel->getUserAvailableRoles(32);
    print_r($availableRoles);
    
    echo "\nTesting switch to Landowner:\n";
    $controller = new UserController();
    
    // Simulate POST data
    $postData = json_encode(['role' => 'Landowner']);
    file_put_contents('php://memory', $postData);
    
    // This would normally be called via HTTP, but we'll test the logic
    echo "Switch role logic would execute here\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>