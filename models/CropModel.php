<?php

require_once __DIR__ . '/../config/Database.php';

class CropModel extends BaseModel {
    protected $table = 'crop_inventory';

    public function __construct() {
        parent::__construct();
    }

    public function getAllCrops($filters = []) {
        $conditions = [];
        $params = [];

        if (isset($filters['crop_name']) && !empty($filters['crop_name'])) {
            $conditions[] = 'crop_name LIKE :crop_name';
            $params[':crop_name'] = "%{$filters['crop_name']}%";
        }

        $sql = "SELECT crop_id, crop_name, crop_duration, quantity FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY crop_id DESC";

        return $this->executeQuery($sql, $params);
    }

    public function getCropById($cropId) {
        return $this->findById($cropId, 'crop_id');
    }

    public function getCropByName($cropName) {
        $sql = "SELECT * FROM {$this->table} WHERE crop_name = :crop_name";
        $result = $this->executeQuery($sql, [':crop_name' => $cropName]);
        return $result ? $result[0] : null;
    }

    public function addCrop($cropData) {
        try {
            $data = [
                'crop_name' => $cropData['crop_name'],
                'crop_duration' => $cropData['crop_duration'],
                'quantity' => $cropData['quantity']
            ];

            $cropId = $this->create($data);
            
            if ($cropId) {
                return [
                    "success" => true, 
                    "message" => "Crop added successfully.", 
                    "crop_id" => $cropId
                ];
            } else {
                return ["success" => false, "message" => "Failed to add crop."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updateCrop($cropId, $cropData) {
        try {
            $data = [];
            
            if (isset($cropData['crop_name'])) {
                $data['crop_name'] = $cropData['crop_name'];
            }
            if (isset($cropData['crop_duration'])) {
                $data['crop_duration'] = $cropData['crop_duration'];
            }
            if (isset($cropData['quantity'])) {
                $data['quantity'] = $cropData['quantity'];
            }
            if (isset($cropData['status'])) {
                $data['status'] = $cropData['status'];
            }

            if (empty($data)) {
                return ["success" => false, "message" => "No data to update."];
            }

            $result = $this->update($cropId, $data, 'crop_id');
            
            if ($result === false) {
                return ["success" => false, "message" => "Failed to update crop."];
            } elseif ($result === 0) {
                return ["success" => false, "message" => "No changes made to the crop."];
            } else {
                return ["success" => true, "message" => "Crop updated successfully."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function deleteCrop($cropId) {
        try {
            $result = $this->delete($cropId, 'crop_id');
            
            if ($result) {
                return ["success" => true, "message" => "Crop deleted successfully."];
            } else {
                return ["success" => false, "message" => "Crop not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updateCropQuantity($cropId, $quantity) {
        try {
            $result = $this->update($cropId, ['quantity' => $quantity], 'crop_id');
            
            if ($result) {
                return ["success" => true, "message" => "Crop quantity updated successfully."];
            } else {
                return ["success" => false, "message" => "Crop not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function searchCrops($searchTerm) {
        $sql = "SELECT crop_id, crop_name, crop_duration, quantity 
                FROM {$this->table} 
                WHERE crop_name LIKE :search
                ORDER BY crop_id DESC";
        
        $params = [':search' => "%{$searchTerm}%"];
        
        return $this->executeQuery($sql, $params);
    }

    public function getCropStats() {
        $sql = "SELECT 
                    COUNT(*) as total_crops,
                    SUM(quantity) as total_quantity,
                    AVG(crop_duration) as avg_duration,
                    MIN(quantity) as min_quantity,
                    MAX(quantity) as max_quantity
                FROM {$this->table}";
        
        $result = $this->executeQuery($sql);
        return $result ? $result[0] : [];
    }

    public function getLowQuantityCrops($threshold = 10) {
        $sql = "SELECT crop_id, crop_name, crop_duration, quantity 
                FROM {$this->table} 
                WHERE quantity <= :threshold
                ORDER BY quantity ASC";
        
        $params = [':threshold' => $threshold];
        
        return $this->executeQuery($sql, $params);
    }

    public function getCropsByStatus($status) {
        // Since status column doesn't exist, return all crops
        return $this->getAllCrops();
    }

    public function cropNameExists($cropName, $excludeId = null) {
        $sql = "SELECT crop_id FROM {$this->table} WHERE crop_name = :crop_name";
        $params = [':crop_name' => $cropName];

        if ($excludeId) {
            $sql .= " AND crop_id != :crop_id";
            $params[':crop_id'] = $excludeId;
        }

        $result = $this->executeQuery($sql, $params);
        return !empty($result);
    }

    public function getDashboardStats() {
        $sql = "SELECT 
                    crop_name,
                    quantity,
                    crop_duration,
                    CASE 
                        WHEN quantity <= 5 THEN 'Critical'
                        WHEN quantity <= 15 THEN 'Low'
                        ELSE 'Normal'
                    END as stock_level
                FROM {$this->table} 
                ORDER BY quantity ASC";
        
        return $this->executeQuery($sql);
    }
}

?>