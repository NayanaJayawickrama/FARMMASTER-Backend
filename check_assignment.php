<?php
require_once 'config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Check recent land_report assignments
$query = "SELECT lr.id as report_id, lr.land_id, lr.assigned_supervisor_id, lr.status, 
          u.first_name, u.last_name, l.location
          FROM land_report lr 
          LEFT JOIN user u ON lr.assigned_supervisor_id = u.user_id 
          LEFT JOIN land l ON lr.land_id = l.land_id
          WHERE lr.land_id = 69 OR lr.assigned_supervisor_id = 31
          ORDER BY lr.id DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Land Report Assignment Check:\n";
echo "============================\n";

if (empty($results)) {
    echo "No assignments found for land_id 69 or supervisor_id 31\n";
} else {
    foreach($results as $row) {
        echo sprintf("Report ID: %d | Land ID: %d | Supervisor: %s %s (ID: %d) | Status: %s\n", 
            $row['report_id'], 
            $row['land_id'], 
            $row['first_name'], 
            $row['last_name'], 
            $row['assigned_supervisor_id'], 
            $row['status']
        );
        echo sprintf("Location: %s\n", substr($row['location'], 0, 80));
        echo "---\n";
    }
}
?>