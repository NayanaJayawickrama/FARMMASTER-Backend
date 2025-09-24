<?php
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "Checking password_resets table...\n";
    
    $stmt = $connection->query("SELECT * FROM password_resets ORDER BY id DESC LIMIT 10");
    $tokens = $stmt->fetchAll();
    
    if (empty($tokens)) {
        echo "No password reset tokens found in database.\n";
    } else {
        echo "Found " . count($tokens) . " tokens:\n";
        foreach ($tokens as $token) {
            echo "ID: {$token['id']}, Email: {$token['email']}, Token: " . substr($token['token'], 0, 20) . "..., Expires: {$token['expires_at']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>