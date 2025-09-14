<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get user_id from query parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$user_id) {
    echo json_encode(["error" => "No user_id provided."]);
    exit;
}

try {
    // Get land reports for the specific user (landowner)
    $sql = "SELECT 
                lr.report_id AS id, 
                l.location, 
                CONCAT(u.first_name, ' ', u.last_name) AS name, 
                lr.status, 
                lr.land_id, 
                lr.user_id
            FROM land_report lr
            JOIN land l ON lr.land_id = l.land_id
            JOIN user u ON lr.user_id = u.user_id
            WHERE lr.user_id = ? 
            ORDER BY lr.report_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = "#2024-LR-" . str_pad($row['id'], 3, "0", STR_PAD_LEFT); // mimic frontend ID format
        $row['supervisor'] = "Unassigned"; // Placeholder, update if supervisor info is available
        $row['supervisorId'] = "";
        $reports[] = $row;
    }

    echo json_encode($reports);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch land reports: " . $e->getMessage()]);
}

$conn->close();
?>
$conn->close();
?>
