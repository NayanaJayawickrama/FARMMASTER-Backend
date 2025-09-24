<?php
// Dedicated endpoint for crop recommendations
// URL: /FARMMASTER-Backend/crop-recommendations.php?report_id=1

require_once 'config/Database.php';
require_once 'controllers/LandReportController.php';
require_once 'utils/Response.php';
require_once 'utils/SessionManager.php';

// CORS Headers - Fix for credentials mode
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:5174');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

try {
    // Get report ID from query parameter or JSON body
    $reportId = $_GET['report_id'] ?? null;
    
    if (!$reportId) {
        // Try to get from JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $reportId = $input['report_id'] ?? null;
    }
    
    if (!$reportId) {
        Response::error('Report ID is required', 400);
    }
    
    // Call the controller method directly
    $controller = new LandReportController();
    $controller->generateCropRecommendations($reportId);
    
} catch (Exception $e) {
    Response::error('Error generating recommendations: ' . $e->getMessage());
}
?>