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
     * Assign supervisor to land report
     */
    public function assignSupervisor($reportId) {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Operational_Manager']);

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['supervisor_name']) || !isset($input['supervisor_id'])) {
                Response::error("Supervisor name and ID are required", 400);
            }

            $result = $this->landReportModel->assignSupervisor($reportId, $input['supervisor_name'], $input['supervisor_id']);
            
            if ($result) {
                Response::success("Supervisor assigned successfully");
            } else {
                Response::error("Failed to assign supervisor");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Public version - Assign supervisor to land report
     */
    public function assignSupervisorPublic($reportId) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['supervisor_name']) || !isset($input['supervisor_id'])) {
                Response::error("Supervisor name and ID are required", 400);
            }

            $result = $this->landReportModel->assignSupervisor($reportId, $input['supervisor_name'], $input['supervisor_id']);
            
            if ($result) {
                Response::success("Supervisor assigned successfully (public)");
            } else {
                Response::error("Failed to assign supervisor");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Delete land report assignment
     */
    public function deleteAssignment($reportId) {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Operational_Manager']);

            $result = $this->landReportModel->deleteReport($reportId);
            
            if ($result) {
                Response::success("Assignment deleted successfully");
            } else {
                Response::error("Failed to delete assignment");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Public version - Delete land report assignment
     */
    public function deleteAssignmentPublic($reportId) {
        try {
            $result = $this->landReportModel->deleteReport($reportId);
            
            if ($result) {
                Response::success("Assignment deleted successfully (public)");
            } else {
                Response::error("Failed to delete assignment");
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get available supervisors (Field Supervisors not currently assigned)
     */
    public function getAvailableSupervisors() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Operational_Manager']);

            $supervisors = $this->landReportModel->getAvailableSupervisors();
            
            Response::success("Available supervisors retrieved successfully", $supervisors);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Public version - Get available supervisors
     */
    public function getAvailableSupervisorsPublic() {
        try {
            $supervisors = $this->landReportModel->getAvailableSupervisors();
            
            Response::success("Available supervisors retrieved successfully (public)", $supervisors);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

?>