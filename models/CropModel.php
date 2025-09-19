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

        if (isset($filters['status']) && !empty($filters['status'])) {
            $conditions[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql = "SELECT crop_id, crop_name, crop_duration, quantity, status FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY crop_id DESC";

        return $this->executeQuery($sql, $params);
    }

    public function getCropById($cropId) {
        $sql = "SELECT * FROM {$this->table} WHERE crop_id = :crop_id";
        $result = $this->executeQuery($sql, [':crop_id' => $cropId]);
        return $result ? $result[0] : null;
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
                'quantity' => $cropData['quantity'],
                'status' => $cropData['status'] ?? 'Available'
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

    public function updateCropStatus($cropId, $status) {
        try {
            // Validate status
            $validStatuses = ['Available', 'Unavailable', 'Sold'];
            if (!in_array($status, $validStatuses)) {
                return ["success" => false, "message" => "Invalid status value."];
            }

            $result = $this->update($cropId, ['status' => $status], 'crop_id');
            
            if ($result === false) {
                return ["success" => false, "message" => "Failed to update crop status."];
            } elseif ($result === 0) {
                return ["success" => false, "message" => "Crop not found or no changes made."];
            } else {
                return ["success" => true, "message" => "Crop status updated successfully."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function deleteCrop($cropId) {
        try {
            // Check if crop has associated products
            $productCheck = $this->executeQuery(
                "SELECT COUNT(*) as count FROM product WHERE crop_id = :crop_id",
                [':crop_id' => $cropId]
            );
            
            if ($productCheck && $productCheck[0]['count'] > 0) {
                return [
                    "success" => false, 
                    "message" => "Cannot delete crop. It has associated products. Delete products first."
                ];
            }
            
            // Check if crop exists
            $crop = $this->getCropById($cropId);
            if (!$crop) {
                return ["success" => false, "message" => "Crop not found."];
            }
            
            $result = $this->delete($cropId, 'crop_id');
            
            if ($result) {
                return ["success" => true, "message" => "Crop deleted successfully."];
            } else {
                return ["success" => false, "message" => "Failed to delete crop."];
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
        $sql = "SELECT crop_id, crop_name, crop_duration, quantity, status 
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
                    MAX(quantity) as max_quantity,
                    SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available_count,
                    SUM(CASE WHEN status = 'Unavailable' THEN 1 ELSE 0 END) as unavailable_count,
                    SUM(CASE WHEN status = 'Sold' THEN 1 ELSE 0 END) as sold_count
                FROM {$this->table}";
        
        $result = $this->executeQuery($sql);
        return $result ? $result[0] : [];
    }

    public function getLowQuantityCrops($threshold = 10) {
        $sql = "SELECT crop_id, crop_name, crop_duration, quantity, status 
                FROM {$this->table} 
                WHERE quantity <= :threshold
                ORDER BY quantity ASC";
        
        $params = [':threshold' => $threshold];
        
        return $this->executeQuery($sql, $params);
    }

    public function getCropsByStatus($status) {
        $sql = "SELECT crop_id, crop_name, crop_duration, quantity, status 
                FROM {$this->table} 
                WHERE status = :status
                ORDER BY crop_id DESC";
        
        $params = [':status' => $status];
        
        return $this->executeQuery($sql, $params);
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
                    status,
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