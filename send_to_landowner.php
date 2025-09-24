<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get the request data
    $input = json_decode(file_get_contents('php://input'), true);
    $reportId = $_GET['report_id'] ?? $input['report_id'] ?? null;
    
    if (!$reportId) {
        echo json_encode(['status' => 'error', 'message' => 'Report ID required']);
        exit();
    }

    // For demonstration: Create/update sent reports file
    $sentReportsFile = 'sent_reports.json';
    $sentReports = [];
    
    if (file_exists($sentReportsFile)) {
        $sentReports = json_decode(file_get_contents($sentReportsFile), true) ?: [];
    }
    
    // Add this report as sent
    $sentReports[$reportId] = [
        'report_id' => $reportId,
        'sent_date' => date('Y-m-d H:i:s'),
        'status' => 'Sent to Owner',
        'land_owner_id' => 32, // Demo land owner ID
        'landowner_name' => 'John Doe', // Demo name
    ];
    
    // Save the updated sent reports
    file_put_contents($sentReportsFile, json_encode($sentReports, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Report successfully sent to land owner: ' . $sentReports[$reportId]['landowner_name'],
        'data' => $sentReports[$reportId]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>