<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class ProductController {
    private $model;
    private $uploadDir = 'uploads/';
    private $validCrops = ['Carrot', 'Leeks', 'Tomato', 'Cabbage'];
    private $validStatus = ['Available', 'Sold', 'Unavailable'];
    private $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

    public function __construct() {
        $this->model = new ProductModel();
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function getProducts() {
        try {
            $filters = [];
            
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['crop_name'])) {
                $filters['crop_name'] = $_GET['crop_name'];
            }

            $products = $this->model->getAllProducts($filters);
            
            // Map fields and add computed image_url for each product
            $baseUrl = $this->getBaseUrl();
            foreach ($products as &$product) {
                // Map database fields to frontend-expected field names
                $product['id'] = $product['product_id'];
                $product['name'] = $product['crop_name'];
                $product['price'] = $product['price_per_unit'];
                
                // Ensure image_url is fully qualified
                $product['image_url'] = $product['image_url'] ? $baseUrl . '/' . ltrim($product['image_url'], '/') : '';
            }
            
            Response::success("Products retrieved successfully", $products);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProduct($productId) {
        try {
            $product = $this->model->getProductById($productId);
            
            if (!$product) {
                Response::notFound("Product not found");
            }

            // Map fields and add computed image_url
            $baseUrl = $this->getBaseUrl();
            $product['id'] = $product['product_id'];
            $product['name'] = $product['crop_name'];
            $product['price'] = $product['price_per_unit'];
            $product['image_url'] = $product['image_url'] ? $baseUrl . '/' . ltrim($product['image_url'], '/') : '';
            
            Response::success("Product retrieved successfully", $product);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function addProduct() {
        try {
            // Check authentication and authorization
            SessionManager::requireRole(['Landowner', 'Operational_Manager', 'Financial_Manager']);

            $cropName = Validator::required($_POST["crop_name"] ?? "", "Crop name");
            $description = Validator::required($_POST["description"] ?? "", "Description");
            $pricePerUnit = Validator::numeric($_POST["price_per_unit"] ?? 0, "Price per unit", 0.01);
            $quantity = Validator::numeric($_POST["quantity"] ?? 0, "Quantity", 0);
            $status = Validator::required($_POST["status"] ?? "", "Status");

            // Validate crop name and status
            $cropName = Validator::inArray($cropName, $this->validCrops, "Crop name");
            $status = Validator::inArray($status, $this->validStatus, "Status");

            // Handle image upload
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                Response::error("Image file is required");
            }

            $file = $_FILES['image'];
            if (!in_array($file['type'], $this->allowedTypes)) {
                Response::error("Invalid image type. Only jpg, jpeg, png allowed");
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueName = uniqid('img_', true) . '.' . $ext;
            $targetPath = $this->uploadDir . $uniqueName;
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                Response::error("Failed to save image file");
            }

            $imagePath = $this->uploadDir . $uniqueName;

            $result = $this->model->addProduct($cropName, $description, $pricePerUnit, $quantity, $status, $imagePath);

            if ($result['success']) {
                // Add computed image_url for response
                $baseUrl = $this->getBaseUrl();
                $result['image_url'] = $baseUrl . '/' . ltrim($imagePath, '/');
                Response::success($result['message'], $result, 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateProduct($productId) {
        try {
            // Check authentication and authorization
            SessionManager::requireRole(['Landowner', 'Operational_Manager', 'Financial_Manager']);

            // Use $_POST for form data (works with method override)
            $cropName = Validator::required($_POST["crop_name"] ?? "", "Crop name");
            $description = Validator::required($_POST["description"] ?? "", "Description");
            $pricePerUnit = Validator::numeric($_POST["price_per_unit"] ?? 0, "Price per unit", 0.01);
            $quantity = Validator::numeric($_POST["quantity"] ?? 0, "Quantity", 0);
            $status = Validator::required($_POST["status"] ?? "", "Status");

            // Validate crop name and status
            $cropName = Validator::inArray($cropName, $this->validCrops, "Crop name");
            $status = Validator::inArray($status, $this->validStatus, "Status");

            // Get current product info to preserve image if not changed
            $currentProduct = $this->model->getProductById($productId);
            if (!$currentProduct) {
                Response::notFound("Product not found");
            }

            $imagePath = $currentProduct['image_url'];

            // Handle new image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                if (!in_array($file['type'], $this->allowedTypes)) {
                    Response::error("Invalid image type. Only jpg, jpeg, png allowed");
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uniqueName = uniqid('img_', true) . '.' . $ext;
                $targetPath = $this->uploadDir . $uniqueName;
                
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    Response::error("Failed to save image file");
                }

                // Delete old image file
                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $imagePath = $this->uploadDir . $uniqueName;
            }

            $result = $this->model->updateProduct($productId, $cropName, $description, $pricePerUnit, $quantity, $status, $imagePath);

            if ($result['success']) {
                // Add computed image_url for response
                $baseUrl = $this->getBaseUrl();
                $result['image_url'] = $imagePath ? $baseUrl . '/' . ltrim($imagePath, '/') : '';
                Response::success($result['message'], $result);
            } else {
                // Check if it's a "no changes" case vs actual error
                if ($result['message'] === 'No changes were made.') {
                    // Return 200 status but with success=false so frontend can handle appropriately
                    Response::json(['status' => 'info', 'message' => $result['message']], 200);
                } else {
                    Response::error($result['message']);
                }
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function deleteProduct($productId) {
        try {
            // Check authentication and authorization
            SessionManager::requireRole(['Landowner', 'Operational_Manager', 'Financial_Manager']);

            $result = $this->model->deleteProduct($productId);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateProductQuantity($productId) {
        try {
            SessionManager::requireRole(['Landowner', 'Operational_Manager', 'Financial_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $quantity = Validator::numeric($data['quantity'] ?? 0, "Quantity", 0);

            $result = $this->model->updateProductQuantity($productId, $quantity);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchProducts() {
        try {
            $searchTerm = $_GET['search'] ?? '';

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $products = $this->model->searchProducts($searchTerm);

            // Add computed image_url for each product
            $baseUrl = $this->getBaseUrl();
            foreach ($products as &$product) {
                $product['image_url'] = $product['image_url'] ? $baseUrl . '/' . ltrim($product['image_url'], '/') : '';
            }

            Response::success("Search completed", $products);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProductStats() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);

            $stats = $this->model->getProductStats();

            Response::success("Product statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getLowStockProducts() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Landowner']);

            $threshold = $_GET['threshold'] ?? 10;
            $threshold = Validator::numeric($threshold, "Threshold", 1);

            $products = $this->model->getLowStockProducts($threshold);

            // Add computed image_url for each product
            $baseUrl = $this->getBaseUrl();
            foreach ($products as &$product) {
                $product['image_url'] = $product['image_url'] ? $baseUrl . '/' . ltrim($product['image_url'], '/') : '';
            }

            Response::success("Low stock products retrieved", $products);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProductsByStatus($status) {
        try {
            $status = Validator::inArray($status, $this->validStatus, "Status");

            $products = $this->model->getProductsByStatus($status);

            // Add computed image_url for each product
            $baseUrl = $this->getBaseUrl();
            foreach ($products as &$product) {
                $product['image_url'] = $product['image_url'] ? $baseUrl . '/' . ltrim($product['image_url'], '/') : '';
            }

            Response::success("Products retrieved successfully", $products);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProductsByCrop($cropName) {
        try {
            $cropName = Validator::inArray($cropName, $this->validCrops, "Crop name");

            $products = $this->model->getProductsByCrop($cropName);

            // Add computed image_url for each product
            $baseUrl = $this->getBaseUrl();
            foreach ($products as &$product) {
                $product['image_url'] = $product['image_url'] ? $baseUrl . '/' . ltrim($product['image_url'], '/') : '';
            }

            Response::success("Products retrieved successfully", $products);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        return $protocol . '://' . $host . $scriptDir;
    }
}

?>