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
    // Get all lands for the user with their report status
    $sql = "SELECT 
                l.land_id,
                l.location,
                l.size,
                l.payment_status,
                l.created_at as request_date,
                l.payment_date,
                lr.report_id,
                lr.report_date,
                lr.land_description,
                lr.crop_recomendation,
                lr.ph_value,
                lr.organic_matter,
                lr.nitrogen_level,
                lr.phosphorus_level,
                lr.potassium_level,
                lr.environmental_notes,
                lr.status as report_status,
                CASE 
                    WHEN l.payment_status = 'pending' THEN 'Payment Pending'
                    WHEN l.payment_status = 'failed' THEN 'Payment Failed'
                    WHEN l.payment_status = 'paid' AND lr.report_id IS NULL THEN 'Assessment Pending'
                    WHEN l.payment_status = 'paid' AND lr.report_id IS NOT NULL THEN COALESCE(lr.status, 'Report Submitted')
                    ELSE 'Unknown'
                END as overall_status
            FROM land l
            LEFT JOIN land_report lr ON l.land_id = lr.land_id
            WHERE l.user_id = ? 
            ORDER BY l.created_at DESC, lr.report_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $assessments = [];
    $processed_lands = [];
    
    while ($row = $result->fetch_assoc()) {
        $land_id = $row['land_id'];
        
        // Only process each land once (get the latest report if multiple exist)
        if (!in_array($land_id, $processed_lands)) {
            $assessments[] = [
                "land_id" => $row["land_id"],
                "location" => $row["location"],
                "size" => $row["size"],
                "payment_status" => $row["payment_status"],
                "request_date" => $row["request_date"],
                "payment_date" => $row["payment_date"],
                "report_id" => $row["report_id"],
                "report_date" => $row["report_date"],
                "land_description" => $row["land_description"],
                "crop_recommendation" => $row["crop_recomendation"],
                "ph_value" => $row["ph_value"],
                "organic_matter" => $row["organic_matter"],
                "nitrogen_level" => $row["nitrogen_level"],
                "phosphorus_level" => $row["phosphorus_level"],
                "potassium_level" => $row["potassium_level"],
                "environmental_notes" => $row["environmental_notes"],
                "report_status" => $row["report_status"],
                "overall_status" => $row["overall_status"],
                "has_report" => !empty($row["report_id"]),
                "is_paid" => $row["payment_status"] === 'paid'
            ];
            $processed_lands[] = $land_id;
        }
    }

    echo json_encode($assessments);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch assessment requests: " . $e->getMessage()]);
}

$conn->close();
?>
