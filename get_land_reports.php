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
    // Get land reports for the specific user (landowner)
    $sql = "SELECT 
                lr.report_id,
                lr.land_id,
                lr.report_date,
                lr.land_description,
                lr.crop_recomendation,
                lr.status,
                l.location,
                l.size,
                l.payment_status
            FROM land_report lr
            JOIN land l ON lr.land_id = l.land_id
            WHERE lr.user_id = ? 
            ORDER BY lr.report_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = [
            "report_id" => $row["report_id"],
            "land_id" => $row["land_id"],
            "report_date" => $row["report_date"],
            "land_description" => $row["land_description"],
            "crop_recommendation" => $row["crop_recomendation"],
            "status" => $row["status"],
            "location" => $row["location"],
            "size" => $row["size"],
            "payment_status" => $row["payment_status"]
        ];
    }

    echo json_encode($reports);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch land reports: " . $e->getMessage()]);
}

$conn->close();
?>
