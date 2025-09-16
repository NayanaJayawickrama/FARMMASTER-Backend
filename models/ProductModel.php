<?php

require_once __DIR__ . '/../config/Database.php';

class ProductModel extends BaseModel {
    protected $table = 'product';
    private $uploadDir = 'uploads/';

    public function __construct() {
        parent::__construct();
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function getAllProducts($filters = []) {
        $conditions = [];
        $params = [];

        if (isset($filters['status']) && !empty($filters['status'])) {
            $conditions[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        if (isset($filters['crop_name']) && !empty($filters['crop_name'])) {
            $conditions[] = 'crop_name = :crop_name';
            $params[':crop_name'] = $filters['crop_name'];
        }

        $sql = "SELECT product_id, crop_name, description, price_per_unit, quantity, status, image_url FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY product_id DESC";

        return $this->executeQuery($sql, $params);
    }

    public function getProductById($productId) {
        return $this->findById($productId, 'product_id');
    }

    public function addProduct($cropName, $description, $pricePerUnit, $quantity, $status, $imagePath) {
        try {
            $data = [
                'crop_name' => $cropName,
                'description' => $description,
                'price_per_unit' => $pricePerUnit,
                'quantity' => $quantity,
                'status' => $status,
                'image_url' => $imagePath
            ];

            $productId = $this->create($data);
            
            if ($productId) {
                return [
                    "success" => true, 
                    "message" => "Product added successfully.", 
                    "product_id" => $productId
                ];
            } else {
                return ["success" => false, "message" => "Failed to add product."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updateProduct($productId, $cropName, $description, $pricePerUnit, $quantity, $status, $imagePath) {
        try {
            $data = [
                'crop_name' => $cropName,
                'description' => $description,
                'price_per_unit' => $pricePerUnit,
                'quantity' => $quantity,
                'status' => $status,
                'image_url' => $imagePath
            ];

            $result = $this->update($productId, $data, 'product_id');
            
            if ($result === false) {
                return ["success" => false, "message" => "Database error occurred."];
            } elseif ($result > 0) {
                return ["success" => true, "message" => "Product updated successfully."];
            } else {
                return ["success" => false, "message" => "No changes were made."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function deleteProduct($productId) {
        try {
            // Get product details first to delete image file
            $product = $this->getProductById($productId);
            
            $result = $this->delete($productId, 'product_id');
            
            if ($result) {
                // Delete image file if exists
                if ($product && $product['image_url'] && file_exists($product['image_url'])) {
                    unlink($product['image_url']);
                }
                
                return ["success" => true, "message" => "Product deleted successfully."];
            } else {
                return ["success" => false, "message" => "Product not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updateProductQuantity($productId, $quantity) {
        try {
            $result = $this->update($productId, ['quantity' => $quantity], 'product_id');
            
            if ($result === false) {
                return ["success" => false, "message" => "Database error occurred."];
            } elseif ($result > 0) {
                return ["success" => true, "message" => "Product quantity updated successfully."];
            } else {
                return ["success" => false, "message" => "No changes were made."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getProductsByStatus($status) {
        return $this->getAllProducts(['status' => $status]);
    }

    public function getProductsByCrop($cropName) {
        return $this->getAllProducts(['crop_name' => $cropName]);
    }

    public function searchProducts($searchTerm) {
        $sql = "SELECT product_id, crop_name, description, price_per_unit, quantity, status, image_url 
                FROM {$this->table} 
                WHERE crop_name LIKE :search OR description LIKE :search
                ORDER BY product_id DESC";
        
        $params = [':search' => "%{$searchTerm}%"];
        
        return $this->executeQuery($sql, $params);
    }

    public function getProductStats() {
        $sql = "SELECT 
                    crop_name,
                    COUNT(*) as total_products,
                    SUM(quantity) as total_quantity,
                    AVG(price_per_unit) as avg_price,
                    SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'Sold' THEN 1 ELSE 0 END) as sold,
                    SUM(CASE WHEN status = 'Unavailable' THEN 1 ELSE 0 END) as unavailable
                FROM {$this->table} 
                GROUP BY crop_name";
        
        return $this->executeQuery($sql);
    }

    public function getLowStockProducts($threshold = 10) {
        $sql = "SELECT product_id, crop_name, description, price_per_unit, quantity, status, image_url 
                FROM {$this->table} 
                WHERE quantity <= :threshold AND status = 'Available'
                ORDER BY quantity ASC";
        
        $params = [':threshold' => $threshold];
        
        return $this->executeQuery($sql, $params);
    }
}

?>