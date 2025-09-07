<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

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
    // Get user's lands with their latest report status
    $sql = "SELECT 
                l.land_id,
                l.location,
                l.size,
                l.payment_status,
                l.created_at,
                lr.report_id,
                lr.report_date,
                lr.status as report_status,
                lr.land_description,
                lr.crop_recomendation
            FROM land l
            LEFT JOIN land_report lr ON l.land_id = lr.land_id
            WHERE l.user_id = ? AND l.payment_status = 'paid'
            ORDER BY l.created_at DESC, lr.report_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $lands = [];
    $processed_lands = [];
    
    while ($row = $result->fetch_assoc()) {
        $land_id = $row['land_id'];
        
        // Only process each land once (get the latest report)
        if (!in_array($land_id, $processed_lands)) {
            $lands[] = [
                "land_id" => $row["land_id"],
                "location" => $row["location"],
                "size" => $row["size"],
                "payment_status" => $row["payment_status"],
                "created_at" => $row["created_at"],
                "report_id" => $row["report_id"],
                "report_date" => $row["report_date"],
                "report_status" => $row["report_status"] ?: 'No Report',
                "has_report" => !empty($row["report_id"]),
                "land_description" => $row["land_description"],
                "crop_recommendation" => $row["crop_recomendation"]
            ];
            $processed_lands[] = $land_id;
        }
    }

    echo json_encode($lands);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch land data: " . $e->getMessage()]);
}

$conn->close();
?>
