<?php

require_once 'models/LandReportModel.php';
require_once 'utils/Response.php';
require_once 'utils/SessionManager.php';
require_once 'utils/Validator.php';

class LandReportController {
    private $landReportModel;

    public function __construct() {
        $this->landReportModel = new LandReportModel();
    }

    /**
     * Get all land reports (requires authentication)
     */
    public function getAllReports() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

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
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }

            $reports = $this->landReportModel->getAllReports($filters);
            
            Response::success("Land reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get all land reports (public endpoint for testing)
     */
    public function getAllReportsPublic() {
        try {
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

            $reports = $this->landReportModel->getAllReports($filters);
            
            Response::success("Land reports retrieved successfully (public)", $reports);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get a single land report by ID
     */
    public function getReport($reportId) {
        try {
            SessionManager::requireAuth();

            $report = $this->landReportModel->getReportById($reportId);
            
            if (!$report) {
                Response::notFound("Land report not found");
            }

            // Check if user can access this report
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($report['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }
            
            Response::success("Land report retrieved successfully", $report);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get a single land report by ID (public endpoint)
     */
    public function getReportPublic($reportId) {
        try {
            $report = $this->landReportModel->getReportById($reportId);
            
            if (!$report) {
                Response::notFound("Land report not found");
            }
            
            Response::success("Land report retrieved successfully (public)", $report);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Create a new land report
     */
    public function createReport() {
        try {
            // TEMP: Comment out auth
            // SessionManager::requireAuth();

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Validate required fields
            $required = ['land_id', 'report_date', 'land_description', 'crop_recomendation'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    Response::error("Field '$field' is required");
                }
            }

            // Set user_id from session
            $data['user_id'] = SessionManager::getCurrentUserId();

            // Validate and sanitize data
            $data['land_description'] = Validator::sanitizeString($data['land_description']);
            $data['crop_recomendation'] = Validator::sanitizeString($data['crop_recomendation']);
            
            if (isset($data['environmental_notes'])) {
                $data['environmental_notes'] = Validator::sanitizeString($data['environmental_notes']);
            }

            $reportId = $this->landReportModel->createReport($data);
            
            if ($reportId) {
                Response::success("Land report created successfully", ['report_id' => $reportId]);
            } else {
                Response::error("Failed to create land report");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Update land report status
     */
    public function updateReportStatus($reportId) {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['status'])) {
                Response::error("Status is required");
            }

            $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
            if (!in_array($data['status'], $allowedStatuses)) {
                Response::error("Invalid status. Allowed values: " . implode(', ', $allowedStatuses));
            }

            // Check if report exists
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                Response::notFound("Land report not found");
            }

            // Update the status
            $updated = $this->landReportModel->updateReportStatus($reportId, $data['status']);
            
            if ($updated) {
                Response::success("Land report status updated successfully", [
                    'report_id' => $reportId,
                    'new_status' => $data['status']
                ]);
            } else {
                Response::error("Failed to update land report status");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Update land report status (public endpoint)
     */
    public function updateReportStatusPublic($reportId) {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['status'])) {
                Response::error("Status is required");
            }

            $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
            if (!in_array($data['status'], $allowedStatuses)) {
                Response::error("Invalid status. Allowed values: " . implode(', ', $allowedStatuses));
            }

            // Check if report exists
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                Response::notFound("Land report not found");
            }

            // Update the status
            $updated = $this->landReportModel->updateReportStatus($reportId, $data['status']);
            
            if ($updated) {
                Response::success("Land report status updated successfully", [
                    'report_id' => $reportId,
                    'new_status' => $data['status']
                ]);
            } else {
                Response::error("Failed to update land report status");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Update land report details
     */
    public function updateReport($reportId) {
        try {
            SessionManager::requireAuth();

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Check if report exists
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                Response::notFound("Land report not found");
            }

            // Check if user can update this report
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($report['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }

            // Validate and sanitize data
            if (isset($data['land_description'])) {
                $data['land_description'] = Validator::sanitizeString($data['land_description']);
            }
            if (isset($data['crop_recomendation'])) {
                $data['crop_recomendation'] = Validator::sanitizeString($data['crop_recomendation']);
            }
            if (isset($data['environmental_notes'])) {
                $data['environmental_notes'] = Validator::sanitizeString($data['environmental_notes']);
            }

            $updated = $this->landReportModel->updateReport($reportId, $data);
            
            if ($updated) {
                Response::success("Land report updated successfully");
            } else {
                Response::error("Failed to update land report");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Delete a land report
     */
    public function deleteReport($reportId) {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            // Check if report exists
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                Response::notFound("Land report not found");
            }

            $deleted = $this->landReportModel->deleteReport($reportId);
            
            if ($deleted) {
                Response::success("Land report deleted successfully");
            } else {
                Response::error("Failed to delete land report");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get land reports for current user
     */
    public function getUserReports() {
        try {
            SessionManager::requireAuth();

            $userId = SessionManager::getCurrentUserId();
            $filters = [];
            
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            $reports = $this->landReportModel->getReportsByUser($userId, $filters);
            
            Response::success("User land reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get land report statistics
     */
    public function getReportStats() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $stats = $this->landReportModel->getReportStats();
            
            Response::success("Land report statistics retrieved successfully", $stats);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get land reports for assignment management
     * Returns reports with assignment status and supervisor information
     */
    public function getAssignmentReports() {
        try {
            error_log("getAssignmentReports called - using database data only");
            
            $reports = $this->landReportModel->getAssignmentReports();
            
            Response::success("Assignment reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            error_log("Error in getAssignmentReports: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Public version - Get land reports for assignment management
     */
    public function getAssignmentReportsPublic() {
        try {
            error_log("getAssignmentReportsPublic called - using database data only");
            
            $reports = $this->landReportModel->getAssignmentReports();
            
            Response::success("Assignment reports retrieved successfully (public)", $reports);
            
        } catch (Exception $e) {
            error_log("Error in getAssignmentReportsPublic: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Get reports assigned to the current supervisor
     */
    public function getAssignedReports() {
        try {
            // TODO: Uncomment for production authentication
            // SessionManager::requireAuth();
            // $currentUserId = SessionManager::getCurrentUserId();
            // $userRole = SessionManager::getCurrentUserRole();
            
            // Only field supervisors can access this endpoint
            // if ($userRole !== 'Supervisor') {
            //     Response::error("Access denied. Only field supervisors can view assigned reports.");
            //     return;
            // }

            // Check for supervisor_id parameter in query string
            $supervisorId = $_GET['supervisor_id'] ?? null;
            
            if ($supervisorId && is_numeric($supervisorId)) {
                // Use provided supervisor ID
                $currentUserId = (int)$supervisorId;
                error_log("Using supervisor ID from parameter: " . $currentUserId);
            } else {
                // For testing - use a default supervisor ID (replace with session user ID in production)
                $currentUserId = 40; // njk njkhjhj - Field Supervisor from database
                error_log("Using default supervisor ID: " . $currentUserId);
            }

            $reports = $this->landReportModel->getAssignedReportsForSupervisor($currentUserId);
            
            Response::success("Assigned land reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }



    /**
     * Get land reports for review and approval
     * Returns completed reports waiting for operational manager review
     */
    public function getReviewReports() {
        try {
            error_log("getReviewReports called - using database data only");
            
            $reports = $this->landReportModel->getReviewReports();
            
            Response::success("Review reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            error_log("Error in getReviewReports: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Get paid land requests that need supervisor assignment
     * Returns paid assessments without assigned supervisors
     */
    public function getPendingAssignments() {
        try {
            // TODO: Uncomment for production
            // SessionManager::requireAuth();
            // SessionManager::requireRole(['Operational_Manager']);
            
            $requests = $this->landReportModel->getPendingAssignments();
            
            // Format the data for frontend
            $formattedRequests = [];
            foreach ($requests as $request) {
                $formattedRequests[] = [
                    'id' => '#' . date('Y') . '-LR-' . str_pad($request['land_id'], 3, '0', STR_PAD_LEFT),
                    'report_id' => null, // No report_id yet
                    'location' => $request['location'],
                    'name' => $request['landowner_name'],
                    'date' => date('Y-m-d', strtotime($request['request_date'])),
                    'supervisor' => 'Not Assigned',
                    'status' => 'Unassigned',
                    'current_status' => 'Assessment Pending',
                    'land_id' => $request['land_id'],
                    'user_id' => $request['user_id']
                ];
            }
            
            Response::success("Pending assignments retrieved successfully", $formattedRequests);
            
        } catch (Exception $e) {
            error_log("Error in getPendingAssignments: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Public version - Get land reports for review and approval
     */
    public function getReviewReportsPublic() {
        try {
            error_log("getReviewReportsPublic called - using database data only");
            
            $reports = $this->landReportModel->getReviewReports();
            
            Response::success("Review reports retrieved successfully (public)", $reports);
            
        } catch (Exception $e) {
            error_log("Error in getReviewReportsPublic: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Get available supervisors (Field Supervisors not currently assigned)
     */
    public function getAvailableSupervisors() {
        try {
            error_log("getAvailableSupervisors called - using database data only");
            
            $supervisors = $this->landReportModel->getAvailableSupervisors();
            
            Response::success("Available supervisors retrieved successfully", $supervisors);
            
        } catch (Exception $e) {
            error_log("Error in getAvailableSupervisors: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Public version - Get available supervisors
     */
    public function getAvailableSupervisorsPublic() {
        try {
            error_log("getAvailableSupervisorsPublic called - using database data only");
            
            $supervisors = $this->landReportModel->getAvailableSupervisors();
            
            Response::success("Available supervisors retrieved successfully (public)", $supervisors);
            
        } catch (Exception $e) {
            error_log("Error in getAvailableSupervisorsPublic: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Assign supervisor to land request (creates land_report record)
     */
    public function assignSupervisorToLandRequest($landId) {
        try {
            // TODO: Uncomment for production
            // SessionManager::requireAuth();
            // SessionManager::requireRole(['Operational_Manager']);
            
            error_log("assignSupervisorToLandRequest called for land ID: " . $landId);
            
            $input = json_decode(file_get_contents('php://input'), true);
            error_log("Assignment input: " . print_r($input, true));
            
            if (!$input || !isset($input['supervisor_name']) || !isset($input['supervisor_id'])) {
                Response::error("Supervisor name and ID are required", 400);
                return;
            }

            $result = $this->landReportModel->assignSupervisorToLandRequest($landId, $input['supervisor_name'], $input['supervisor_id']);
            
            if ($result) {
                Response::success("Supervisor assigned successfully to land request", [
                    'land_id' => $landId,
                    'supervisor_name' => $input['supervisor_name'],
                    'supervisor_id' => $input['supervisor_id']
                ]);
            } else {
                Response::error("Failed to assign supervisor to land request");
            }
            
        } catch (Exception $e) {
            error_log("Error in assignSupervisorToLandRequest: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Assign supervisor to land report
     */
    public function assignSupervisor($reportId) {
        try {
            error_log("assignSupervisor called for report ID: " . $reportId);
            
            $input = json_decode(file_get_contents('php://input'), true);
            error_log("Assignment input: " . print_r($input, true));
            
            if (!$input || !isset($input['supervisor_name']) || !isset($input['supervisor_id'])) {
                Response::error("Supervisor name and ID are required", 400);
                return;
            }

            $result = $this->landReportModel->assignSupervisor($reportId, $input['supervisor_name'], $input['supervisor_id']);
            
            if ($result) {
                Response::success("Supervisor assigned successfully", [
                    'report_id' => $reportId,
                    'supervisor_name' => $input['supervisor_name'],
                    'supervisor_id' => $input['supervisor_id']
                ]);
            } else {
                Response::error("Failed to assign supervisor");
            }
            
        } catch (Exception $e) {
            error_log("Error in assignSupervisor: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Submit land data by field supervisor
     */
    public function submitLandData($reportId) {
        try {
            // TODO: Uncomment for production
            // SessionManager::requireAuth();
            // SessionManager::requireRole(['Supervisor']); // Field Supervisor role
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
                return;
            }

            // Check if report exists and is assigned to current supervisor
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                Response::notFound("Land report not found");
                return;
            }

            // TODO: Uncomment for production - Verify the report is assigned to current supervisor
            // $currentUserId = SessionManager::getCurrentUserId();
            $assignmentNotes = $report['environmental_notes'] ?? '';
            // if (!str_contains($assignmentNotes, "ID: $currentUserId")) {
            //     Response::forbidden("This report is not assigned to you");
            //     return;
            // }

            // Validate and prepare update data - Handle both old and new parameter names for compatibility
            $updateData = [];
            $validationErrors = [];
            
            // Handle pH value (should be between 4.0 and 9.5 for agricultural land)
            $phValue = null;
            if (isset($data['ph_value'])) $phValue = $data['ph_value'];
            else if (isset($data['phValue'])) $phValue = $data['phValue'];
            
            if ($phValue !== null && $phValue !== '') {
                $phValue = floatval($phValue);
                if ($phValue < 4.0 || $phValue > 9.5) {
                    $validationErrors[] = "pH value must be between 4.0 and 9.5";
                } else {
                    $updateData['ph_value'] = $phValue;
                }
            }
            
            // Handle organic matter (should be between 0.5% and 15%)
            $organicMatter = null;
            if (isset($data['organic_matter'])) $organicMatter = $data['organic_matter'];
            else if (isset($data['organicMatter'])) $organicMatter = $data['organicMatter'];
            
            if ($organicMatter !== null && $organicMatter !== '') {
                $organicMatter = floatval($organicMatter);
                if ($organicMatter < 0.5 || $organicMatter > 15.0) {
                    $validationErrors[] = "Organic matter must be between 0.5% and 15%";
                } else {
                    $updateData['organic_matter'] = $organicMatter;
                }
            }
            
            // Handle nitrogen (accepts "low", "medium", "high")
            $nitrogen = null;
            if (isset($data['nitrogen_level'])) $nitrogen = $data['nitrogen_level'];
            else if (isset($data['nitrogen'])) $nitrogen = $data['nitrogen'];
            
            if ($nitrogen !== null && $nitrogen !== '') {
                $allowedValues = ['low', 'medium', 'high'];
                if (in_array(strtolower($nitrogen), $allowedValues)) {
                    $updateData['nitrogen_level'] = strtolower($nitrogen);
                } else {
                    $validationErrors[] = "Nitrogen level must be 'low', 'medium', or 'high'";
                }
            }
            
            // Handle phosphorus (accepts "low", "medium", "high")
            $phosphorus = null;
            if (isset($data['phosphorus_level'])) $phosphorus = $data['phosphorus_level'];
            else if (isset($data['phosphorus'])) $phosphorus = $data['phosphorus'];
            
            if ($phosphorus !== null && $phosphorus !== '') {
                $allowedValues = ['low', 'medium', 'high'];
                if (in_array(strtolower($phosphorus), $allowedValues)) {
                    $updateData['phosphorus_level'] = strtolower($phosphorus);
                } else {
                    $validationErrors[] = "Phosphorus level must be 'low', 'medium', or 'high'";
                }
            }
            
            // Handle potassium (accepts "low", "medium", "high")
            $potassium = null;
            if (isset($data['potassium_level'])) $potassium = $data['potassium_level'];
            else if (isset($data['potassium'])) $potassium = $data['potassium'];
            
            if ($potassium !== null && $potassium !== '') {
                $allowedValues = ['low', 'medium', 'high'];
                if (in_array(strtolower($potassium), $allowedValues)) {
                    $updateData['potassium_level'] = strtolower($potassium);
                } else {
                    $validationErrors[] = "Potassium level must be 'low', 'medium', or 'high'";
                }
            }
            
            // Check for validation errors
            if (!empty($validationErrors)) {
                Response::error("Validation failed: " . implode(", ", $validationErrors));
                return;
            }
            
            // Handle environmental notes
            if (isset($data['environmental_notes'])) {
                $updateData['environmental_notes'] = $assignmentNotes . "\n\nField Assessment Notes: " . $data['environmental_notes'];
            } else if (isset($data['notes'])) {
                $updateData['environmental_notes'] = $assignmentNotes . "\n\nField Assessment Notes: " . $data['notes'];
            }
            
            // Mark as completed
            $updateData['completion_status'] = 'Completed';
            
            $result = $this->landReportModel->updateReport($reportId, $updateData);
            
            if ($result) {
                Response::success("Land data submitted successfully", ['report_id' => $reportId]);
            } else {
                Response::error("Failed to submit land data");
            }
            
        } catch (Exception $e) {
            error_log("Error in submitLandData: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Generate crop recommendations based on soil analysis data
     */
    public function generateCropRecommendations($reportId) {
        try {
            // TODO: Add authentication when ready
            // SessionManager::requireAuth();
            // SessionManager::requireRole(['Supervisor']);
            
            error_log("generateCropRecommendations called for report ID: " . $reportId);
            
            // This endpoint doesn't require JSON input data, just the report ID
            // Get the raw input for logging but don't fail if empty
            $rawInput = file_get_contents("php://input");
            error_log("Raw input received: " . ($rawInput ?: 'empty'));
            
            // No JSON input validation needed for this endpoint
            // Get the report with soil data directly
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                error_log("Report not found with ID: " . $reportId);
                Response::notFound("Land report not found");
                return;
            }
            
            error_log("Report found successfully for ID: " . $reportId);

            error_log("Report found: pH=" . ($report['ph_value'] ?? 'null') . ", Organic=" . ($report['organic_matter'] ?? 'null'));

            // Check if we have enough soil data for recommendations
            if (empty($report['ph_value']) || empty($report['organic_matter'])) {
                error_log("Insufficient soil data for report ID: " . $reportId);
                Response::error("Insufficient soil data. Please ensure pH value and organic matter are recorded. Current pH: " . ($report['ph_value'] ?? 'empty') . ", Organic Matter: " . ($report['organic_matter'] ?? 'empty'));
                return;
            }

            $recommendations = $this->generateCropRecommendationLogic($report);
            
            // Save recommendations to the report
            $updateData = [
                'crop_recomendation' => $recommendations['detailed_text']
            ];
            
            $result = $this->landReportModel->updateReport($reportId, $updateData);
            
            if ($result) {
                Response::success("Crop recommendations generated successfully", $recommendations);
            } else {
                Response::error("Failed to save crop recommendations");
            }
            
        } catch (Exception $e) {
            error_log("Error in generateCropRecommendations: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Intelligent crop recommendation logic for Sri Lankan agriculture
     */
    private function generateCropRecommendationLogic($report) {
        $ph = floatval($report['ph_value'] ?? 0);
        $organicMatter = floatval($report['organic_matter'] ?? 0);
        
        // Convert level strings to numeric values for analysis
        $nitrogen = $this->convertLevelToNumeric($report['nitrogen_level'] ?? '');
        $phosphorus = $this->convertLevelToNumeric($report['phosphorus_level'] ?? '');
        $potassium = $this->convertLevelToNumeric($report['potassium_level'] ?? '');
        
        // Store original level strings for display
        $nitrogenLevel = $report['nitrogen_level'] ?? '';
        $phosphorusLevel = $report['phosphorus_level'] ?? '';
        $potassiumLevel = $report['potassium_level'] ?? '';
        
        $recommendations = [];
        $warnings = [];
        $improvements = [];
        
        // Sri Lankan crops with their optimal conditions and NPK requirements
        $crops = [
            'Rice' => [
                'ph_min' => 5.5, 'ph_max' => 7.0, 'ph_optimal' => 6.0,
                'organic_min' => 2.0, 'nitrogen_min' => 20, 'phosphorus_min' => 15, 'potassium_min' => 120,
                'yield_per_acre' => '4-5 tons', 'market_price' => 'Rs. 80-100/kg',
                'season' => 'Yala & Maha', 'water_need' => 'High'
            ],
            'Coconut' => [
                'ph_min' => 5.2, 'ph_max' => 8.0, 'ph_optimal' => 6.5,
                'organic_min' => 1.5, 'nitrogen_min' => 15, 'phosphorus_min' => 10, 'potassium_min' => 200,
                'yield_per_acre' => '2000-3000 nuts/year', 'market_price' => 'Rs. 25-35/nut',
                'season' => 'Year-round', 'water_need' => 'Medium'
            ],
            'Tomato' => [
                'ph_min' => 6.0, 'ph_max' => 7.0, 'ph_optimal' => 6.5,
                'organic_min' => 3.0, 'nitrogen_min' => 25, 'phosphorus_min' => 20, 'potassium_min' => 150,
                'yield_per_acre' => '8-12 tons', 'market_price' => 'Rs. 80-150/kg',
                'season' => 'Cool season', 'water_need' => 'High'
            ],
            'Cabbage' => [
                'ph_min' => 6.0, 'ph_max' => 7.5, 'ph_optimal' => 6.5,
                'organic_min' => 2.5, 'nitrogen_min' => 22, 'phosphorus_min' => 18, 'potassium_min' => 180,
                'yield_per_acre' => '15-20 tons', 'market_price' => 'Rs. 40-80/kg',
                'season' => 'Cool season', 'water_need' => 'Medium'
            ],
            'Lettuce' => [
                'ph_min' => 6.0, 'ph_max' => 7.5, 'ph_optimal' => 6.8,
                'organic_min' => 2.0, 'nitrogen_min' => 30, 'phosphorus_min' => 12, 'potassium_min' => 100,
                'yield_per_acre' => '6-8 tons', 'market_price' => 'Rs. 100-200/kg',
                'season' => 'Cool season', 'water_need' => 'Medium'
            ],
            'Carrot' => [
                'ph_min' => 6.0, 'ph_max' => 7.0, 'ph_optimal' => 6.5,
                'organic_min' => 2.0, 'nitrogen_min' => 18, 'phosphorus_min' => 25, 'potassium_min' => 140,
                'yield_per_acre' => '10-15 tons', 'market_price' => 'Rs. 60-120/kg',
                'season' => 'Cool season', 'water_need' => 'Medium'
            ],
            'Beans' => [
                'ph_min' => 6.0, 'ph_max' => 7.5, 'ph_optimal' => 6.8,
                'organic_min' => 1.5, 'nitrogen_min' => 12, 'phosphorus_min' => 22, 'potassium_min' => 110,
                'yield_per_acre' => '3-5 tons', 'market_price' => 'Rs. 80-150/kg',
                'season' => 'Year-round', 'water_need' => 'Medium'
            ],
            'Banana' => [
                'ph_min' => 5.5, 'ph_max' => 7.0, 'ph_optimal' => 6.2,
                'organic_min' => 3.5, 'nitrogen_min' => 30, 'phosphorus_min' => 15, 'potassium_min' => 250,
                'yield_per_acre' => '30-40 tons', 'market_price' => 'Rs. 50-100/kg',
                'season' => 'Year-round', 'water_need' => 'High'
            ]
        ];
        
        // Analyze each crop
        foreach ($crops as $cropName => $cropData) {
            $score = 0;
            $reasons = [];
            
            // pH suitability (40% weight)
            if ($ph >= $cropData['ph_min'] && $ph <= $cropData['ph_max']) {
                if (abs($ph - $cropData['ph_optimal']) <= 0.5) {
                    $score += 40;
                    $reasons[] = "Optimal pH range";
                } else {
                    $score += 25;
                    $reasons[] = "Acceptable pH range";
                }
            } else if ($ph < $cropData['ph_min']) {
                $reasons[] = "pH too low (needs lime application)";
            } else {
                $reasons[] = "pH too high (needs organic matter)";
            }
            
            // Organic matter (30% weight)
            if ($organicMatter >= $cropData['organic_min']) {
                if ($organicMatter >= $cropData['organic_min'] + 1.0) {
                    $score += 30;
                    $reasons[] = "Excellent organic matter content";
                } else {
                    $score += 20;
                    $reasons[] = "Good organic matter content";
                }
            } else {
                $reasons[] = "Low organic matter (needs compost)";
            }
            
            // Nutrient levels (30% weight) - using actual numeric values
            $nutrientScore = 0;
            $nutrientReasons = [];
            
            // Check nitrogen (if provided)
            if ($nitrogen > 0) {
                $nitrogenNeeded = $cropData['nitrogen_min'] ?? 20;
                if ($nitrogen >= $nitrogenNeeded) {
                    $nutrientScore += 10;
                    $nutrientReasons[] = "Adequate nitrogen";
                } else {
                    $nutrientReasons[] = "Low nitrogen (needs {$nitrogenNeeded}+ ppm)";
                }
            }
            
            // Check phosphorus (if provided)
            if ($phosphorus > 0) {
                $phosphorusNeeded = $cropData['phosphorus_min'] ?? 15;
                if ($phosphorus >= $phosphorusNeeded) {
                    $nutrientScore += 10;
                    $nutrientReasons[] = "Adequate phosphorus";
                } else {
                    $nutrientReasons[] = "Low phosphorus (needs {$phosphorusNeeded}+ ppm)";
                }
            }
            
            // Check potassium (if provided)
            if ($potassium > 0) {
                $potassiumNeeded = $cropData['potassium_min'] ?? 100;
                if ($potassium >= $potassiumNeeded) {
                    $nutrientScore += 10;
                    $nutrientReasons[] = "Adequate potassium";
                } else {
                    $nutrientReasons[] = "Low potassium (needs {$potassiumNeeded}+ ppm)";
                }
            }
            
            $score += $nutrientScore;
            $reasons = array_merge($reasons, $nutrientReasons);
            
            if ($score >= 60) {
                $recommendations[] = [
                    'crop' => $cropName,
                    'suitability' => 'Highly Suitable',
                    'score' => $score,
                    'yield' => $cropData['yield_per_acre'],
                    'market_price' => $cropData['market_price'],
                    'season' => $cropData['season'],
                    'water_requirement' => $cropData['water_need'],
                    'reasons' => $reasons
                ];
            } elseif ($score >= 40) {
                $recommendations[] = [
                    'crop' => $cropName,
                    'suitability' => 'Moderately Suitable',
                    'score' => $score,
                    'yield' => $cropData['yield_per_acre'],
                    'market_price' => $cropData['market_price'],
                    'season' => $cropData['season'],
                    'water_requirement' => $cropData['water_need'],
                    'reasons' => $reasons
                ];
            }
        }
        
        // Sort by score
        usort($recommendations, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        // Generate soil improvement recommendations based on actual values
        if ($ph < 5.5) {
            $improvements[] = "pH too low: Apply agricultural lime (2-3 tons per hectare) to raise pH to 6.0-7.0";
        } elseif ($ph > 7.5) {
            $improvements[] = "pH too high: Add organic matter and sulfur to lower pH to 6.0-7.0";
        }
        
        if ($organicMatter < 2.0) {
            $improvements[] = "Low organic matter: Add compost or well-decomposed manure (5-10 tons per hectare)";
        }
        
        if ($nitrogenLevel === 'low') {
            $improvements[] = "Low nitrogen level: Apply nitrogen-rich fertilizer or green manure to improve crop growth";
        }
        
        if ($phosphorusLevel === 'low') {
            $improvements[] = "Low phosphorus level: Apply rock phosphate or triple superphosphate to enhance root development";
        }
        
        if ($potassiumLevel === 'low') {
            $improvements[] = "Low potassium level: Apply muriate of potash or organic potassium sources to strengthen plant immunity";
        }
        
        // Generate detailed text report
        $detailedText = $this->generateDetailedRecommendationText($recommendations, $improvements, $report);
        
        return [
            'recommendations' => array_slice($recommendations, 0, 5), // Top 5 crops
            'soil_improvements' => $improvements,
            'detailed_text' => $detailedText,
            'soil_summary' => [
                'ph' => $ph,
                'organic_matter' => $organicMatter,
                'nitrogen' => $nitrogenLevel,
                'phosphorus' => $phosphorusLevel,
                'potassium' => $potassiumLevel
            ]
        ];
    }

    /**
     * Generate detailed text recommendation report
     */
    private function generateDetailedRecommendationText($recommendations, $improvements, $report) {
        $text = "🌱 LAND SUITABILITY ANALYSIS & CROP RECOMMENDATIONS\n";
        $text .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        $text .= "📊 SOIL ANALYSIS SUMMARY:\n";
        $text .= "• pH Value: " . ($report['ph_value'] ?? 'Not recorded') . "\n";
        $text .= "• Organic Matter: " . ($report['organic_matter'] ?? 'Not recorded') . "%\n";
        $text .= "• Nitrogen Level: " . ucfirst($report['nitrogen_level'] ?? 'Not recorded') . "\n";
        $text .= "• Phosphorus Level: " . ucfirst($report['phosphorus_level'] ?? 'Not recorded') . "\n";
        $text .= "• Potassium Level: " . ucfirst($report['potassium_level'] ?? 'Not recorded') . "\n\n";
        
        $text .= "🌾 TOP RECOMMENDED CROPS:\n\n";
        foreach (array_slice($recommendations, 0, 3) as $i => $rec) {
            $text .= ($i + 1) . ". " . $rec['crop'] . " (" . $rec['suitability'] . ")\n";
            $text .= "   Expected Yield: " . $rec['yield'] . "\n";
            $text .= "   Market Price: " . $rec['market_price'] . "\n";
            $text .= "   Growing Season: " . $rec['season'] . "\n";
            $text .= "   Water Requirement: " . $rec['water_requirement'] . "\n";
            $text .= "   Reasons: " . implode(', ', $rec['reasons']) . "\n\n";
        }
        
        if (!empty($improvements)) {
            $text .= "🔧 SOIL IMPROVEMENT RECOMMENDATIONS:\n";
            foreach ($improvements as $improvement) {
                $text .= "• " . $improvement . "\n";
            }
            $text .= "\n";
        }
        
        $text .= "💡 FARMING TIPS:\n";
        $text .= "• Test soil every 2-3 years for optimal management\n";
        $text .= "• Use organic fertilizers whenever possible\n";
        $text .= "• Consider crop rotation to maintain soil health\n";
        $text .= "• Implement proper drainage systems if needed\n";
        $text .= "• Consult local agricultural officers for specific guidance\n\n";
        
        $text .= "📞 For technical support, contact your local Agricultural Extension Office.\n";
        
        return $text;
    }

    /**
     * Submit review decision for a land report
     */
    public function submitReview($reportId) {
        try {
            error_log("submitReview called for report ID: " . $reportId);
            
            $data = json_decode(file_get_contents("php://input"), true);
            error_log("Review input: " . print_r($data, true));
            
            if (!$data || !isset($data['decision'])) {
                Response::error("Review decision is required");
                return;
            }

            $allowedDecisions = ['approved', 'rejected'];
            if (!in_array($data['decision'], $allowedDecisions)) {
                Response::error("Invalid decision. Must be 'approved' or 'rejected'");
                return;
            }

            $feedback = isset($data['feedback']) ? $data['feedback'] : '';
            
            $result = $this->landReportModel->submitReview($reportId, $data['decision'], $feedback);
            
            if ($result) {
                error_log("Review submission successful for report {$reportId}");
                Response::success("Review submitted successfully", [
                    'report_id' => $reportId,
                    'decision' => $data['decision'],
                    'feedback' => $feedback
                ]);
            } else {
                error_log("Review submission failed for report {$reportId}");
                Response::error("Failed to submit review. Report may not exist or database error occurred.");
            }
            
        } catch (Exception $e) {
            error_log("Error in submitReview: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Send completed report to land owner
     */
    public function sendToLandOwner($reportId) {
        try {
            error_log("sendToLandOwner called for report ID: " . $reportId);
            
            // Get the report to verify it exists and get land owner info
            $report = $this->landReportModel->getReportById($reportId);
            
            if (!$report) {
                Response::error("Report not found");
                return;
            }
            
            // Update report status to indicate it's been sent to land owner
            $updateData = [
                'status' => 'Sent to Owner',
                'sent_to_owner_date' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->landReportModel->updateReportStatus($reportId, 'Sent to Owner');
            
            error_log("Update result: " . json_encode($result));
            
            if ($result && $result['success']) {
                // Log the action for audit trail
                error_log("Report {$reportId} successfully sent to land owner (User ID: {$report['user_id']})");
                
                // TODO: Add notification system here
                // Example: Create notification record, send email, etc.
                // $this->createNotification($report['user_id'], "Your land report is ready for review", $reportId);
                
                Response::success("Report sent to land owner successfully", [
                    'report_id' => $reportId,
                    'land_owner_id' => $report['user_id'],
                    'status' => 'Sent to Owner',
                    'message' => 'The land owner can now view their completed report in their dashboard'
                ]);
            } else {
                error_log("Failed to send report {$reportId} to land owner");
                Response::error("Failed to send report to land owner. Database error occurred.");
            }
            
        } catch (Exception $e) {
            error_log("Error in sendToLandOwner: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Get land owner's completed reports (including those sent to owner)
     */
    public function getLandOwnerReports() {
        try {
            // Check if user_id is provided in query params
            $userId = $_GET['user_id'] ?? null;
            
            if (!$userId) {
                error_log("getLandOwnerReports: No user_id provided");
                Response::error("User ID is required");
                return;
            }

            error_log("getLandOwnerReports called for user ID: " . $userId);
            
            $reports = $this->landReportModel->getLandOwnerReports($userId);
            
            error_log("getLandOwnerReports: Found " . count($reports) . " reports for user " . $userId);
            
            Response::success("Land owner reports retrieved successfully", $reports);
            
        } catch (Exception $e) {
            error_log("Error in getLandOwnerReports: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            Response::error($e->getMessage());
        }
    }

    /**
     * Generate land suitability conclusion
     */
    public function generateConclusion($reportId) {
        try {
            SessionManager::requireAuth();
            
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Check if user can access this report
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                Response::notFound("Land report not found");
            }
            
            if ($report['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }
            
            $result = $this->landReportModel->generateLandConclusion($reportId);
            
            if ($result['success']) {
                Response::success("Land conclusion generated successfully", $result['data']);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Create proposal request for suitable land
     */
    public function createProposalRequest($reportId) {
        try {
            SessionManager::requireAuth();
            
            $currentUserId = SessionManager::getCurrentUserId();
            
            // Check if user owns this report
            $report = $this->landReportModel->getReportById($reportId);
            if (!$report) {
                Response::notFound("Land report not found");
            }
            
            if ($report['user_id'] != $currentUserId) {
                Response::forbidden("You can only create proposals for your own land reports");
            }
            
            $result = $this->landReportModel->createProposalRequest($reportId, $currentUserId);
            
            if ($result['success']) {
                Response::success($result['message'], ['request_id' => $result['request_id']], 201);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get proposal requests for financial manager
     */
    public function getProposalRequests() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager']);
            
            $status = $_GET['status'] ?? null;
            
            $sql = "SELECT 
                        pr.*,
                        lr.land_description,
                        lr.ph_value,
                        lr.organic_matter,
                        lr.nitrogen_level,
                        lr.phosphorus_level,
                        lr.potassium_level,
                        l.location,
                        l.size,
                        u.first_name,
                        u.last_name,
                        u.email,
                        u.phone
                    FROM proposal_requests pr
                    JOIN land_report lr ON pr.report_id = lr.report_id
                    JOIN land l ON pr.land_id = l.land_id
                    JOIN user u ON pr.user_id = u.user_id";
            
            $params = [];
            if ($status) {
                $sql .= " WHERE pr.status = :status";
                $params[':status'] = $status;
            }
            
            $sql .= " ORDER BY pr.created_at DESC";
            
            $requests = $this->landReportModel->executeQuery($sql, $params);
            
            // Parse JSON crop recommendations
            foreach ($requests as &$request) {
                $request['crop_recommendations'] = json_decode($request['crop_recommendations'] ?? '[]', true);
            }
            
            Response::success("Proposal requests retrieved successfully", $requests);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get proposal requests for financial manager (public version for testing)
     */
    public function getProposalRequestsPublic() {
        try {
            $status = $_GET['status'] ?? null;
            
            $result = $this->landReportModel->getProposalRequestsPublic($status);
            
            if ($result['success']) {
                Response::success("Proposal requests retrieved successfully", $result['data']);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Create interest request for FarmMaster partnership (simple version)
     */
    public function createInterestRequest($reportId) {
        try {
            SessionManager::requireAuth();
            
            $currentUserId = SessionManager::getCurrentUserId();
            
            $result = $this->landReportModel->createInterestRequest($reportId, $currentUserId);
            
            if ($result['success']) {
                Response::success($result['message'], ['request_id' => $result['request_id']], 201);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Decline interest request for FarmMaster partnership
     */
    public function declineInterestRequest($reportId) {
        try {
            SessionManager::requireAuth();
            
            $currentUserId = SessionManager::getCurrentUserId();
            
            $result = $this->landReportModel->declineInterestRequest($reportId, $currentUserId);
            
            if ($result['success']) {
                Response::success($result['message'], ['request_id' => $result['request_id']], 201);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Check if interest request exists for a report
     */
    public function checkInterestRequest($reportId) {
        try {
            // No authentication required for checking - this is for UI display purposes
            $result = $this->landReportModel->hasInterestRequest($reportId);
            
            if ($result['success']) {
                Response::success('Interest request check completed', [
                    'has_request' => $result['has_request'],
                    'request' => $result['request']
                ]);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get interest requests for financial manager
     */
    public function getInterestRequests() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager']);
            
            // Use the same method as our working debug version
            $result = $this->landReportModel->getInterestRequestsDebug();
            
            if ($result['success']) {
                Response::success("Interest requests retrieved successfully", $result['data']);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error("Error in getInterestRequests: " . $e->getMessage());
        }
    }

    /**
     * Get interest requests for debugging (public endpoint)
     */
    public function getInterestRequestsPublic() {
        try {
            $result = $this->landReportModel->getInterestRequestsDebug();
            
            if ($result['success']) {
                Response::success("Interest requests retrieved successfully (DEBUG)", $result['data']);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error("Debug error: " . $e->getMessage());
        }
    }

    /**
     * Update interest request status
     */
    public function updateInterestRequestStatus($requestId) {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager']);
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['status'])) {
                Response::error("Status is required");
                return;
            }
            
            $allowedStatuses = ['pending', 'under_review', 'approved', 'rejected'];
            if (!in_array($data['status'], $allowedStatuses)) {
                Response::error("Invalid status. Allowed values: " . implode(', ', $allowedStatuses));
                return;
            }
            
            $notes = $data['notes'] ?? null;
            
            $result = $this->landReportModel->updateInterestRequestStatus($requestId, $data['status'], $notes);
            
            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Public version - Get reports assigned to specific supervisor (for testing)
     */
    public function getAssignedReportsForSupervisorPublic($supervisorId) {
        try {
            error_log("getAssignedReportsForSupervisorPublic called for supervisor ID: " . $supervisorId);
            
            $reports = $this->landReportModel->getAssignedReportsForSupervisor($supervisorId);
            
            Response::success("Assigned land reports retrieved successfully (public)", $reports);
            
        } catch (Exception $e) {
            error_log("Error in getAssignedReportsForSupervisorPublic: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    /**
     * Convert level strings (low, medium, high) to numeric values for analysis
     */
    private function convertLevelToNumeric($level) {
        $level = strtolower(trim($level));
        switch($level) {
            case 'low':
                return 15; // Low nutrient level
            case 'medium':
                return 35; // Medium nutrient level  
            case 'high':
                return 60; // High nutrient level
            default:
                return 0; // No data or invalid
        }
    }
}

?>