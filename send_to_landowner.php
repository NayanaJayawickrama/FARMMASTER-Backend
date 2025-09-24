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

require_once 'config/Database.php';
require_once 'models/LandReportModel.php';

try {
    // Get the request data
    $input = json_decode(file_get_contents('php://input'), true);
    $reportId = $_GET['report_id'] ?? $input['report_id'] ?? null;
    
    if (!$reportId) {
        echo json_encode(['status' => 'error', 'message' => 'Report ID required']);
        exit();
    }

    // Initialize database and model
    $database = Database::getInstance();
    $db = $database->getConnection();
    $landReportModel = new LandReportModel($db);

    // Update the report status to "Sent to Owner"
    $updateData = [
        'status' => 'Sent to Owner',
        'sent_date' => date('Y-m-d H:i:s')
    ];
    
    $updateResult = $landReportModel->updateReport($reportId, $updateData);
    
    if (!$updateResult) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update report status']);
        exit();
    }

    // Get the updated report details
    $reportDetails = $landReportModel->getReportById($reportId);
    
    // For demonstration: Also create/update sent reports file
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
        'land_owner_id' => $reportDetails['land_owner_id'] ?? 32,
        'landowner_name' => $reportDetails['landowner_name'] ?? 'John Doe',
    ];
    
    // Save the updated sent reports
    file_put_contents($sentReportsFile, json_encode($sentReports, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Report successfully sent to land owner and status updated to: Sent to Owner',
        'data' => [
            'report_id' => $reportId,
            'status' => 'Sent to Owner',
            'sent_date' => date('Y-m-d H:i:s'),
            'database_updated' => true,
            'report_details' => $reportDetails
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>