<?php
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "Database connection successful!\n";
    
    // Check if land_report table exists
    $stmt = $connection->query("SHOW TABLES LIKE 'land_report'");
    $table_exists = $stmt->rowCount() > 0;
    
    if ($table_exists) {
        echo "land_report table exists!\n";
        
        // Check table structure
        $stmt = $connection->query("DESCRIBE land_report");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "  " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
        
        // Check if there's any data
        $stmt = $connection->query("SELECT COUNT(*) as total FROM land_report");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total records in land_report: " . $count['total'] . "\n";
        
        if ($count['total'] > 0) {
            // Show some sample data
            $stmt = $connection->query("SELECT * FROM land_report LIMIT 5");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Sample records:\n";
            foreach ($records as $record) {
                echo "  ID: " . $record['report_id'] . ", Status: " . ($record['status'] ?? 'NULL') . 
                     ", Completion: " . ($record['completion_status'] ?? 'NULL') . 
                     ", Land Desc: " . (isset($record['land_description']) && $record['land_description'] ? 'Yes' : 'No') . "\n";
            }
        }
    } else {
        echo "land_report table does NOT exist!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>