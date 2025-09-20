<?php

require_once __DIR__ . '/../models/LandModel.php';
require_once __DIR__ . '/../models/LandReportModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class LandController {
    private $landModel;
    private $reportModel;
    private $validPaymentStatus = ['pending', 'paid', 'failed'];
    private $validReportStatus = ['pending', 'completed', 'reviewed', 'approved'];

    public function __construct() {
        $this->landModel = new LandModel();
        $this->reportModel = new LandReportModel();
    }

    public function getAllLands() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager', 'Field Supervisor']);

            $filters = [];
            
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['payment_status'])) {
                $filters['payment_status'] = $_GET['payment_status'];
            }
            if (isset($_GET['location'])) {
                $filters['location'] = $_GET['location'];
            }

            $lands = $this->landModel->getAllLands($filters);
            
            Response::success("Lands retrieved successfully", $lands);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getLand($landId) {
        try {
            SessionManager::requireAuth();

            $land = $this->landModel->getLandById($landId);
            
            if (!$land) {
                Response::notFound("Land not found");
            }

            // Check if user can access this land
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($land['user_id'] != $currentUserId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor'])) {
                Response::forbidden("Access denied");
            }
            
            Response::success("Land retrieved successfully", $land);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getUserLands($userId = null) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Use current user ID if not provided or if not manager
            if (!$userId || (!in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor']))) {
                $userId = $currentUserId;
            }

            $lands = $this->landModel->getUserLands($userId);
            
            Response::success("User lands retrieved successfully", $lands);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function addLand() {
        try {
            SessionManager::requireRole(['Landowner']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $userId = SessionManager::getCurrentUserId();
            $size = Validator::required($data['size'] ?? '', 'Size');
            $size = Validator::numeric($size, 'Land size', 0.1); // Minimum 0.1 acres
            $location = Validator::required($data['location'] ?? '', 'Location');
            $paymentStatus = isset($data['payment_status']) ? 
                           Validator::inArray($data['payment_status'], $this->validPaymentStatus, 'Payment status') : 
                           'pending';

            $landData = [
                'user_id' => $userId,
                'size' => $size,
                'location' => $location,
                'payment_status' => $paymentStatus
            ];

            if (isset($data['payment_date'])) {
                $landData['payment_date'] = $data['payment_date'];
            }

            $result = $this->landModel->addLand($landData);

            if ($result['success']) {
                Response::success($result['message'], $result, 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateLand($landId) {
        try {
            SessionManager::requireAuth();

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Check if land exists and user has permission
            $land = $this->landModel->getLandById($landId);
            if (!$land) {
                Response::notFound("Land not found");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($land['user_id'] != $currentUserId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager'])) {
                Response::forbidden("Access denied");
            }

            $landData = [];

            if (isset($data['size'])) {
                $size = Validator::required($data['size'], 'Size');
                $landData['size'] = Validator::numeric($size, 'Land size', 0.1); // Minimum 0.1 acres
            }
            if (isset($data['location'])) {
                $landData['location'] = Validator::required($data['location'], 'Location');
            }
            if (isset($data['payment_status']) && in_array($currentRole, ['Operational_Manager', 'Financial_Manager'])) {
                $landData['payment_status'] = Validator::inArray($data['payment_status'], $this->validPaymentStatus, 'Payment status');
            }
            if (isset($data['payment_date']) && in_array($currentRole, ['Operational_Manager', 'Financial_Manager'])) {
                $landData['payment_date'] = $data['payment_date'];
            }

            if (empty($landData)) {
                Response::error("No valid fields to update");
            }

            $result = $this->landModel->updateLand($landId, $landData);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updatePaymentStatus($landId) {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['payment_status'])) {
                Response::error("Payment status is required");
            }

            $paymentStatus = Validator::inArray($data['payment_status'], $this->validPaymentStatus, 'Payment status');
            $paymentDate = $data['payment_date'] ?? null;

            if ($paymentStatus === 'paid' && !$paymentDate) {
                $paymentDate = date('Y-m-d H:i:s');
            }

            $result = $this->landModel->updatePaymentStatus($landId, $paymentStatus, $paymentDate);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function deleteLand($landId) {
        try {
            SessionManager::requireRole(['Operational_Manager']);

            $result = $this->landModel->deleteLand($landId);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchLands() {
        try {
            SessionManager::requireAuth();

            $searchTerm = $_GET['search'] ?? '';

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Regular users can only search their own lands
            $userId = in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor']) ? null : $currentUserId;

            $lands = $this->landModel->searchLands($searchTerm, $userId);

            Response::success("Search completed", $lands);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getLandStats() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);

            $stats = $this->landModel->getLandStats();

            Response::success("Land statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    // Land Report methods
    public function getAllReports() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager', 'Field Supervisor']);

            $filters = [];
            
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['land_id'])) {
                $filters['land_id'] = $_GET['land_id'];
            }

            $reports = $this->reportModel->getAllReports($filters);
            
            Response::success("Reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getReport($reportId) {
        try {
            SessionManager::requireAuth();

            $report = $this->reportModel->getReportById($reportId);
            
            if (!$report) {
                Response::notFound("Report not found");
            }

            // Check if user can access this report
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($report['user_id'] != $currentUserId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor'])) {
                Response::forbidden("Access denied");
            }
            
            Response::success("Report retrieved successfully", $report);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getUserReports($userId = null) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Use current user ID if not provided or if not manager
            if (!$userId || (!in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor']))) {
                $userId = $currentUserId;
            }

            $reports = $this->reportModel->getUserReports($userId);
            
            Response::success("User reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getLandReports($landId) {
        try {
            SessionManager::requireAuth();

            // Check if user can access this land's reports
            $land = $this->landModel->getLandById($landId);
            if (!$land) {
                Response::notFound("Land not found");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($land['user_id'] != $currentUserId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor'])) {
                Response::forbidden("Access denied");
            }

            $reports = $this->reportModel->getLandReports($landId);
            
            Response::success("Land reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function addReport() {
        try {
            SessionManager::requireRole(['Field Supervisor', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $landId = Validator::required($data['land_id'] ?? '', 'Land ID');
            $userId = Validator::required($data['user_id'] ?? '', 'User ID');
            $landDescription = Validator::required($data['land_description'] ?? '', 'Land description');
            $cropRecommendation = Validator::required($data['crop_recomendation'] ?? '', 'Crop recommendation');

            // Check if land exists and is paid
            $land = $this->landModel->getLandById($landId);
            if (!$land) {
                Response::notFound("Land not found");
            }
            if ($land['payment_status'] !== 'paid') {
                Response::error("Payment must be completed before generating report");
            }

            $reportData = [
                'land_id' => $landId,
                'user_id' => $userId,
                'land_description' => $landDescription,
                'crop_recomendation' => $cropRecommendation
            ];

            // Optional fields
            if (isset($data['ph_value'])) {
                $reportData['ph_value'] = Validator::numeric($data['ph_value'], 'pH Value', 0, 14);
            }
            if (isset($data['organic_matter'])) {
                $reportData['organic_matter'] = Validator::numeric($data['organic_matter'], 'Organic Matter', 0);
            }
            if (isset($data['nitrogen_level'])) {
                $reportData['nitrogen_level'] = Validator::numeric($data['nitrogen_level'], 'Nitrogen Level', 0);
            }
            if (isset($data['phosphorus_level'])) {
                $reportData['phosphorus_level'] = Validator::numeric($data['phosphorus_level'], 'Phosphorus Level', 0);
            }
            if (isset($data['potassium_level'])) {
                $reportData['potassium_level'] = Validator::numeric($data['potassium_level'], 'Potassium Level', 0);
            }
            if (isset($data['environmental_notes'])) {
                $reportData['environmental_notes'] = Validator::sanitizeString($data['environmental_notes']);
            }
            if (isset($data['status'])) {
                $reportData['status'] = Validator::inArray($data['status'], $this->validReportStatus, 'Status');
            }

            $result = $this->reportModel->addReport($reportData);

            if ($result['success']) {
                Response::success($result['message'], $result, 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateReport($reportId) {
        try {
            SessionManager::requireRole(['Field Supervisor', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Check if report exists
            $existingReport = $this->reportModel->getReportById($reportId);
            if (!$existingReport) {
                Response::notFound("Report not found");
            }

            $reportData = [];

            if (isset($data['land_description'])) {
                $reportData['land_description'] = Validator::required($data['land_description'], 'Land description');
            }
            if (isset($data['crop_recomendation'])) {
                $reportData['crop_recomendation'] = Validator::required($data['crop_recomendation'], 'Crop recommendation');
            }
            if (isset($data['ph_value'])) {
                $reportData['ph_value'] = Validator::numeric($data['ph_value'], 'pH Value', 0, 14);
            }
            if (isset($data['organic_matter'])) {
                $reportData['organic_matter'] = Validator::numeric($data['organic_matter'], 'Organic Matter', 0);
            }
            if (isset($data['nitrogen_level'])) {
                $reportData['nitrogen_level'] = Validator::numeric($data['nitrogen_level'], 'Nitrogen Level', 0);
            }
            if (isset($data['phosphorus_level'])) {
                $reportData['phosphorus_level'] = Validator::numeric($data['phosphorus_level'], 'Phosphorus Level', 0);
            }
            if (isset($data['potassium_level'])) {
                $reportData['potassium_level'] = Validator::numeric($data['potassium_level'], 'Potassium Level', 0);
            }
            if (isset($data['environmental_notes'])) {
                $reportData['environmental_notes'] = Validator::sanitizeString($data['environmental_notes']);
            }
            if (isset($data['status'])) {
                $reportData['status'] = Validator::inArray($data['status'], $this->validReportStatus, 'Status');
            }

            if (empty($reportData)) {
                Response::error("No valid fields to update");
            }

            $result = $this->reportModel->updateReport($reportId, $reportData);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateReportStatus($reportId) {
        try {
            SessionManager::requireRole(['Field Supervisor', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['status'])) {
                Response::error("Status is required");
            }

            $status = Validator::inArray($data['status'], $this->validReportStatus, 'Status');

            $result = $this->reportModel->updateReportStatus($reportId, $status);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function deleteReport($reportId) {
        try {
            SessionManager::requireRole(['Operational_Manager']);

            $result = $this->reportModel->deleteReport($reportId);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getReportStats() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager', 'Field Supervisor']);

            $stats = $this->reportModel->getReportStats();

            Response::success("Report statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAssessmentRequests($userId = null) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Use current user ID if not provided or if not manager
            if (!$userId || (!in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor']))) {
                $userId = $currentUserId;
            }

            $assessments = $this->reportModel->getAssessmentRequests($userId);

            Response::success("Assessment requests retrieved successfully", $assessments);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function downloadLandReportPDF($reportId) {
        try {
            SessionManager::requireAuth();

            $report = $this->reportModel->getReportById($reportId);
            
            if (!$report) {
                Response::notFound("Report not found");
            }

            // Check if user can access this report
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($report['user_id'] != $currentUserId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor'])) {
                Response::forbidden("Access denied");
            }

            // For now, return the report data (PDF generation can be implemented later)
            Response::success("Report data for PDF generation", $report);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function generateLandReportPDF($reportId) {
        try {
            SessionManager::requireAuth();

            // Validate report ID
            if (empty($reportId) || !is_numeric($reportId)) {
                echo "<h1>Error: Invalid report ID</h1>";
                exit;
            }

            $report = $this->reportModel->getReportById($reportId);
            
            if (!$report) {
                echo "<h1>Error: Report not found</h1>";
                exit;
            }

            // Check if user can access this report
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($report['user_id'] != $currentUserId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager', 'Field Supervisor'])) {
                echo "<h1>Error: Access denied</h1>";
                exit;
            }

            // Get associated land data
            $landData = $this->landModel->getLandById($report['land_id']);
            
            if (!$landData) {
                echo "<h1>Error: Associated land data not found</h1>";
                exit;
            }

            // Generate HTML for PDF printing
            $this->renderLandReportHTML($report, $landData);

        } catch (Exception $e) {
            echo "<h1>Error: " . htmlspecialchars($e->getMessage()) . "</h1>";
        }
    }

    private function renderLandReportHTML($report, $landData) {
        // Set content type to HTML
        header('Content-Type: text/html; charset=utf-8');
        
        // Get current date
        $currentDate = date('Y-m-d H:i:s');
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Land Assessment Report - Report #<?php echo htmlspecialchars($report['report_id']); ?></title>
            <style>
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                    @page { margin: 0.75in; }
                }
                
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.4;
                    margin: 20px;
                    color: #333;
                    font-size: 14px;
                }
                
                .header {
                    text-align: center;
                    border-bottom: 2px solid #2d5a27;
                    padding-bottom: 15px;
                    margin-bottom: 25px;
                }
                
                .logo {
                    font-size: 22px;
                    font-weight: bold;
                    color: #2d5a27;
                    margin-bottom: 5px;
                }
                
                .subtitle {
                    font-size: 16px;
                    color: #666;
                    margin-bottom: 8px;
                }
                
                .report-info {
                    background-color: #f8f9fa;
                    padding: 12px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                }
                
                .report-info table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 13px;
                }
                
                .report-info td {
                    padding: 6px 10px;
                    border-bottom: 1px solid #ddd;
                }
                
                .report-info td:first-child {
                    font-weight: bold;
                    background-color: #e9ecef;
                    width: 25%;
                }
                
                .section {
                    margin-bottom: 25px;
                }
                
                .section h2 {
                    color: #2d5a27;
                    border-bottom: 1px solid #2d5a27;
                    padding-bottom: 3px;
                    margin-bottom: 12px;
                    font-size: 16px;
                }
                
                .soil-analysis {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin-bottom: 15px;
                }
                
                .analysis-item {
                    background-color: #f8f9fa;
                    padding: 12px;
                    border-radius: 4px;
                    border-left: 3px solid #28a745;
                }
                
                .analysis-label {
                    font-weight: bold;
                    color: #2d5a27;
                    display: block;
                    margin-bottom: 4px;
                    font-size: 12px;
                }
                
                .analysis-value {
                    font-size: 15px;
                    color: #333;
                }
                
                .print-btn {
                    position: fixed;
                    top: 15px;
                    right: 15px;
                    padding: 8px 16px;
                    background-color: #28a745;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .print-btn:hover {
                    background-color: #218838;
                }
                
                .content-box {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 4px;
                    margin-bottom: 15px;
                }
                
                .content-box h3 {
                    color: #2d5a27;
                    margin-top: 0;
                    margin-bottom: 10px;
                    font-size: 14px;
                }
                
                .content-box p {
                    margin: 0;
                    font-size: 13px;
                }
                
                .footer {
                    margin-top: 40px;
                    text-align: center;
                    font-size: 11px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 15px;
                }
                
                .recommendation-box {
                    background-color: #e8f5e8;
                    padding: 15px;
                    border-radius: 4px;
                    border-left: 4px solid #28a745;
                }
                
                .notes-box {
                    background-color: #fff3cd;
                    padding: 15px;
                    border-radius: 4px;
                    border-left: 4px solid #ffc107;
                }
            </style>
        </head>
        <body>
            <button class="print-btn no-print" onclick="window.print()">🖨️ Print PDF</button>
            
            <div class="header">
                <div class="logo">FARM MASTER</div>
                <div class="subtitle">Land Assessment Report</div>
                <div style="font-size: 14px;">Report ID: #<?php echo htmlspecialchars($report['report_id']); ?></div>
            </div>
            
            <div class="report-info">
                <table>
                    <tr>
                        <td>Report ID</td>
                        <td><?php echo htmlspecialchars($report['report_id']); ?></td>
                    </tr>
                    <tr>
                        <td>Land ID</td>
                        <td><?php echo htmlspecialchars($landData['land_id']); ?></td>
                    </tr>
                    <tr>
                        <td>Land Size</td>
                        <td><?php echo htmlspecialchars($landData['size']); ?> acres</td>
                    </tr>
                    <tr>
                        <td>Location</td>
                        <td><?php echo htmlspecialchars($landData['location']); ?></td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td><?php echo ucfirst(htmlspecialchars($landData['payment_status'])); ?></td>
                    </tr>
                    <tr>
                        <td>Report Status</td>
                        <td><?php echo ucfirst(htmlspecialchars($report['status'])); ?></td>
                    </tr>
                    <tr>
                        <td>Generated Date</td>
                        <td><?php echo $currentDate; ?></td>
                    </tr>
                    <tr>
                        <td>Assessment Date</td>
                        <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <h2>🌱 Soil Analysis Results</h2>
                <div class="soil-analysis">
                    <div class="analysis-item">
                        <span class="analysis-label">pH Value</span>
                        <div class="analysis-value"><?php echo htmlspecialchars($report['ph_value'] ?? 'Not analyzed'); ?></div>
                    </div>
                    <div class="analysis-item">
                        <span class="analysis-label">Organic Matter (%)</span>
                        <div class="analysis-value"><?php echo htmlspecialchars($report['organic_matter'] ?? 'Not analyzed'); ?></div>
                    </div>
                    <div class="analysis-item">
                        <span class="analysis-label">Nitrogen Level (ppm)</span>
                        <div class="analysis-value"><?php echo htmlspecialchars($report['nitrogen_level'] ?? 'Not analyzed'); ?></div>
                    </div>
                    <div class="analysis-item">
                        <span class="analysis-label">Phosphorus Level (ppm)</span>
                        <div class="analysis-value"><?php echo htmlspecialchars($report['phosphorus_level'] ?? 'Not analyzed'); ?></div>
                    </div>
                    <div class="analysis-item">
                        <span class="analysis-label">Potassium Level (ppm)</span>
                        <div class="analysis-value"><?php echo htmlspecialchars($report['potassium_level'] ?? 'Not analyzed'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>📋 Land Assessment</h2>
                <div class="content-box">
                    <h3>Land Description:</h3>
                    <p><?php echo nl2br(htmlspecialchars($report['land_description'])); ?></p>
                </div>
            </div>
            
            <div class="section">
                <h2>🌾 Crop Recommendations</h2>
                <div class="recommendation-box">
                    <p style="font-size: 14px; margin: 0;"><?php echo nl2br(htmlspecialchars($report['crop_recomendation'] ?? 'No recommendations available')); ?></p>
                </div>
            </div>
            
            <?php if (!empty($report['environmental_notes'])): ?>
            <div class="section">
                <h2>🌍 Environmental Notes</h2>
                <div class="notes-box">
                    <p style="margin: 0; font-size: 13px;"><?php echo nl2br(htmlspecialchars($report['environmental_notes'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="footer">
                <p><strong>Farm Master Land Assessment System</strong></p>
                <p>Report generated on <?php echo $currentDate; ?></p>
                <p>For questions about this assessment, please contact our agricultural experts.</p>
            </div>
            
            <script>
                // Keyboard shortcut for printing
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 'p') {
                        e.preventDefault();
                        window.print();
                    }
                });
                
                // Auto-focus for better UX
                window.onload = function() {
                    document.querySelector('.print-btn').focus();
                };
            </script>
        </body>
        </html>
        <?php
    }
}

?>
