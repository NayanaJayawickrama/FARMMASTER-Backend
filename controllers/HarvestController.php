<?php

require_once __DIR__ . '/../models/HarvestModel.php';
require_once __DIR__ . '/../models/LandModel.php';
require_once __DIR__ . '/../models/ProposalModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class HarvestController {
    private $harvestModel;
    private $landModel;
    private $proposalModel;

    public function __construct() {
        $this->harvestModel = new HarvestModel();
        $this->landModel = new LandModel();
        $this->proposalModel = new ProposalModel();
    }

    public function getUserHarvests($userId = null) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Use current user ID if not provided or if not manager
            if (!$userId || (!in_array($currentRole, ['Financial_Manager', 'Operational_Manager']))) {
                $userId = $currentUserId;
            }

            $filters = [];
            
            if (isset($_GET['product_type'])) {
                $filters['product_type'] = $_GET['product_type'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['land_id'])) {
                $filters['land_id'] = $_GET['land_id'];
            }
            if (isset($_GET['proposal_id'])) {
                $filters['proposal_id'] = $_GET['proposal_id'];
            }
            if (isset($_GET['min_amount'])) {
                $filters['min_amount'] = $_GET['min_amount'];
            }
            if (isset($_GET['max_amount'])) {
                $filters['max_amount'] = $_GET['max_amount'];
            }

            $harvests = $this->harvestModel->getUserHarvests($userId, $filters);
            
            Response::success("User harvests retrieved successfully", $harvests);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAllHarvests() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $filters = [];
            
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['product_type'])) {
                $filters['product_type'] = $_GET['product_type'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['land_id'])) {
                $filters['land_id'] = $_GET['land_id'];
            }
            if (isset($_GET['proposal_id'])) {
                $filters['proposal_id'] = $_GET['proposal_id'];
            }
            if (isset($_GET['min_amount'])) {
                $filters['min_amount'] = $_GET['min_amount'];
            }
            if (isset($_GET['max_amount'])) {
                $filters['max_amount'] = $_GET['max_amount'];
            }

            $harvests = $this->harvestModel->getAllHarvests($filters);
            
            Response::success("All harvests retrieved successfully", $harvests);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getHarvest($harvestId) {
        try {
            SessionManager::requireAuth();

            $harvest = $this->harvestModel->getHarvestById($harvestId);
            
            if (!$harvest) {
                Response::notFound("Harvest not found");
            }

            // Check if user can access this harvest
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($harvest['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }
            
            Response::success("Harvest retrieved successfully", $harvest);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getLandHarvests($landId) {
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

            $harvests = $this->harvestModel->getLandHarvests($landId);
            
            Response::success("Land harvests retrieved successfully", $harvests);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function createHarvest() {
        try {
            SessionManager::requireRole(['Field_Supervisor', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Validate input data
            $userId = Validator::required($data['user_id'] ?? '', 'User ID');
            $landId = Validator::required($data['land_id'] ?? '', 'Land ID');
            $harvestDate = Validator::required($data['harvest_date'] ?? '', 'Harvest date');
            $productType = Validator::required($data['product_type'] ?? '', 'Product type');
            $harvestAmount = Validator::numeric($data['harvest_amount'] ?? 0, 'Harvest amount', 0);
            $income = Validator::numeric($data['income'] ?? 0, 'Income', 0);
            $expenses = Validator::numeric($data['expenses'] ?? 0, 'Expenses', 0);
            $landRent = Validator::numeric($data['land_rent'] ?? 0, 'Land rent', 0);

            // Calculate profit and shares
            $netProfit = $income - $expenses - $landRent;
            
            // If proposal exists, use its profit sharing ratios
            $proposalId = $data['proposal_id'] ?? null;
            $landownerShare = 0;
            $farmmasterShare = 0;
            
            if ($proposalId) {
                $proposal = $this->proposalModel->getProposalById($proposalId);
                if ($proposal && $proposal['status'] === 'Accepted') {
                    $landownerShare = $netProfit * ($proposal['profit_sharing_landowner'] / 100);
                    $farmmasterShare = $netProfit * ($proposal['profit_sharing_farmmaster'] / 100);
                } else {
                    Response::error("Invalid or unaccepted proposal");
                }
            } else {
                // Default 50-50 split if no proposal
                $landownerShare = $netProfit * 0.5;
                $farmmasterShare = $netProfit * 0.5;
            }

            $harvestData = [
                'user_id' => $userId,
                'land_id' => $landId,
                'proposal_id' => $proposalId,
                'harvest_date' => $harvestDate,
                'product_type' => $productType,
                'harvest_amount' => $harvestAmount,
                'income' => $income,
                'expenses' => $expenses,
                'land_rent' => $landRent,
                'net_profit' => $netProfit,
                'landowner_share' => $landownerShare,
                'farmmaster_share' => $farmmasterShare,
                'notes' => isset($data['notes']) ? Validator::sanitizeString($data['notes']) : null
            ];

            $result = $this->harvestModel->createHarvest($harvestData);

            if ($result['success']) {
                Response::success($result['message'], $result, 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateHarvest($harvestId) {
        try {
            SessionManager::requireRole(['Field_Supervisor', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Validate numeric fields if provided
            if (isset($data['harvest_amount'])) {
                $data['harvest_amount'] = Validator::numeric($data['harvest_amount'], 'Harvest amount', 0);
            }
            if (isset($data['income'])) {
                $data['income'] = Validator::numeric($data['income'], 'Income', 0);
            }
            if (isset($data['expenses'])) {
                $data['expenses'] = Validator::numeric($data['expenses'], 'Expenses', 0);
            }
            if (isset($data['land_rent'])) {
                $data['land_rent'] = Validator::numeric($data['land_rent'], 'Land rent', 0);
            }

            // Recalculate profit if financial data changed
            if (isset($data['income']) || isset($data['expenses']) || isset($data['land_rent'])) {
                $harvest = $this->harvestModel->getHarvestById($harvestId);
                if ($harvest) {
                    $income = $data['income'] ?? $harvest['income'];
                    $expenses = $data['expenses'] ?? $harvest['expenses'];
                    $landRent = $data['land_rent'] ?? $harvest['land_rent'];
                    
                    $data['net_profit'] = $income - $expenses - $landRent;
                    
                    // Recalculate shares if proposal exists
                    if ($harvest['proposal_id']) {
                        $proposal = $this->proposalModel->getProposalById($harvest['proposal_id']);
                        if ($proposal) {
                            $data['landowner_share'] = $data['net_profit'] * ($proposal['profit_sharing_landowner'] / 100);
                            $data['farmmaster_share'] = $data['net_profit'] * ($proposal['profit_sharing_farmmaster'] / 100);
                        }
                    } else {
                        // Default 50-50 split
                        $data['landowner_share'] = $data['net_profit'] * 0.5;
                        $data['farmmaster_share'] = $data['net_profit'] * 0.5;
                    }
                }
            }

            if (isset($data['notes'])) {
                $data['notes'] = Validator::sanitizeString($data['notes']);
            }

            $result = $this->harvestModel->updateHarvest($harvestId, $data);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function deleteHarvest($harvestId) {
        try {
            SessionManager::requireRole(['Operational_Manager']);

            $result = $this->harvestModel->deleteHarvest($harvestId);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchHarvests() {
        try {
            SessionManager::requireAuth();

            $searchTerm = $_GET['search'] ?? '';

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Regular users can only search their own harvests
            $userId = in_array($currentRole, ['Financial_Manager', 'Operational_Manager']) ? null : $currentUserId;

            $harvests = $this->harvestModel->searchHarvests($searchTerm, $userId);

            Response::success("Search completed", $harvests);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getHarvestStats() {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            
            // Regular users can only see their own stats
            $userId = in_array($currentRole, ['Financial_Manager', 'Operational_Manager']) ? null : $currentUserId;

            $stats = $this->harvestModel->getHarvestStats($dateFrom, $dateTo, $userId);

            Response::success("Harvest statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getMonthlyHarvestData() {
        try {
            SessionManager::requireAuth();

            $year = $_GET['year'] ?? null;
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Regular users can only see their own data
            $userId = in_array($currentRole, ['Financial_Manager', 'Operational_Manager']) ? null : $currentUserId;

            $monthlyData = $this->harvestModel->getMonthlyHarvestData($year, $userId);

            Response::success("Monthly harvest data retrieved", $monthlyData);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function bulkUpdateHarvestUsers() {
        try {
            SessionManager::requireRole(['Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['from_user_id']) || !isset($data['to_user_id'])) {
                Response::error("From and to user IDs are required");
            }

            $fromUserId = Validator::numeric($data['from_user_id'], 'From User ID');
            $toUserId = Validator::numeric($data['to_user_id'], 'To User ID');

            $result = $this->harvestModel->updateHarvestUsers($fromUserId, $toUserId);

            if ($result['success']) {
                Response::success($result['message'], $result);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getDashboardData() {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($currentRole === 'Landowner') {
                // Landowner dashboard
                $harvests = $this->harvestModel->getUserHarvests($currentUserId);
                $stats = $this->harvestModel->getHarvestStats(null, null, $currentUserId);
                
                Response::success("Landowner harvest dashboard data retrieved", [
                    'stats' => $stats,
                    'recent_harvests' => array_slice($harvests, 0, 5)
                ]);
                
            } else {
                // Manager dashboard
                SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);
                
                $allHarvests = $this->harvestModel->getAllHarvests();
                $stats = $this->harvestModel->getHarvestStats();
                
                Response::success("Manager harvest dashboard data retrieved", [
                    'stats' => $stats,
                    'recent_harvests' => array_slice($allHarvests, 0, 10)
                ]);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

?>