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

// Get report_id from query parameter
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

if (!$report_id) {
    echo json_encode(["error" => "No report_id provided."]);
    exit;
}

try {
    // Get detailed land report information
    $sql = "SELECT 
                lr.report_id,
                lr.land_id,
                lr.user_id,
                lr.report_date,
                lr.land_description,
                lr.crop_recomendation,
                lr.status,
                l.location,
                l.size,
                l.payment_status,
                u.first_name,
                u.last_name,
                u.email,
                u.phone
            FROM land_report lr
            JOIN land l ON lr.land_id = l.land_id
            JOIN user u ON lr.user_id = u.user_id
            WHERE lr.report_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "Report not found."]);
        exit;
    }

    $report = $result->fetch_assoc();
    
    // Format the response
    $response = [
        "report_id" => $report["report_id"],
        "land_id" => $report["land_id"],
        "user_id" => $report["user_id"],
        "report_date" => $report["report_date"],
        "land_description" => $report["land_description"],
        "crop_recommendation" => $report["crop_recomendation"],
        "status" => $report["status"],
        "land_info" => [
            "location" => $report["location"],
            "size" => $report["size"],
            "payment_status" => $report["payment_status"]
        ],
        "owner_info" => [
            "name" => $report["first_name"] . " " . $report["last_name"],
            "email" => $report["email"],
            "phone" => $report["phone"]
        ]
    ];

    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch report details: " . $e->getMessage()]);
}

$conn->close();
?>
