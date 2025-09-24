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
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'User ID required']);
        exit();
    }

    // Use MySQLi since PDO MySQL driver is not available
    $mysqli = new mysqli("localhost", "root", "", "farm_master#");
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    // Get all reports for this user, especially those sent to owner
    $stmt = $mysqli->prepare("SELECT 
                        lr.report_id,
                        lr.land_id,
                        lr.user_id,
                        lr.report_date,
                        lr.status,
                        lr.environmental_notes,
                        lr.land_description,
                        lr.crop_recomendation,
                        lr.ph_value,
                        lr.organic_matter,
                        lr.nitrogen_level,
                        lr.phosphorus_level,
                        lr.potassium_level,
                        l.location,
                        l.size,
                        CONCAT(u.first_name, ' ', u.last_name) as landowner_name,
                        CASE 
                            WHEN lr.status = 'Sent to Owner' THEN 'Completed - Ready to View'
                            WHEN lr.status = 'Approved' THEN 'Approved'
                            WHEN lr.status = 'Rejected' THEN 'Needs Revision'
                            ELSE lr.status
                        END as display_status
                    FROM land_report lr
                    LEFT JOIN land l ON lr.land_id = l.land_id
                    JOIN user u ON lr.user_id = u.user_id
                    WHERE lr.user_id = ?
                    ORDER BY lr.report_date DESC");
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = $result->fetch_all(MYSQLI_ASSOC);
    
    // Format reports for frontend
    $formattedReports = [];
    foreach ($reports as $report) {
        $formattedReports[] = [
            'id' => '#' . date('Y') . '-LR-' . str_pad($report['report_id'], 3, '0', STR_PAD_LEFT),
            'report_id' => $report['report_id'],
            'location' => $report['location'],
            'landowner_name' => $report['landowner_name'],
            'status' => $report['display_status'],
            'report_date' => $report['report_date'],
            'land_id' => $report['land_id'],
            'user_id' => $report['user_id'],
            'is_completed' => in_array($report['status'], ['Sent to Owner', 'Approved']),
            'can_view_full_report' => in_array($report['status'], ['Sent to Owner', 'Approved']),
            'report_details' => [
                'land_description' => $report['land_description'],
                'crop_recommendation' => $report['crop_recomendation'],
                'ph_value' => $report['ph_value'],
                'organic_matter' => $report['organic_matter'],
                'nitrogen_level' => $report['nitrogen_level'],
                'phosphorus_level' => $report['phosphorus_level'],
                'potassium_level' => $report['potassium_level'],
                'environmental_notes' => $report['environmental_notes']
            ]
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Land owner reports retrieved successfully',
        'data' => $formattedReports,
        'total_reports' => count($formattedReports),
        'completed_reports' => count(array_filter($formattedReports, function($r) { return $r['is_completed']; }))
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>