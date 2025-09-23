<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    echo "=== Checking land_report table ===\n\n";
    
    // Count total records
    $stmt = $db->query("SELECT COUNT(*) as total FROM land_report");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records in land_report: " . $count['total'] . "\n\n";
    
    // Show table structure
    echo "Table Structure:\n";
    $stmt = $db->query("DESCRIBE land_report");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    echo "\n=== Sample Data ===\n";
    $stmt = $db->query("SELECT * FROM land_report LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Report ID: " . $row['report_id'] . "\n";
        echo "Land ID: " . $row['land_id'] . "\n";
        echo "User ID: " . $row['user_id'] . "\n";
        echo "Status: " . ($row['status'] ?? 'NULL') . "\n";
        echo "Environmental Notes: " . substr($row['environmental_notes'] ?? 'NULL', 0, 100) . "\n";
        echo "---\n";
    }
    
    echo "\n=== Reports with Supervisor Assignment ===\n";
    $stmt = $db->query("SELECT report_id, land_id, status, environmental_notes FROM land_report WHERE environmental_notes LIKE '%Assigned to:%'");
    $assignedReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Reports with supervisor assignment: " . count($assignedReports) . "\n";
    foreach ($assignedReports as $report) {
        echo "Report ID: " . $report['report_id'] . " - Status: " . ($report['status'] ?? 'NULL') . "\n";
    }
    
    echo "\n=== Land Payment Status ===\n";
    $stmt = $db->query("SELECT l.land_id, l.payment_status, COUNT(lr.report_id) as report_count 
                       FROM land l 
                       LEFT JOIN land_report lr ON l.land_id = lr.land_id 
                       GROUP BY l.land_id, l.payment_status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Land ID: " . $row['land_id'] . " - Payment: " . $row['payment_status'] . " - Reports: " . $row['report_count'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>