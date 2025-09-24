<?php
header('Content-Type: text/plain');

try {
    require_once 'config/Database.php';
    
    echo "=== FARMMASTER Password Reset Token Debug ===\n\n";
    
    // Test database connection
    echo "1. Testing database connection...\n";
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connected successfully!\n\n";
    
    // Check if password_resets table exists
    echo "2. Checking password_resets table...\n";
    $stmt = $connection->query("SHOW TABLES LIKE 'password_resets'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ password_resets table exists\n\n";
        
        // Get table structure
        echo "3. Table structure:\n";
        $stmt = $connection->query("DESCRIBE password_resets");
        $columns = $stmt->fetchAll();
        foreach ($columns as $column) {
            echo "   {$column['Field']} ({$column['Type']})\n";
        }
        echo "\n";
        
        // Check for recent tokens
        echo "4. Recent tokens (last 10):\n";
        $stmt = $connection->query("SELECT id, email, SUBSTRING(token, 1, 20) as token_preview, expires_at, 
                                   CASE WHEN expires_at > NOW() THEN 'Valid' ELSE 'Expired' END as status 
                                   FROM password_resets ORDER BY id DESC LIMIT 10");
        $tokens = $stmt->fetchAll();
        
        if (empty($tokens)) {
            echo "   No tokens found in database\n";
        } else {
            foreach ($tokens as $token) {
                echo "   ID: {$token['id']}, Email: {$token['email']}, Token: {$token['token_preview']}..., Expires: {$token['expires_at']}, Status: {$token['status']}\n";
            }
        }
        echo "\n";
        
        // Count valid tokens
        $stmt = $connection->query("SELECT COUNT(*) as count FROM password_resets WHERE expires_at > NOW()");
        $validCount = $stmt->fetch();
        echo "5. Valid (non-expired) tokens: {$validCount['count']}\n\n";
        
        // Check for tokens for specific email
        echo "6. Tokens for lo@gmail.com:\n";
        $stmt = $connection->prepare("SELECT id, SUBSTRING(token, 1, 30) as token_preview, expires_at,
                                     CASE WHEN expires_at > NOW() THEN 'Valid' ELSE 'Expired' END as status 
                                     FROM password_resets WHERE email = ? ORDER BY id DESC LIMIT 5");
        $stmt->execute(['lo@gmail.com']);
        $emailTokens = $stmt->fetchAll();
        
        if (empty($emailTokens)) {
            echo "   No tokens found for lo@gmail.com\n";
        } else {
            foreach ($emailTokens as $token) {
                echo "   ID: {$token['id']}, Token: {$token['token_preview']}..., Expires: {$token['expires_at']}, Status: {$token['status']}\n";
            }
        }
        
    } else {
        echo "❌ password_resets table does NOT exist!\n";
        echo "   You need to create the table first.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>