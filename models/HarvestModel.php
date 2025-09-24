<?php

require_once __DIR__ . '/../config/Database.php';

class HarvestModel extends BaseModel {
    protected $table = 'harvest';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all harvest data for a user
     */
    public function getUserHarvests($userId, $filters = []) {
        $sql = "SELECT 
                    h.harvest_id,
                    h.land_id,
                    h.proposal_id,
                    h.harvest_date,
                    h.product_type,
                    h.harvest_amount,
                    h.income,
                    h.expenses,
                    h.land_rent,
                    h.net_profit,
                    h.landowner_share,
                    h.farmmaster_share,
                    h.notes,
                    h.created_at,
                    COALESCE(l.location, 'Land Deleted') as location,
                    COALESCE(l.size, 0) as size,
                    p.status as proposal_status
                FROM harvest h
                LEFT JOIN land l ON h.land_id = l.land_id
                LEFT JOIN proposals p ON h.proposal_id = p.proposal_id
                WHERE h.user_id = ?";
        
        $params = [$userId];
        
        // Add filters
        if (isset($filters['product_type'])) {
            $sql .= " AND h.product_type = ?";
            $params[] = $filters['product_type'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND h.harvest_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND h.harvest_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['land_id'])) {
            $sql .= " AND h.land_id = ?";
            $params[] = $filters['land_id'];
        }
        
        if (isset($filters['proposal_id'])) {
            $sql .= " AND h.proposal_id = ?";
            $params[] = $filters['proposal_id'];
        }
        
        if (isset($filters['min_amount'])) {
            $sql .= " AND h.harvest_amount >= ?";
            $params[] = $filters['min_amount'];
        }
        
        if (isset($filters['max_amount'])) {
            $sql .= " AND h.harvest_amount <= ?";
            $params[] = $filters['max_amount'];
        }

        $sql .= " ORDER BY h.harvest_date DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "harvest_id" => (int)$row["harvest_id"],
                    "land_id" => (int)$row["land_id"],
                    "proposal_id" => $row["proposal_id"] ? (int)$row["proposal_id"] : null,
                    "location" => $row["location"],
                    "land_size" => $row["size"],
                    "harvest_date" => $row["harvest_date"],
                    "product_type" => $row["product_type"],
                    "harvest_amount" => (float)$row["harvest_amount"],
                    "income" => (float)$row["income"],
                    "expenses" => (float)$row["expenses"],
                    "land_rent" => (float)$row["land_rent"],
                    "net_profit" => (float)$row["net_profit"],
                    "landowner_share" => (float)$row["landowner_share"],
                    "farmmaster_share" => (float)$row["farmmaster_share"],
                    "notes" => $row["notes"],
                    "proposal_status" => $row["proposal_status"],
                    "created_at" => $row["created_at"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to fetch user harvests: " . $e->getMessage());
        }
    }

    /**
     * Get all harvest data (for managers)
     */
    public function getAllHarvests($filters = []) {
        $sql = "SELECT 
                    h.harvest_id,
                    h.user_id,
                    h.land_id,
                    h.proposal_id,
                    h.harvest_date,
                    h.product_type,
                    h.harvest_amount,
                    h.income,
                    h.expenses,
                    h.land_rent,
                    h.net_profit,
                    h.landowner_share,
                    h.farmmaster_share,
                    h.notes,
                    h.created_at,
                    COALESCE(l.location, 'Land Deleted') as location,
                    COALESCE(l.size, 0) as size,
                    p.status as proposal_status,
                    u.firstname,
                    u.lastname,
                    u.email
                FROM harvest h
                LEFT JOIN land l ON h.land_id = l.land_id
                LEFT JOIN proposals p ON h.proposal_id = p.proposal_id
                LEFT JOIN users u ON h.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];
        
        // Add filters
        if (isset($filters['user_id'])) {
            $sql .= " AND h.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['product_type'])) {
            $sql .= " AND h.product_type = ?";
            $params[] = $filters['product_type'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND h.harvest_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND h.harvest_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['land_id'])) {
            $sql .= " AND h.land_id = ?";
            $params[] = $filters['land_id'];
        }
        
        if (isset($filters['proposal_id'])) {
            $sql .= " AND h.proposal_id = ?";
            $params[] = $filters['proposal_id'];
        }
        
        if (isset($filters['min_amount'])) {
            $sql .= " AND h.harvest_amount >= ?";
            $params[] = $filters['min_amount'];
        }
        
        if (isset($filters['max_amount'])) {
            $sql .= " AND h.harvest_amount <= ?";
            $params[] = $filters['max_amount'];
        }

        $sql .= " ORDER BY h.harvest_date DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "harvest_id" => (int)$row["harvest_id"],
                    "user_id" => (int)$row["user_id"],
                    "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                    "user_email" => $row["email"],
                    "land_id" => (int)$row["land_id"],
                    "proposal_id" => $row["proposal_id"] ? (int)$row["proposal_id"] : null,
                    "location" => $row["location"],
                    "land_size" => $row["size"],
                    "harvest_date" => $row["harvest_date"],
                    "product_type" => $row["product_type"],
                    "harvest_amount" => (float)$row["harvest_amount"],
                    "income" => (float)$row["income"],
                    "expenses" => (float)$row["expenses"],
                    "land_rent" => (float)$row["land_rent"],
                    "net_profit" => (float)$row["net_profit"],
                    "landowner_share" => (float)$row["landowner_share"],
                    "farmmaster_share" => (float)$row["farmmaster_share"],
                    "notes" => $row["notes"],
                    "proposal_status" => $row["proposal_status"],
                    "created_at" => $row["created_at"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to fetch harvests: " . $e->getMessage());
        }
    }

    /**
     * Get harvest by ID
     */
    public function getHarvestById($harvestId) {
        try {
            $sql = "SELECT 
                        h.*,
                        COALESCE(l.location, 'Land Deleted') as location,
                        COALESCE(l.size, 0) as size,
                        p.status as proposal_status,
                        u.firstname,
                        u.lastname,
                        u.email
                    FROM harvest h
                    LEFT JOIN land l ON h.land_id = l.land_id
                    LEFT JOIN proposals p ON h.proposal_id = p.proposal_id
                    LEFT JOIN users u ON h.user_id = u.user_id
                    WHERE h.harvest_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$harvestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return [
                "harvest_id" => (int)$row["harvest_id"],
                "user_id" => (int)$row["user_id"],
                "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                "user_email" => $row["email"],
                "land_id" => (int)$row["land_id"],
                "proposal_id" => $row["proposal_id"] ? (int)$row["proposal_id"] : null,
                "location" => $row["location"],
                "land_size" => $row["size"],
                "harvest_date" => $row["harvest_date"],
                "product_type" => $row["product_type"],
                "harvest_amount" => (float)$row["harvest_amount"],
                "income" => (float)$row["income"],
                "expenses" => (float)$row["expenses"],
                "land_rent" => (float)$row["land_rent"],
                "net_profit" => (float)$row["net_profit"],
                "landowner_share" => (float)$row["landowner_share"],
                "farmmaster_share" => (float)$row["farmmaster_share"],
                "notes" => $row["notes"],
                "proposal_status" => $row["proposal_status"],
                "created_at" => $row["created_at"]
            ];

        } catch (PDOException $e) {
            throw new Exception("Failed to get harvest: " . $e->getMessage());
        }
    }

    /**
     * Create a new harvest record
     */
    public function createHarvest($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO harvest (
                        user_id, land_id, proposal_id, harvest_date, product_type,
                        harvest_amount, income, expenses, land_rent, net_profit,
                        landowner_share, farmmaster_share, notes, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $data['user_id'],
                $data['land_id'],
                $data['proposal_id'] ?? null,
                $data['harvest_date'],
                $data['product_type'],
                $data['harvest_amount'],
                $data['income'],
                $data['expenses'],
                $data['land_rent'],
                $data['net_profit'],
                $data['landowner_share'],
                $data['farmmaster_share'],
                $data['notes'] ?? null
            ]);

            if ($success) {
                $harvestId = $this->db->lastInsertId();
                $this->db->commit();
                
                return [
                    'success' => true,
                    'message' => 'Harvest record created successfully',
                    'harvest_id' => $harvestId
                ];
            } else {
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Failed to create harvest record'
                ];
            }

        } catch (PDOException $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update harvest record
     */
    public function updateHarvest($harvestId, $data) {
        try {
            $fields = [];
            $params = [];

            $updateableFields = [
                'harvest_date', 'product_type', 'harvest_amount', 'income',
                'expenses', 'land_rent', 'net_profit', 'landowner_share',
                'farmmaster_share', 'notes'
            ];

            foreach ($updateableFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return [
                    'success' => false,
                    'message' => 'No valid fields to update'
                ];
            }

            $params[] = $harvestId;

            $sql = "UPDATE harvest SET " . implode(', ', $fields) . " WHERE harvest_id = ?";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($params);

            if ($success && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Harvest record updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Harvest not found or no changes made'
                ];
            }

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete harvest record
     */
    public function deleteHarvest($harvestId) {
        try {
            $sql = "DELETE FROM harvest WHERE harvest_id = ?";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([$harvestId]);

            if ($success && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Harvest record deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Harvest not found'
                ];
            }

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get harvest statistics
     */
    public function getHarvestStats($dateFrom = null, $dateTo = null, $userId = null) {
        $whereClause = "WHERE 1=1";
        $params = [];

        if ($dateFrom) {
            $whereClause .= " AND harvest_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $whereClause .= " AND harvest_date <= ?";
            $params[] = $dateTo;
        }
        if ($userId) {
            $whereClause .= " AND user_id = ?";
            $params[] = $userId;
        }

        try {
            // Total harvests
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM harvest {$whereClause}");
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total harvest amount
            $stmt = $this->db->prepare("SELECT SUM(harvest_amount) as total_amount FROM harvest {$whereClause}");
            $stmt->execute($params);
            $totalAmount = $stmt->fetch(PDO::FETCH_ASSOC)['total_amount'] ?? 0;

            // Total income
            $stmt = $this->db->prepare("SELECT SUM(income) as total_income FROM harvest {$whereClause}");
            $stmt->execute($params);
            $totalIncome = $stmt->fetch(PDO::FETCH_ASSOC)['total_income'] ?? 0;

            // Total expenses
            $stmt = $this->db->prepare("SELECT SUM(expenses) as total_expenses FROM harvest {$whereClause}");
            $stmt->execute($params);
            $totalExpenses = $stmt->fetch(PDO::FETCH_ASSOC)['total_expenses'] ?? 0;

            // Net profit
            $stmt = $this->db->prepare("SELECT SUM(net_profit) as total_net_profit FROM harvest {$whereClause}");
            $stmt->execute($params);
            $totalNetProfit = $stmt->fetch(PDO::FETCH_ASSOC)['total_net_profit'] ?? 0;

            // Landowner share
            $stmt = $this->db->prepare("SELECT SUM(landowner_share) as total_landowner_share FROM harvest {$whereClause}");
            $stmt->execute($params);
            $totalLandownerShare = $stmt->fetch(PDO::FETCH_ASSOC)['total_landowner_share'] ?? 0;

            // Farmmaster share
            $stmt = $this->db->prepare("SELECT SUM(farmmaster_share) as total_farmmaster_share FROM harvest {$whereClause}");
            $stmt->execute($params);
            $totalFarmmasterShare = $stmt->fetch(PDO::FETCH_ASSOC)['total_farmmaster_share'] ?? 0;

            // Product type breakdown
            $stmt = $this->db->prepare("
                SELECT product_type, COUNT(*) as count, SUM(harvest_amount) as total_amount 
                FROM harvest {$whereClause} 
                GROUP BY product_type 
                ORDER BY count DESC
            ");
            $stmt->execute($params);
            $productStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total_harvests' => (int)$total,
                'total_harvest_amount' => (float)$totalAmount,
                'total_income' => (float)$totalIncome,
                'total_expenses' => (float)$totalExpenses,
                'total_net_profit' => (float)$totalNetProfit,
                'total_landowner_share' => (float)$totalLandownerShare,
                'total_farmmaster_share' => (float)$totalFarmmasterShare,
                'product_breakdown' => array_map(function($item) {
                    return [
                        'product_type' => $item['product_type'],
                        'harvest_count' => (int)$item['count'],
                        'total_amount' => (float)$item['total_amount']
                    ];
                }, $productStats)
            ];

        } catch (PDOException $e) {
            throw new Exception("Failed to get harvest statistics: " . $e->getMessage());
        }
    }

    /**
     * Search harvests
     */
    public function searchHarvests($searchTerm, $userId = null) {
        $sql = "SELECT 
                    h.harvest_id,
                    h.user_id,
                    h.land_id,
                    h.harvest_date,
                    h.product_type,
                    h.harvest_amount,
                    h.income,
                    h.net_profit,
                    COALESCE(l.location, 'Land Deleted') as location,
                    COALESCE(l.size, 0) as size,
                    u.firstname,
                    u.lastname,
                    u.email
                FROM harvest h
                LEFT JOIN land l ON h.land_id = l.land_id
                LEFT JOIN users u ON h.user_id = u.user_id
                WHERE (
                    h.product_type LIKE ? OR 
                    COALESCE(l.location, '') LIKE ? OR
                    h.notes LIKE ? OR
                    u.firstname LIKE ? OR
                    u.lastname LIKE ? OR
                    u.email LIKE ?
                )";

        $params = [
            "%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%",
            "%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"
        ];

        if ($userId) {
            $sql .= " AND h.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY h.harvest_date DESC LIMIT 50";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "harvest_id" => (int)$row["harvest_id"],
                    "user_id" => (int)$row["user_id"],
                    "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                    "user_email" => $row["email"],
                    "land_id" => (int)$row["land_id"],
                    "location" => $row["location"],
                    "land_size" => $row["size"],
                    "harvest_date" => $row["harvest_date"],
                    "product_type" => $row["product_type"],
                    "harvest_amount" => (float)$row["harvest_amount"],
                    "income" => (float)$row["income"],
                    "net_profit" => (float)$row["net_profit"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to search harvests: " . $e->getMessage());
        }
    }

    /**
     * Get land harvests (harvests for a specific land)
     */
    public function getLandHarvests($landId) {
        try {
            $sql = "SELECT 
                        h.*,
                        p.status as proposal_status,
                        u.firstname,
                        u.lastname,
                        u.email
                    FROM harvest h
                    LEFT JOIN proposals p ON h.proposal_id = p.proposal_id
                    LEFT JOIN users u ON h.user_id = u.user_id
                    WHERE h.land_id = ?
                    ORDER BY h.harvest_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "harvest_id" => (int)$row["harvest_id"],
                    "user_id" => (int)$row["user_id"],
                    "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                    "user_email" => $row["email"],
                    "proposal_id" => $row["proposal_id"] ? (int)$row["proposal_id"] : null,
                    "harvest_date" => $row["harvest_date"],
                    "product_type" => $row["product_type"],
                    "harvest_amount" => (float)$row["harvest_amount"],
                    "income" => (float)$row["income"],
                    "expenses" => (float)$row["expenses"],
                    "land_rent" => (float)$row["land_rent"],
                    "net_profit" => (float)$row["net_profit"],
                    "landowner_share" => (float)$row["landowner_share"],
                    "farmmaster_share" => (float)$row["farmmaster_share"],
                    "notes" => $row["notes"],
                    "proposal_status" => $row["proposal_status"],
                    "created_at" => $row["created_at"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to get land harvests: " . $e->getMessage());
        }
    }

    /**
     * Get monthly harvest data
     */
    public function getMonthlyHarvestData($year = null, $userId = null) {
        $year = $year ?? date('Y');
        
        $whereClause = "WHERE YEAR(harvest_date) = ?";
        $params = [$year];
        
        if ($userId) {
            $whereClause .= " AND user_id = ?";
            $params[] = $userId;
        }

        try {
            $sql = "SELECT 
                        MONTH(harvest_date) as month,
                        COUNT(*) as harvest_count,
                        SUM(harvest_amount) as total_amount,
                        SUM(income) as total_income,
                        SUM(net_profit) as total_profit
                    FROM harvest {$whereClause}
                    GROUP BY MONTH(harvest_date)
                    ORDER BY month";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize all months with zero values
            $monthlyData = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthlyData[] = [
                    'month' => $i,
                    'month_name' => date('F', mktime(0, 0, 0, $i, 1)),
                    'harvest_count' => 0,
                    'total_amount' => 0,
                    'total_income' => 0,
                    'total_profit' => 0
                ];
            }

            // Update with actual data
            foreach ($results as $row) {
                $monthIndex = (int)$row['month'] - 1;
                $monthlyData[$monthIndex] = [
                    'month' => (int)$row['month'],
                    'month_name' => date('F', mktime(0, 0, 0, $row['month'], 1)),
                    'harvest_count' => (int)$row['harvest_count'],
                    'total_amount' => (float)$row['total_amount'],
                    'total_income' => (float)$row['total_income'],
                    'total_profit' => (float)$row['total_profit']
                ];
            }

            return $monthlyData;

        } catch (PDOException $e) {
            throw new Exception("Failed to get monthly harvest data: " . $e->getMessage());
        }
    }

    /**
     * Update harvest user IDs (bulk operation)
     */
    public function updateHarvestUsers($fromUserId, $toUserId) {
        try {
            $sql = "UPDATE harvest SET user_id = ? WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([$toUserId, $fromUserId]);
            
            $affectedRows = $stmt->rowCount();

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Harvest user IDs updated successfully',
                    'affected_rows' => $affectedRows
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update harvest user IDs'
                ];
            }

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get harvest reports by land ID
     */
    public function getHarvestsByLand($landId) {
        $sql = "SELECT 
                    h.harvest_id,
                    h.land_id,
                    h.user_id,
                    h.proposal_id,
                    h.harvest_date,
                    h.product_type,
                    h.harvest_amount,
                    h.income,
                    h.expenses,
                    h.land_rent,
                    h.net_profit,
                    h.landowner_share,
                    h.farmmaster_share,
                    h.notes,
                    h.created_at,
                    h.updated_at,
                    p.status as proposal_status
                FROM harvest h
                LEFT JOIN proposals p ON h.proposal_id = p.proposal_id
                WHERE h.land_id = :land_id
                ORDER BY h.harvest_date DESC";
        
        return $this->executeQuery($sql, [':land_id' => $landId]);
    }

}

?>