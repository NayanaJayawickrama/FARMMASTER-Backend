<?php

require_once __DIR__ . '/../config/Database.php';

class ProposalModel extends BaseModel {
    protected $table = 'proposals';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all proposals for a user
     */
    public function getUserProposals($userId, $filters = []) {
        $sql = "SELECT 
                    p.proposal_id,
                    p.land_id,
                    p.crop_type,
                    p.estimated_yield,
                    p.lease_duration_years,
                    p.rental_value,
                    p.profit_sharing_farmmaster,
                    p.profit_sharing_landowner,
                    p.estimated_profit_landowner,
                    p.status,
                    p.proposal_date,
                    p.created_at,
                    p.updated_at,
                    l.location,
                    l.size
                FROM proposals p
                JOIN land l ON p.land_id = l.land_id
                WHERE p.user_id = ?";
        
        $params = [$userId];
        
        // Add filters
        if (isset($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['crop_type'])) {
            $sql .= " AND p.crop_type = ?";
            $params[] = $filters['crop_type'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (isset($filters['min_rental'])) {
            $sql .= " AND p.rental_value >= ?";
            $params[] = $filters['min_rental'];
        }
        
        if (isset($filters['max_rental'])) {
            $sql .= " AND p.rental_value <= ?";
            $params[] = $filters['max_rental'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "proposal_id" => (int)$row["proposal_id"],
                    "land_id" => (int)$row["land_id"],
                    "location" => $row["location"],
                    "land_size" => $row["size"],
                    "crop_type" => $row["crop_type"],
                    "estimated_yield" => (float)$row["estimated_yield"],
                    "lease_duration_years" => (int)$row["lease_duration_years"],
                    "rental_value" => (float)$row["rental_value"],
                    "profit_sharing_farmmaster" => (float)$row["profit_sharing_farmmaster"],
                    "profit_sharing_landowner" => (float)$row["profit_sharing_landowner"],
                    "estimated_profit_landowner" => (float)$row["estimated_profit_landowner"],
                    "status" => $row["status"],
                    "proposal_date" => $row["proposal_date"],
                    "created_at" => $row["created_at"],
                    "updated_at" => $row["updated_at"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to fetch user proposals: " . $e->getMessage());
        }
    }

    /**
     * Get all proposals (for managers)
     */
    public function getAllProposals($filters = []) {
        $sql = "SELECT 
                    p.proposal_id,
                    p.user_id,
                    p.land_id,
                    p.crop_type,
                    p.estimated_yield,
                    p.lease_duration_years,
                    p.rental_value,
                    p.profit_sharing_farmmaster,
                    p.profit_sharing_landowner,
                    p.estimated_profit_landowner,
                    p.status,
                    p.proposal_date,
                    p.created_at,
                    p.updated_at,
                    l.location,
                    l.size,
                    CONCAT(u.first_name, ' ', u.last_name) as landowner_name,
                    u.email,
                    CONCAT('#', YEAR(p.created_at), LPAD(p.proposal_id, 3, '0')) as proposal_display_id
                FROM proposals p
                JOIN land l ON p.land_id = l.land_id
                JOIN user u ON p.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];
        
        // Add filters
        if (isset($filters['user_id'])) {
            $sql .= " AND p.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['crop_type'])) {
            $sql .= " AND p.crop_type = ?";
            $params[] = $filters['crop_type'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (isset($filters['min_rental'])) {
            $sql .= " AND p.rental_value >= ?";
            $params[] = $filters['min_rental'];
        }
        
        if (isset($filters['max_rental'])) {
            $sql .= " AND p.rental_value <= ?";
            $params[] = $filters['max_rental'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "proposal_id" => (int)$row["proposal_id"],
                    "user_id" => (int)$row["user_id"],
                    "landowner_name" => $row["landowner_name"],
                    "user_email" => $row["email"],
                    "land_id" => (int)$row["land_id"],
                    "location" => $row["location"],
                    "land_size" => $row["size"],
                    "crop_type" => $row["crop_type"],
                    "estimated_yield" => (float)$row["estimated_yield"],
                    "lease_duration_years" => (int)$row["lease_duration_years"],
                    "rental_value" => (float)$row["rental_value"],
                    "profit_sharing_farmmaster" => (float)$row["profit_sharing_farmmaster"],
                    "profit_sharing_landowner" => (float)$row["profit_sharing_landowner"],
                    "estimated_profit_landowner" => (float)$row["estimated_profit_landowner"],
                    "status" => $row["status"],
                    "proposal_date" => $row["proposal_date"],
                    "created_at" => $row["created_at"],
                    "updated_at" => $row["updated_at"],
                    "proposal_display_id" => $row["proposal_display_id"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to fetch proposals: " . $e->getMessage());
        }
    }

    /**
     * Get proposal by ID
     */
    public function getProposalById($proposalId) {
        try {
            $sql = "SELECT 
                        p.*,
                        l.location,
                        l.size,
                        u.first_name,
                        u.last_name,
                        u.email
                    FROM proposals p
                    JOIN land l ON p.land_id = l.land_id
                    JOIN user u ON p.user_id = u.user_id
                    WHERE p.proposal_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$proposalId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return [
                "proposal_id" => (int)$row["proposal_id"],
                "user_id" => (int)$row["user_id"],
                "user_name" => trim($row["first_name"] . " " . $row["last_name"]),
                "user_email" => $row["email"],
                "land_id" => (int)$row["land_id"],
                "location" => $row["location"],
                "land_size" => $row["size"],
                "crop_type" => $row["crop_type"],
                "estimated_yield" => (float)$row["estimated_yield"],
                "lease_duration_years" => (int)$row["lease_duration_years"],
                "rental_value" => (float)$row["rental_value"],
                "profit_sharing_farmmaster" => (float)$row["profit_sharing_farmmaster"],
                "profit_sharing_landowner" => (float)$row["profit_sharing_landowner"],
                "estimated_profit_landowner" => (float)$row["estimated_profit_landowner"],
                "status" => $row["status"],
                "proposal_date" => $row["proposal_date"],
                "created_at" => $row["created_at"],
                "updated_at" => $row["updated_at"]
            ];

        } catch (PDOException $e) {
            throw new Exception("Failed to get proposal: " . $e->getMessage());
        }
    }

    /**
     * Create a new proposal
     */
    public function createProposal($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO proposals (
                        user_id, land_id, crop_type, estimated_yield,
                        lease_duration_years, rental_value, profit_sharing_farmmaster,
                        profit_sharing_landowner, estimated_profit_landowner,
                        status, proposal_date, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $data['user_id'],
                $data['land_id'],
                $data['crop_type'],
                $data['estimated_yield'],
                $data['lease_duration_years'],
                $data['rental_value'],
                $data['profit_sharing_farmmaster'],
                $data['profit_sharing_landowner'],
                $data['estimated_profit_landowner'],
                $data['status'] ?? 'Pending',
                $data['proposal_date'] ?? date('Y-m-d')
            ]);

            if ($success) {
                $proposalId = $this->db->lastInsertId();
                $this->db->commit();
                
                return [
                    'success' => true,
                    'message' => 'Proposal created successfully',
                    'proposal_id' => $proposalId
                ];
            } else {
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Failed to create proposal'
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
     * Update proposal status
     */
    public function updateProposalStatus($proposalId, $status, $notes = null) {
        try {
            $sql = "UPDATE proposals SET status = ?, updated_at = NOW()";
            $params = [$status, $proposalId];

            if ($notes) {
                $sql .= ", notes = ?";
                array_splice($params, 1, 0, [$notes]);
            }

            $sql .= " WHERE proposal_id = ?";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($params);

            if ($success && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Proposal status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Proposal not found or no changes made'
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
     * Update proposal details
     */
    public function updateProposal($proposalId, $data) {
        try {
            $fields = [];
            $params = [];

            $updateableFields = [
                'crop_type', 'estimated_yield', 'lease_duration_years',
                'rental_value', 'profit_sharing_farmmaster', 'profit_sharing_landowner',
                'estimated_profit_landowner', 'proposal_date'
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

            $fields[] = "updated_at = NOW()";
            $params[] = $proposalId;

            $sql = "UPDATE proposals SET " . implode(', ', $fields) . " WHERE proposal_id = ?";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($params);

            if ($success && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Proposal updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Proposal not found or no changes made'
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
     * Get proposal statistics
     */
    public function getProposalStats($dateFrom = null, $dateTo = null) {
        $whereClause = "WHERE 1=1";
        $params = [];

        if ($dateFrom) {
            $whereClause .= " AND created_at >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $whereClause .= " AND created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        try {
            // Total proposals
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM proposals {$whereClause}");
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Status breakdown
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count 
                FROM proposals {$whereClause} 
                GROUP BY status
            ");
            $stmt->execute($params);
            $statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Crop type breakdown
            $stmt = $this->db->prepare("
                SELECT crop_type, COUNT(*) as count 
                FROM proposals {$whereClause} 
                GROUP BY crop_type 
                ORDER BY count DESC 
                LIMIT 10
            ");
            $stmt->execute($params);
            $cropStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Average rental value
            $stmt = $this->db->prepare("SELECT AVG(rental_value) as avg_rental FROM proposals {$whereClause}");
            $stmt->execute($params);
            $avgRental = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rental'];

            return [
                'total_proposals' => (int)$total,
                'average_rental_value' => round((float)$avgRental, 2),
                'status_breakdown' => array_map(function($item) {
                    return [
                        'status' => $item['status'],
                        'count' => (int)$item['count']
                    ];
                }, $statusStats),
                'crop_breakdown' => array_map(function($item) {
                    return [
                        'crop_type' => $item['crop_type'],
                        'count' => (int)$item['count']
                    ];
                }, $cropStats)
            ];

        } catch (PDOException $e) {
            throw new Exception("Failed to get proposal statistics: " . $e->getMessage());
        }
    }

    /**
     * Search proposals
     */
    public function searchProposals($searchTerm, $userId = null) {
        $sql = "SELECT 
                    p.proposal_id,
                    p.user_id,
                    p.land_id,
                    p.crop_type,
                    p.estimated_yield,
                    p.rental_value,
                    p.status,
                    p.proposal_date,
                    p.created_at,
                    l.location,
                    l.size,
                    u.firstname,
                    u.lastname,
                    u.email
                FROM proposals p
                JOIN land l ON p.land_id = l.land_id
                LEFT JOIN users u ON p.user_id = u.user_id
                WHERE (
                    p.crop_type LIKE ? OR 
                    l.location LIKE ? OR
                    u.firstname LIKE ? OR
                    u.lastname LIKE ? OR
                    u.email LIKE ?
                )";

        $params = [
            "%{$searchTerm}%", "%{$searchTerm}%",
            "%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"
        ];

        if ($userId) {
            $sql .= " AND p.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT 50";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "proposal_id" => (int)$row["proposal_id"],
                    "user_id" => (int)$row["user_id"],
                    "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                    "user_email" => $row["email"],
                    "land_id" => (int)$row["land_id"],
                    "location" => $row["location"],
                    "land_size" => $row["size"],
                    "crop_type" => $row["crop_type"],
                    "estimated_yield" => (float)$row["estimated_yield"],
                    "rental_value" => (float)$row["rental_value"],
                    "status" => $row["status"],
                    "proposal_date" => $row["proposal_date"],
                    "created_at" => $row["created_at"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to search proposals: " . $e->getMessage());
        }
    }

    /**
     * Get land proposals (proposals for a specific land)
     */
    public function getLandProposals($landId) {
        try {
            $sql = "SELECT 
                        p.*,
                        u.firstname,
                        u.lastname,
                        u.email
                    FROM proposals p
                    LEFT JOIN users u ON p.user_id = u.user_id
                    WHERE p.land_id = ?
                    ORDER BY p.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "proposal_id" => (int)$row["proposal_id"],
                    "user_id" => (int)$row["user_id"],
                    "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                    "user_email" => $row["email"],
                    "crop_type" => $row["crop_type"],
                    "estimated_yield" => (float)$row["estimated_yield"],
                    "lease_duration_years" => (int)$row["lease_duration_years"],
                    "rental_value" => (float)$row["rental_value"],
                    "profit_sharing_farmmaster" => (float)$row["profit_sharing_farmmaster"],
                    "profit_sharing_landowner" => (float)$row["profit_sharing_landowner"],
                    "estimated_profit_landowner" => (float)$row["estimated_profit_landowner"],
                    "status" => $row["status"],
                    "proposal_date" => $row["proposal_date"],
                    "created_at" => $row["created_at"],
                    "updated_at" => $row["updated_at"]
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to get land proposals: " . $e->getMessage());
        }
    }
}

?>