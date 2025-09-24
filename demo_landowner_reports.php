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
    $landOwnerId = $_GET['land_owner_id'] ?? null;
    
    if (!$landOwnerId) {
        echo json_encode(['status' => 'error', 'message' => 'Land owner ID required']);
        exit();
    }

    // For demonstration: Read sent reports file
    $sentReportsFile = 'sent_reports.json';
    $reports = [];
    
    if (file_exists($sentReportsFile)) {
        $sentReports = json_decode(file_get_contents($sentReportsFile), true) ?: [];
        
        // Filter reports for this land owner (for demo, all reports will be for owner 32)
        foreach ($sentReports as $reportId => $report) {
            if ($report['land_owner_id'] == $landOwnerId) {
                $reports[] = [
                    'report_id' => $reportId,
                    'land_id' => 'Sample Land ' . $reportId,
                    'report_type' => 'Supervisor Report',
                    'report_description' => 'Demo land report #' . $reportId,
                    'status' => $report['status'],
                    'created_at' => $report['sent_date'],
                    'landowner_name' => $report['landowner_name']
                ];
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $reports,
        'count' => count($reports)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>