<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Only POST method is allowed"]);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['proposal_id']) || !isset($input['action'])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$proposal_id = intval($input['proposal_id']);
$action = trim($input['action']); // 'accept' or 'reject'

if (!in_array($action, ['accept', 'reject'])) {
    echo json_encode(["error" => "Invalid action. Must be 'accept' or 'reject'"]);
    exit;
}

try {
    $status = ($action === 'accept') ? 'Accepted' : 'Rejected';
    
    // Update proposal status
    $sql = "UPDATE proposals SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE proposal_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $proposal_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Proposal " . strtolower($status) . " successfully",
            "proposal_id" => $proposal_id,
            "new_status" => $status
        ]);
    } else {
        echo json_encode(["error" => "Failed to update proposal status"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
?>
