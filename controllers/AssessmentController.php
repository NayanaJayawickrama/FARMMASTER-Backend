<?php

require_once __DIR__ . '/../models/AssessmentModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class AssessmentController {
    private $assessmentModel;

    public function __construct() {
        $this->assessmentModel = new AssessmentModel();
    }

    public function getUserAssessments($userId = null) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Use current user ID if not provided or if not manager
            if (!$userId || (!in_array($currentRole, ['Financial_Manager', 'Operational_Manager', 'Field_Supervisor']))) {
                $userId = $currentUserId;
            }

            $filters = [];
            
            if (isset($_GET['payment_status'])) {
                $filters['payment_status'] = $_GET['payment_status'];
            }
            if (isset($_GET['has_report'])) {
                $filters['has_report'] = $_GET['has_report'] === 'true';
            }
            if (isset($_GET['report_status'])) {
                $filters['report_status'] = $_GET['report_status'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }

            $assessments = $this->assessmentModel->getUserAssessments($userId, $filters);
            
            Response::success("User assessments retrieved successfully", $assessments);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAllAssessments() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager', 'Field_Supervisor']);

            $filters = [];
            
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['payment_status'])) {
                $filters['payment_status'] = $_GET['payment_status'];
            }
            if (isset($_GET['has_report'])) {
                $filters['has_report'] = $_GET['has_report'] === 'true';
            }
            if (isset($_GET['report_status'])) {
                $filters['report_status'] = $_GET['report_status'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }

            $assessments = $this->assessmentModel->getAllAssessments($filters);
            
            Response::success("All assessments retrieved successfully", $assessments);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAssessmentStats() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager', 'Field_Supervisor']);

            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            $stats = $this->assessmentModel->getAssessmentStats($dateFrom, $dateTo);

            Response::success("Assessment statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchAssessments() {
        try {
            SessionManager::requireAuth();

            $searchTerm = $_GET['search'] ?? '';

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Regular users can only search their own assessments
            $userId = in_array($currentRole, ['Financial_Manager', 'Operational_Manager', 'Field_Supervisor']) ? null : $currentUserId;

            $assessments = $this->assessmentModel->searchAssessments($searchTerm, $userId);

            Response::success("Search completed", $assessments);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getPendingAssessments() {
        try {
            SessionManager::requireRole(['Field_Supervisor']);

            // Get paid lands without reports (pending assessment)
            $filters = [
                'payment_status' => 'paid',
                'has_report' => false
            ];

            $pendingAssessments = $this->assessmentModel->getAllAssessments($filters);

            Response::success("Pending assessments retrieved successfully", $pendingAssessments);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getCompletedAssessments() {
        try {
            SessionManager::requireRole(['Field_Supervisor', 'Operational_Manager']);

            // Get assessments with reports
            $filters = [
                'has_report' => true
            ];

            if (isset($_GET['report_status'])) {
                $filters['report_status'] = $_GET['report_status'];
            }

            $completedAssessments = $this->assessmentModel->getAllAssessments($filters);

            Response::success("Completed assessments retrieved successfully", $completedAssessments);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAssessmentsByPaymentStatus($status) {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $validStatuses = ['pending', 'paid', 'failed'];
            
            if (!in_array($status, $validStatuses)) {
                Response::error("Invalid payment status");
            }

            $filters = ['payment_status' => $status];

            $assessments = $this->assessmentModel->getAllAssessments($filters);

            Response::success("Assessments retrieved successfully", [
                'payment_status' => $status,
                'count' => count($assessments),
                'assessments' => $assessments
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getDashboardData() {
        try {
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($currentRole === 'Landowner') {
                // Landowner dashboard
                SessionManager::requireAuth();
                $userId = SessionManager::getCurrentUserId();
                
                $assessments = $this->assessmentModel->getUserAssessments($userId);
                $stats = [
                    'total_requests' => count($assessments),
                    'pending_payment' => count(array_filter($assessments, fn($a) => $a['payment_status'] === 'pending')),
                    'paid_requests' => count(array_filter($assessments, fn($a) => $a['payment_status'] === 'paid')),
                    'completed_reports' => count(array_filter($assessments, fn($a) => $a['has_report'] === true)),
                    'pending_assessment' => count(array_filter($assessments, fn($a) => $a['payment_status'] === 'paid' && !$a['has_report']))
                ];
                
                Response::success("Landowner dashboard data retrieved", [
                    'stats' => $stats,
                    'recent_assessments' => array_slice($assessments, 0, 5)
                ]);
                
            } else {
                // Manager/Supervisor dashboard
                SessionManager::requireRole(['Financial_Manager', 'Operational_Manager', 'Field_Supervisor']);
                
                $allAssessments = $this->assessmentModel->getAllAssessments();
                $stats = [
                    'total_assessments' => count($allAssessments),
                    'pending_payment' => count(array_filter($allAssessments, fn($a) => $a['payment_status'] === 'pending')),
                    'pending_assessment' => count(array_filter($allAssessments, fn($a) => $a['payment_status'] === 'paid' && !$a['has_report'])),
                    'completed_reports' => count(array_filter($allAssessments, fn($a) => $a['has_report'] === true)),
                    'failed_payments' => count(array_filter($allAssessments, fn($a) => $a['payment_status'] === 'failed'))
                ];
                
                Response::success("Manager dashboard data retrieved", [
                    'stats' => $stats,
                    'recent_assessments' => array_slice($allAssessments, 0, 10)
                ]);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getMonthlyAssessmentTrends() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $year = $_GET['year'] ?? date('Y');

            // This would need a custom query to get monthly trends
            // For now, returning basic stats
            $stats = $this->assessmentModel->getAssessmentStats(
                "{$year}-01-01", 
                "{$year}-12-31"
            );

            Response::success("Monthly assessment trends retrieved", [
                'year' => $year,
                'stats' => $stats
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

?>