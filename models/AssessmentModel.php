<?php

require_once __DIR__ . '/../config/Database.php';

class AssessmentModel extends BaseModel {
    protected $table = 'land';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all assessment requests for a user with report details
     */
    public function getUserAssessments($userId, $filters = []) {
        $sql = "SELECT 
                    l.land_id,
                    l.location,
                    l.size,
                    l.payment_status,
                    l.created_at as request_date,
                    l.payment_date,
                    lr.report_id,
                    lr.report_date,
                    lr.land_description,
                    lr.crop_recomendation,
                    lr.ph_value,
                    lr.organic_matter,
                    lr.nitrogen_level,
                    lr.phosphorus_level,
                    lr.potassium_level,
                    lr.environmental_notes,
                    lr.status as report_status,
                    CASE 
                        WHEN l.payment_status = 'pending' THEN 'Payment Pending'
                        WHEN l.payment_status = 'failed' THEN 'Payment Failed'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NULL THEN 'Assessment Pending'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NOT NULL THEN COALESCE(lr.status, 'Report Submitted')
                        ELSE 'Unknown'
                    END as overall_status
                FROM land l
                LEFT JOIN land_report lr ON l.land_id = lr.land_id
                WHERE l.user_id = ?";
        
        $params = [$userId];
        
        // Add filters
        if (isset($filters['payment_status'])) {
            $sql .= " AND l.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (isset($filters['has_report'])) {
            if ($filters['has_report']) {
                $sql .= " AND lr.report_id IS NOT NULL";
            } else {
                $sql .= " AND lr.report_id IS NULL";
            }
        }
        
        if (isset($filters['report_status'])) {
            $sql .= " AND lr.status = ?";
            $params[] = $filters['report_status'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND l.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND l.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY l.created_at DESC, lr.report_date DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $assessments = [];
            $processed_lands = [];
            
            foreach ($results as $row) {
                $land_id = $row['land_id'];
                
                // Only process each land once (get the latest report if multiple exist)
                if (!in_array($land_id, $processed_lands)) {
                    $assessments[] = [
                        "land_id" => $row["land_id"],
                        "location" => $row["location"],
                        "size" => $row["size"],
                        "payment_status" => $row["payment_status"],
                        "request_date" => $row["request_date"],
                        "payment_date" => $row["payment_date"],
                        "report_id" => $row["report_id"],
                        "report_date" => $row["report_date"],
                        "land_description" => $row["land_description"],
                        "crop_recommendation" => $row["crop_recomendation"],
                        "ph_value" => $row["ph_value"],
                        "organic_matter" => $row["organic_matter"],
                        "nitrogen_level" => $row["nitrogen_level"],
                        "phosphorus_level" => $row["phosphorus_level"],
                        "potassium_level" => $row["potassium_level"],
                        "environmental_notes" => $row["environmental_notes"],
                        "report_status" => $row["report_status"],
                        "overall_status" => $row["overall_status"],
                        "has_report" => !empty($row["report_id"]),
                        "is_paid" => $row["payment_status"] === 'paid'
                    ];
                    $processed_lands[] = $land_id;
                }
            }

            return $assessments;

        } catch (PDOException $e) {
            throw new Exception("Failed to fetch user assessments: " . $e->getMessage());
        }
    }

    /**
     * Get all assessment requests (for managers)
     */
    public function getAllAssessments($filters = []) {
        $sql = "SELECT 
                    l.land_id,
                    l.user_id,
                    l.location,
                    l.size,
                    l.payment_status,
                    l.created_at as request_date,
                    l.payment_date,
                    lr.report_id,
                    lr.report_date,
                    lr.land_description,
                    lr.crop_recomendation,
                    lr.ph_value,
                    lr.organic_matter,
                    lr.nitrogen_level,
                    lr.phosphorus_level,
                    lr.potassium_level,
                    lr.environmental_notes,
                    lr.status as report_status,
                    u.firstname,
                    u.lastname,
                    u.email,
                    CASE 
                        WHEN l.payment_status = 'pending' THEN 'Payment Pending'
                        WHEN l.payment_status = 'failed' THEN 'Payment Failed'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NULL THEN 'Assessment Pending'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NOT NULL THEN COALESCE(lr.status, 'Report Submitted')
                        ELSE 'Unknown'
                    END as overall_status
                FROM land l
                LEFT JOIN land_report lr ON l.land_id = lr.land_id
                LEFT JOIN users u ON l.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];
        
        // Add filters
        if (isset($filters['user_id'])) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['payment_status'])) {
            $sql .= " AND l.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (isset($filters['has_report'])) {
            if ($filters['has_report']) {
                $sql .= " AND lr.report_id IS NOT NULL";
            } else {
                $sql .= " AND lr.report_id IS NULL";
            }
        }
        
        if (isset($filters['report_status'])) {
            $sql .= " AND lr.status = ?";
            $params[] = $filters['report_status'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND l.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND l.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY l.created_at DESC, lr.report_date DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $assessments = [];
            $processed_lands = [];
            
            foreach ($results as $row) {
                $land_id = $row['land_id'];
                
                // Only process each land once (get the latest report if multiple exist)
                if (!in_array($land_id, $processed_lands)) {
                    $assessments[] = [
                        "land_id" => $row["land_id"],
                        "user_id" => $row["user_id"],
                        "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                        "user_email" => $row["email"],
                        "location" => $row["location"],
                        "size" => $row["size"],
                        "payment_status" => $row["payment_status"],
                        "request_date" => $row["request_date"],
                        "payment_date" => $row["payment_date"],
                        "report_id" => $row["report_id"],
                        "report_date" => $row["report_date"],
                        "land_description" => $row["land_description"],
                        "crop_recommendation" => $row["crop_recomendation"],
                        "ph_value" => $row["ph_value"],
                        "organic_matter" => $row["organic_matter"],
                        "nitrogen_level" => $row["nitrogen_level"],
                        "phosphorus_level" => $row["phosphorus_level"],
                        "potassium_level" => $row["potassium_level"],
                        "environmental_notes" => $row["environmental_notes"],
                        "report_status" => $row["report_status"],
                        "overall_status" => $row["overall_status"],
                        "has_report" => !empty($row["report_id"]),
                        "is_paid" => $row["payment_status"] === 'paid'
                    ];
                    $processed_lands[] = $land_id;
                }
            }

            return $assessments;

        } catch (PDOException $e) {
            throw new Exception("Failed to fetch assessments: " . $e->getMessage());
        }
    }

    /**
     * Get assessment statistics
     */
    public function getAssessmentStats($dateFrom = null, $dateTo = null) {
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
            // Total assessments
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM land {$whereClause}");
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Payment status breakdown
            $stmt = $this->db->prepare("
                SELECT payment_status, COUNT(*) as count 
                FROM land {$whereClause} 
                GROUP BY payment_status
            ");
            $stmt->execute($params);
            $paymentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Assessments with reports
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT l.land_id) as count 
                FROM land l 
                JOIN land_report lr ON l.land_id = lr.land_id 
                {$whereClause}
            ");
            $stmt->execute($params);
            $withReports = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Assessments without reports (but paid)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM land l 
                LEFT JOIN land_report lr ON l.land_id = lr.land_id 
                {$whereClause} AND l.payment_status = 'paid' AND lr.report_id IS NULL
            ");
            $stmt->execute($params);
            $withoutReports = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return [
                'total_assessments' => (int)$total,
                'with_reports' => (int)$withReports,
                'without_reports' => (int)$withoutReports,
                'payment_status' => array_map(function($item) {
                    return [
                        'status' => $item['payment_status'],
                        'count' => (int)$item['count']
                    ];
                }, $paymentStats)
            ];

        } catch (PDOException $e) {
            throw new Exception("Failed to get assessment statistics: " . $e->getMessage());
        }
    }

    /**
     * Search assessments
     */
    public function searchAssessments($searchTerm, $userId = null) {
        $sql = "SELECT 
                    l.land_id,
                    l.user_id,
                    l.location,
                    l.size,
                    l.payment_status,
                    l.created_at as request_date,
                    l.payment_date,
                    lr.report_id,
                    lr.report_date,
                    lr.land_description,
                    lr.crop_recomendation,
                    u.firstname,
                    u.lastname,
                    u.email,
                    CASE 
                        WHEN l.payment_status = 'pending' THEN 'Payment Pending'
                        WHEN l.payment_status = 'failed' THEN 'Payment Failed'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NULL THEN 'Assessment Pending'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NOT NULL THEN COALESCE(lr.status, 'Report Submitted')
                        ELSE 'Unknown'
                    END as overall_status
                FROM land l
                LEFT JOIN land_report lr ON l.land_id = lr.land_id
                LEFT JOIN users u ON l.user_id = u.user_id
                WHERE (
                    l.location LIKE ? OR 
                    lr.land_description LIKE ? OR 
                    lr.crop_recomendation LIKE ? OR
                    u.firstname LIKE ? OR
                    u.lastname LIKE ? OR
                    u.email LIKE ?
                )";

        $params = [
            "%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%",
            "%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"
        ];

        if ($userId) {
            $sql .= " AND l.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT 50";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    "land_id" => $row["land_id"],
                    "user_id" => $row["user_id"],
                    "user_name" => trim($row["firstname"] . " " . $row["lastname"]),
                    "user_email" => $row["email"],
                    "location" => $row["location"],
                    "size" => $row["size"],
                    "payment_status" => $row["payment_status"],
                    "request_date" => $row["request_date"],
                    "payment_date" => $row["payment_date"],
                    "report_id" => $row["report_id"],
                    "report_date" => $row["report_date"],
                    "land_description" => $row["land_description"],
                    "crop_recommendation" => $row["crop_recomendation"],
                    "overall_status" => $row["overall_status"],
                    "has_report" => !empty($row["report_id"]),
                    "is_paid" => $row["payment_status"] === 'paid'
                ];
            }, $results);

        } catch (PDOException $e) {
            throw new Exception("Failed to search assessments: " . $e->getMessage());
        }
    }
}

?>