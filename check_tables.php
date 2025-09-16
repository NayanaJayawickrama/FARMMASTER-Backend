<?php
// Check what tables exist
require_once 'config/Database.php';

echo "=== Database Tables Check ===\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Show all tables
    $stmt = $db->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);
    
    echo "Available tables:\n";
    foreach ($tables as $table) {
        echo "- {$table[0]}\n";
    }
    echo "\n";
    
    // Check if it's 'crop' table instead
    $stmt = $db->prepare("SHOW TABLES LIKE '%crop%'");
    $stmt->execute();
    $cropTables = $stmt->fetchAll(PDO::FETCH_NUM);
    
    if ($cropTables) {
        echo "Crop-related tables found:\n";
        foreach ($cropTables as $table) {
            echo "- {$table[0]}\n";
            
            // Check structure of crop table
            $stmt = $db->prepare("DESCRIBE {$table[0]}");
            $stmt->execute();
            $columns = $stmt->fetchAll();
            
            echo "  Columns:\n";
            foreach ($columns as $column) {
                echo "    - {$column['Field']}: {$column['Type']}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "=== Check Complete ===\n";
?>