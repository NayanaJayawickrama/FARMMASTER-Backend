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
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'User ID required']);
        exit();
    }

    $landReportModel = new LandReportModel();
    $reports = $landReportModel->getLandOwnerReports($userId);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Land owner reports retrieved successfully',
        'data' => $reports,
        'count' => count($reports)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>