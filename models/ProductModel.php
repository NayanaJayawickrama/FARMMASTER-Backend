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

        // Join crop_inventory for crop_name, quantity, status
        $sql = "SELECT p.product_id, p.price_per_unit, p.description, p.image_url, p.is_featured, 
                       c.crop_name, c.quantity, c.status
                FROM {$this->table} p
                JOIN crop_inventory c ON p.crop_id = c.crop_id";

        if (isset($filters['status']) && !empty($filters['status'])) {
            $conditions[] = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (isset($filters['crop_name']) && !empty($filters['crop_name'])) {
            $conditions[] = 'c.crop_name = :crop_name';
            $params[':crop_name'] = $filters['crop_name'];
        }
        if (isset($filters['is_featured'])) {
            $conditions[] = 'p.is_featured = :is_featured';
            $params[':is_featured'] = $filters['is_featured'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY p.product_id DESC";

        $products = $this->executeQuery($sql, $params);

        // Auto-set status to Sold if quantity is 0
        foreach ($products as &$product) {
            if ($product['quantity'] == 0) {
                $product['status'] = 'Sold';
            }
        }
        return $products;
    }

    public function getProductById($productId) {
        $sql = "SELECT p.product_id, p.price_per_unit, p.description, p.image_url, p.is_featured, 
                       c.crop_name, c.quantity, c.status
                FROM {$this->table} p
                JOIN crop_inventory c ON p.crop_id = c.crop_id
                WHERE p.product_id = :product_id";
        $params = [':product_id' => $productId];
        $product = $this->executeQuery($sql, $params);
        if ($product && isset($product[0])) {
            if ($product[0]['quantity'] == 0) {
                $product[0]['status'] = 'Sold';
            }
            return $product[0];
        }
        return null;
    }

    public function addProduct($cropId, $pricePerUnit, $description, $imagePath, $isFeatured = 0) {
        try {
            $data = [
                'crop_id' => $cropId,
                'price_per_unit' => $pricePerUnit,
                'description' => $description,
                'image_url' => $imagePath,
                'is_featured' => $isFeatured
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

    // Only allow updating price, description, image, is_featured
    public function updateProduct($productId, $pricePerUnit, $description, $imagePath, $isFeatured = null) {
        try {
            $data = [
                'price_per_unit' => $pricePerUnit,
                'description' => $description,
                'image_url' => $imagePath
            ];
            // Only update is_featured if provided
            if ($isFeatured !== null) {
                $data['is_featured'] = $isFeatured;
            }
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

    public function getNewCropsForProduct() {
        $sql = "SELECT c.crop_id, c.crop_name
                FROM crop_inventory c
                LEFT JOIN product p ON c.crop_id = p.crop_id
                WHERE p.crop_id IS NULL";
        return $this->executeQuery($sql);
    }

    public function getAvailableQuantity($productId) {
        try {
            $sql = "SELECT c.quantity as available_quantity, c.crop_name
                    FROM {$this->table} p
                    JOIN crop_inventory c ON p.crop_id = c.crop_id
                    WHERE p.product_id = :product_id";
            $params = [':product_id' => $productId];
            $result = $this->executeQuery($sql, $params);
            
            if ($result && isset($result[0])) {
                return [
                    'success' => true,
                    'available_quantity' => (int)$result[0]['available_quantity'],
                    'crop_name' => $result[0]['crop_name']
                ];
            }
            return ['success' => false, 'message' => 'Product not found'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function validateCartQuantity($productId, $requestedQuantity) {
        $availableData = $this->getAvailableQuantity($productId);
        
        if (!$availableData['success']) {
            return $availableData;
        }
        
        $availableQuantity = $availableData['available_quantity'];
        $cropName = $availableData['crop_name'];
        
        // Debug logging
        error_log("Product ID: $productId, Available: $availableQuantity, Requested: $requestedQuantity, Crop: $cropName");
        
        if ($requestedQuantity > $availableQuantity) {
            return [
                'success' => false,
                'available_quantity' => $availableQuantity,
                'requested_quantity' => $requestedQuantity,
                'crop_name' => $cropName,
                'message' => "Sorry! Only {$availableQuantity} Kg of {$cropName} is available in stock. You requested {$requestedQuantity} Kg."
            ];
        }
        
        return [
            'success' => true,
            'available_quantity' => $availableQuantity,
            'crop_name' => $cropName,
            'message' => 'Quantity is valid'
        ];
    }
}

?>