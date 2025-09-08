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
    // Get all harvest data for the user with land information
    $sql = "SELECT 
                h.harvest_id,
                h.land_id,
                h.proposal_id,
                h.harvest_date,
                h.product_type,
                h.harvest_amount,
                h.income,
                h.expenses,
                h.land_rent,
                h.net_profit,
                h.landowner_share,
                h.farmmaster_share,
                h.notes,
                h.created_at,
                l.location,
                l.size,
                p.status as proposal_status
            FROM harvest h
            JOIN land l ON h.land_id = l.land_id
            LEFT JOIN proposals p ON h.proposal_id = p.proposal_id
            WHERE h.user_id = ? 
            ORDER BY h.harvest_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $harvests = [];
    
    while ($row = $result->fetch_assoc()) {
        $harvests[] = [
            "harvest_id" => $row["harvest_id"],
            "land_id" => $row["land_id"],
            "proposal_id" => $row["proposal_id"],
            "location" => $row["location"],
            "land_size" => $row["size"],
            "harvest_date" => $row["harvest_date"],
            "product_type" => $row["product_type"],
            "harvest_amount" => floatval($row["harvest_amount"]),
            "income" => floatval($row["income"]),
            "expenses" => floatval($row["expenses"]),
            "land_rent" => floatval($row["land_rent"]),
            "net_profit" => floatval($row["net_profit"]),
            "landowner_share" => floatval($row["landowner_share"]),
            "farmmaster_share" => floatval($row["farmmaster_share"]),
            "notes" => $row["notes"],
            "proposal_status" => $row["proposal_status"],
            "created_at" => $row["created_at"]
        ];
    }

    echo json_encode($harvests);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch harvest data: " . $e->getMessage()]);
}

$conn->close();
?>
