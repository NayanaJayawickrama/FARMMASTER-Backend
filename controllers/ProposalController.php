<?php

require_once __DIR__ . '/../models/ProposalModel.php';
require_once __DIR__ . '/../models/LandModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class ProposalController {
    private $proposalModel;
    private $landModel;
    private $validStatuses = ['Pending', 'Accepted', 'Rejected', 'Cancelled', 'In_Progress', 'Completed'];

    public function __construct() {
        $this->proposalModel = new ProposalModel();
        $this->landModel = new LandModel();
    }

    public function getUserProposals($userId = null) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Use current user ID if not provided or if not manager
            if (!$userId || (!in_array($currentRole, ['Financial_Manager', 'Operational_Manager']))) {
                $userId = $currentUserId;
            }

            $filters = [];
            
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['crop_type'])) {
                $filters['crop_type'] = $_GET['crop_type'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['min_rental'])) {
                $filters['min_rental'] = $_GET['min_rental'];
            }
            if (isset($_GET['max_rental'])) {
                $filters['max_rental'] = $_GET['max_rental'];
            }

            $proposals = $this->proposalModel->getUserProposals($userId, $filters);
            
            Response::success("User proposals retrieved successfully", $proposals);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAllProposals() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $filters = [];
            
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['crop_type'])) {
                $filters['crop_type'] = $_GET['crop_type'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['min_rental'])) {
                $filters['min_rental'] = $_GET['min_rental'];
            }
            if (isset($_GET['max_rental'])) {
                $filters['max_rental'] = $_GET['max_rental'];
            }

            $proposals = $this->proposalModel->getAllProposals($filters);
            
            Response::success("All proposals retrieved successfully", $proposals);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProposal($proposalId) {
        try {
            SessionManager::requireAuth();

            $proposal = $this->proposalModel->getProposalById($proposalId);
            
            if (!$proposal) {
                Response::notFound("Proposal not found");
            }

            // Check if user can access this proposal
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($proposal['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }
            
            Response::success("Proposal retrieved successfully", $proposal);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getLandProposals($landId) {
        try {
            SessionManager::requireAuth();

            // Verify land ownership or manager access
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if (!in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                $land = $this->landModel->getLandById($landId);
                if (!$land || $land['user_id'] != $currentUserId) {
                    Response::forbidden("Access denied");
                }
            }

            $proposals = $this->proposalModel->getLandProposals($landId);
            
            Response::success("Land proposals retrieved successfully", $proposals);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function createProposal() {
        try {
            SessionManager::requireAuth();

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();

            // Validate input data
            $landId = Validator::required($data['land_id'] ?? '', 'Land ID');
            $cropType = Validator::required($data['crop_type'] ?? '', 'Crop type');
            $estimatedYield = Validator::numeric($data['estimated_yield'] ?? 0, 'Estimated yield', 0);
            $leaseDurationYears = Validator::numeric($data['lease_duration_years'] ?? 0, 'Lease duration', 1);
            $rentalValue = Validator::numeric($data['rental_value'] ?? 0, 'Rental value', 0);
            $profitSharingFarmmaster = Validator::numeric($data['profit_sharing_farmmaster'] ?? 0, 'Profit sharing farmmaster', 0, 100);
            $profitSharingLandowner = Validator::numeric($data['profit_sharing_landowner'] ?? 0, 'Profit sharing landowner', 0, 100);

            // Verify land exists and has completed assessment
            $land = $this->landModel->getLandById($landId);
            if (!$land) {
                Response::notFound("Land not found");
            }

            // Only managers can create proposals for any land, users can only create for their own land
            if ($land['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }

            if ($land['payment_status'] !== 'paid') {
                Response::error("Land assessment must be paid before creating proposal");
            }

            // Calculate estimated profit for landowner
            $estimatedProfitLandowner = ($estimatedYield * $profitSharingLandowner / 100) + $rentalValue;

            $proposalData = [
                'user_id' => $land['user_id'], // Proposal belongs to landowner
                'land_id' => $landId,
                'crop_type' => $cropType,
                'estimated_yield' => $estimatedYield,
                'lease_duration_years' => $leaseDurationYears,
                'rental_value' => $rentalValue,
                'profit_sharing_farmmaster' => $profitSharingFarmmaster,
                'profit_sharing_landowner' => $profitSharingLandowner,
                'estimated_profit_landowner' => $estimatedProfitLandowner,
                'status' => 'Pending',
                'proposal_date' => $data['proposal_date'] ?? date('Y-m-d')
            ];

            $result = $this->proposalModel->createProposal($proposalData);

            if ($result['success']) {
                Response::success($result['message'], $result, 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateProposalStatus($proposalId) {
        try {
            SessionManager::requireAuth();

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['action'])) {
                Response::error("Action is required (accept or reject)");
            }

            $action = strtolower($data['action']);
            $notes = isset($data['notes']) ? Validator::sanitizeString($data['notes']) : null;

            if (!in_array($action, ['accept', 'reject'])) {
                Response::error("Invalid action. Must be 'accept' or 'reject'");
            }

            // Get proposal details
            $proposal = $this->proposalModel->getProposalById($proposalId);
            if (!$proposal) {
                Response::notFound("Proposal not found");
            }

            // Check if user can update this proposal
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Only the landowner or managers can update proposal status
            if ($proposal['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }

            $newStatus = ($action === 'accept') ? 'Accepted' : 'Rejected';
            
            $result = $this->proposalModel->updateProposalStatus($proposalId, $newStatus, $notes);

            if ($result['success']) {
                Response::success("Proposal " . strtolower($newStatus) . " successfully", [
                    'proposal_id' => $proposalId,
                    'new_status' => $newStatus,
                    'action' => $action
                ]);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateProposal($proposalId) {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Validate numeric fields if provided
            if (isset($data['estimated_yield'])) {
                $data['estimated_yield'] = Validator::numeric($data['estimated_yield'], 'Estimated yield', 0);
            }
            if (isset($data['lease_duration_years'])) {
                $data['lease_duration_years'] = Validator::numeric($data['lease_duration_years'], 'Lease duration', 1);
            }
            if (isset($data['rental_value'])) {
                $data['rental_value'] = Validator::numeric($data['rental_value'], 'Rental value', 0);
            }
            if (isset($data['profit_sharing_farmmaster'])) {
                $data['profit_sharing_farmmaster'] = Validator::numeric($data['profit_sharing_farmmaster'], 'Profit sharing farmmaster', 0, 100);
            }
            if (isset($data['profit_sharing_landowner'])) {
                $data['profit_sharing_landowner'] = Validator::numeric($data['profit_sharing_landowner'], 'Profit sharing landowner', 0, 100);
            }

            // Recalculate estimated profit if yield or profit sharing changed
            if (isset($data['estimated_yield']) || isset($data['profit_sharing_landowner']) || isset($data['rental_value'])) {
                $proposal = $this->proposalModel->getProposalById($proposalId);
                if ($proposal) {
                    $yield = $data['estimated_yield'] ?? $proposal['estimated_yield'];
                    $landowner_share = $data['profit_sharing_landowner'] ?? $proposal['profit_sharing_landowner'];
                    $rental = $data['rental_value'] ?? $proposal['rental_value'];
                    
                    $data['estimated_profit_landowner'] = ($yield * $landowner_share / 100) + $rental;
                }
            }

            $result = $this->proposalModel->updateProposal($proposalId, $data);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function changeProposalStatus($proposalId) {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['status'])) {
                Response::error("Status is required");
            }

            $status = Validator::inArray($data['status'], $this->validStatuses, 'Status');
            $notes = isset($data['notes']) ? Validator::sanitizeString($data['notes']) : null;

            $result = $this->proposalModel->updateProposalStatus($proposalId, $status, $notes);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchProposals() {
        try {
            SessionManager::requireAuth();

            $searchTerm = $_GET['search'] ?? '';

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Regular users can only search their own proposals
            $userId = in_array($currentRole, ['Financial_Manager', 'Operational_Manager']) ? null : $currentUserId;

            $proposals = $this->proposalModel->searchProposals($searchTerm, $userId);

            Response::success("Search completed", $proposals);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProposalStats() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            $stats = $this->proposalModel->getProposalStats($dateFrom, $dateTo);

            Response::success("Proposal statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getPendingProposals() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $filters = ['status' => 'Pending'];

            $proposals = $this->proposalModel->getAllProposals($filters);

            Response::success("Pending proposals retrieved successfully", $proposals);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAcceptedProposals() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $filters = ['status' => 'Accepted'];

            $proposals = $this->proposalModel->getAllProposals($filters);

            Response::success("Accepted proposals retrieved successfully", $proposals);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProposalsByStatus($status) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();

            $validStatus = Validator::inArray($status, $this->validStatuses, 'Status');
            
            $filters = ['status' => $validStatus];

            if (in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                $proposals = $this->proposalModel->getAllProposals($filters);
            } else {
                $proposals = $this->proposalModel->getUserProposals($currentUserId, $filters);
            }

            Response::success("Proposals retrieved successfully", [
                'status' => $validStatus,
                'count' => count($proposals),
                'proposals' => $proposals
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function bulkUpdateProposalUsers() {
        try {
            SessionManager::requireRole(['Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['from_user_id']) || !isset($data['to_user_id'])) {
                Response::error("From and to user IDs are required");
            }

            $fromUserId = Validator::numeric($data['from_user_id'], 'From User ID');
            $toUserId = Validator::numeric($data['to_user_id'], 'To User ID');

            // This is a system maintenance function
            try {
                $sql = "UPDATE proposals SET user_id = ? WHERE user_id = ?";
                $stmt = Database::getInstance()->prepare($sql);
                $success = $stmt->execute([$toUserId, $fromUserId]);
                
                $affectedRows = $stmt->rowCount();

                if ($success) {
                    Response::success("Proposals updated successfully", [
                        'affected_rows' => $affectedRows,
                        'from_user_id' => $fromUserId,
                        'to_user_id' => $toUserId
                    ]);
                } else {
                    Response::error("Failed to update proposals");
                }

            } catch (PDOException $e) {
                Response::error("Database error: " . $e->getMessage());
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

?>