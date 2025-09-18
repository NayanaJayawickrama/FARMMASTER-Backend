<?php

require_once __DIR__ . '/../config/Database.php';

class LandModel extends BaseModel {
    protected $table = 'land';

    public function __construct() {
        parent::__construct();
    }

    public function getAllLands($filters = []) {
        $conditions = [];
        $params = [];

        if (isset($filters['user_id']) && !empty($filters['user_id'])) {
            $conditions[] = 'user_id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
            $conditions[] = 'payment_status = :payment_status';
            $params[':payment_status'] = $filters['payment_status'];
        }

        if (isset($filters['location']) && !empty($filters['location'])) {
            $conditions[] = 'location LIKE :location';
            $params[':location'] = "%{$filters['location']}%";
        }

        $sql = "SELECT l.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN user u ON l.user_id = u.user_id";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY l.created_at DESC";

        return $this->executeQuery($sql, $params);
    }

    public function getLandById($landId) {
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN user u ON l.user_id = u.user_id 
                WHERE l.land_id = :land_id";
        $result = $this->executeQuery($sql, [':land_id' => $landId]);
        return $result ? $result[0] : null;
    }

    public function getUserLands($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->executeQuery($sql, [':user_id' => $userId]);
    }

    public function addLand($landData) {
        try {
            $data = [
                'user_id' => $landData['user_id'],
                'size' => $landData['size'],
                'location' => $landData['location'],
                'payment_status' => $landData['payment_status'] ?? 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (isset($landData['payment_date'])) {
                $data['payment_date'] = $landData['payment_date'];
            }

            $landId = $this->create($data);
            
            if ($landId) {
                return [
                    "success" => true, 
                    "message" => "Land data inserted successfully.", 
                    "land_id" => $landId
                ];
            } else {
                return ["success" => false, "message" => "Failed to insert land data."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updateLand($landId, $landData) {
        try {
            $data = [];
            
            if (isset($landData['size'])) {
                $data['size'] = $landData['size'];
            }
            if (isset($landData['location'])) {
                $data['location'] = $landData['location'];
            }
            if (isset($landData['payment_status'])) {
                $data['payment_status'] = $landData['payment_status'];
            }
            if (isset($landData['payment_date'])) {
                $data['payment_date'] = $landData['payment_date'];
            }

            if (empty($data)) {
                return ["success" => false, "message" => "No data to update."];
            }

            $result = $this->update($landId, $data, 'land_id');
            
            if ($result) {
                return ["success" => true, "message" => "Land updated successfully."];
            } else {
                return ["success" => false, "message" => "No changes made or land not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updatePaymentStatus($landId, $paymentStatus, $paymentDate = null) {
        try {
            $data = ['payment_status' => $paymentStatus];
            if ($paymentDate) {
                $data['payment_date'] = $paymentDate;
            }

            $result = $this->update($landId, $data, 'land_id');
            
            if ($result) {
                return ["success" => true, "message" => "Payment status updated successfully."];
            } else {
                return ["success" => false, "message" => "Land not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function deleteLand($landId) {
        try {
            $result = $this->delete($landId, 'land_id');
            
            if ($result) {
                return ["success" => true, "message" => "Land deleted successfully."];
            } else {
                return ["success" => false, "message" => "Land not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getLandStats() {
        $sql = "SELECT 
                    COUNT(*) as total_lands,
                    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payment,
                    SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid,
                    SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed,
                    AVG(CAST(SUBSTRING_INDEX(size, ' ', 1) AS DECIMAL(10,2))) as avg_size
                FROM {$this->table}";
        
        $result = $this->executeQuery($sql);
        return $result ? $result[0] : [];
    }

    public function searchLands($searchTerm, $userId = null) {
        $conditions = ["(l.location LIKE :search OR l.size LIKE :search)"];
        $params = [':search' => "%{$searchTerm}%"];

        if ($userId) {
            $conditions[] = "l.user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $sql = "SELECT l.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN user u ON l.user_id = u.user_id 
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY l.created_at DESC";

        return $this->executeQuery($sql, $params);
    }
}

?>