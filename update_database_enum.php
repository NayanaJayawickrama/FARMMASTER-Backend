<?php
// Database update script to fix the status ENUM issue
header('Content-Type: application/json');

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'farm_master#';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "Connected to database successfully.\n";
    
    // First, check current ENUM values
    $checkEnum = $pdo->query("SHOW COLUMNS FROM land_report WHERE Field = 'status'");
    $enumInfo = $checkEnum->fetch(PDO::FETCH_ASSOC);
    echo "Current status ENUM: " . $enumInfo['Type'] . "\n";
    
    // Update the ENUM to include 'Sent to Owner'
    $alterQuery = "ALTER TABLE land_report 
                   MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected', 'Not Reviewed', 'Under Review', 'Sent to Owner') 
                   DEFAULT 'Pending'";
    
    $pdo->exec($alterQuery);
    echo "✅ Successfully updated status ENUM to include 'Sent to Owner'\n";
    
    // Verify the change
    $checkEnumAfter = $pdo->query("SHOW COLUMNS FROM land_report WHERE Field = 'status'");
    $enumInfoAfter = $checkEnumAfter->fetch(PDO::FETCH_ASSOC);
    echo "Updated status ENUM: " . $enumInfoAfter['Type'] . "\n";
    
    // Test updating a record
    $testUpdate = $pdo->prepare("UPDATE land_report SET status = 'Sent to Owner' WHERE report_id = 21");
    $testUpdate->execute();
    $rowsAffected = $testUpdate->rowCount();
    echo "✅ Test update affected {$rowsAffected} rows\n";
    
    // Verify the update worked
    $checkUpdate = $pdo->prepare("SELECT report_id, status FROM land_report WHERE report_id = 21");
    $checkUpdate->execute();
    $result = $checkUpdate->fetch(PDO::FETCH_ASSOC);
    echo "Report 21 status after update: " . $result['status'] . "\n";
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database successfully updated to support Sent to Owner status',
        'data' => [
            'old_enum' => $enumInfo['Type'],
            'new_enum' => $enumInfoAfter['Type'],
            'test_update_rows' => $rowsAffected,
            'report_21_status' => $result['status']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database update failed: ' . $e->getMessage()
    ]);
}
?>