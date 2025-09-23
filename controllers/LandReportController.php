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
            SessionManager::requireAuth();

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

            // For testing - use a default supervisor ID (replace with session user ID in production)
            $currentUserId = 31; // Kanchana Almeda - Field Supervisor from database

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
                Response::success("Review submitted successfully", [
                    'report_id' => $reportId,
                    'decision' => $data['decision'],
                    'feedback' => $feedback
                ]);
            } else {
                Response::error("Failed to submit review");
            }
            
        } catch (Exception $e) {
            error_log("Error in submitReview: " . $e->getMessage());
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
}

?>