<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
    // Get all proposals for the user with land information
    $sql = "SELECT 
                p.proposal_id,
                p.land_id,
                p.crop_type,
                p.estimated_yield,
                p.lease_duration_years,
                p.rental_value,
                p.profit_sharing_farmmaster,
                p.profit_sharing_landowner,
                p.estimated_profit_landowner,
                p.status,
                p.proposal_date,
                p.created_at,
                l.location,
                l.size
            FROM proposals p
            JOIN land l ON p.land_id = l.land_id
            WHERE p.user_id = ? 
            ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $proposals = [];
    
    while ($row = $result->fetch_assoc()) {
        $proposals[] = [
            "proposal_id" => $row["proposal_id"],
            "land_id" => $row["land_id"],
            "location" => $row["location"],
            "land_size" => $row["size"],
            "crop_type" => $row["crop_type"],
            "estimated_yield" => floatval($row["estimated_yield"]),
            "lease_duration_years" => intval($row["lease_duration_years"]),
            "rental_value" => floatval($row["rental_value"]),
            "profit_sharing_farmmaster" => floatval($row["profit_sharing_farmmaster"]),
            "profit_sharing_landowner" => floatval($row["profit_sharing_landowner"]),
            "estimated_profit_landowner" => floatval($row["estimated_profit_landowner"]),
            "status" => $row["status"],
            "proposal_date" => $row["proposal_date"],
            "created_at" => $row["created_at"]
        ];
    }

    echo json_encode($proposals);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch proposals: " . $e->getMessage()]);
}

$conn->close();
?>
