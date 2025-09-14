<?php
require_once 'ProductModel.php';

class ProductController {
    private $model;
    private $uploadDir = 'uploads/';

    public function __construct() {
        $this->model = new ProductModel();
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    private function getBaseUrlFromEnv() {
        // Load .env file and get BASE_URL
        $envPath = __DIR__ . '/.env';
        $baseUrl = '';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), 'BASE_URL=') === 0) {
                    $baseUrl = trim(explode('=', $line, 2)[1]);
                    break;
                }
            }
        }
        return rtrim($baseUrl, '/');
    }

    public function getProducts() {
        try {
            $products = $this->model->getAllProducts();
            $baseUrl = $this->getBaseUrlFromEnv();
            foreach ($products as &$product) {
                // Always return full URL for image
                if (!empty($product['image_url'])) {
                    $relativePath = ltrim($product['image_url'], '/');
                    $product['image_url'] = $baseUrl . '/' . $relativePath;
                } else {
                    $product['image_url'] = '';
                }
                // Rename crop_quantity to quantity for frontend compatibility
                if (isset($product['crop_quantity'])) {
                    $product['quantity'] = $product['crop_quantity'];
                    unset($product['crop_quantity']);
                }
            }
            return $products;
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function addProduct($data, $files) {
        $crop_id = intval($data["crop_id"] ?? 0);
        $description = trim($data["description"] ?? "");
        $price_per_unit = floatval($data["price_per_unit"] ?? 0);

        // Validate crop_id exists in crop_inventory
        $validCropIds = $this->getValidCropIds();
        if (!in_array($crop_id, $validCropIds)) {
            return ["success" => false, "message" => "Invalid crop ID."];
        }
        if ($price_per_unit <= 0) {
            return ["success" => false, "message" => "Invalid price."];
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!isset($files['image']) || $files['image']['error'] !== UPLOAD_ERR_OK) {
            return ["success" => false, "message" => "Image file is required."];
        }
        $file = $files['image'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ["success" => false, "message" => "Invalid image type. Only jpg, jpeg, png allowed."];
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('img_', true) . '.' . $ext;
        $targetPath = $this->uploadDir . $uniqueName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ["success" => false, "message" => "Failed to save image file."];
        }
        $image_path = $this->uploadDir . $uniqueName;

        $result = $this->model->addProduct($crop_id, $description, $price_per_unit, $image_path);
        $baseUrl = $this->getBaseUrlFromEnv();
        $result['image_url'] = $image_path ? $baseUrl . '/' . ltrim($image_path, '/') : '';
        return $result;
    }

    public function updateProduct($data, $files) {
        $product_id = intval($data["product_id"] ?? 0);
        $crop_id = intval($data["crop_id"] ?? 0);
        $description = trim($data["description"] ?? "");
        $price_per_unit = floatval($data["price_per_unit"] ?? 0);

        if ($product_id <= 0) {
            return ["success" => false, "message" => "Invalid product ID."];
        }
        $validCropIds = $this->getValidCropIds();
        if (!in_array($crop_id, $validCropIds)) {
            return ["success" => false, "message" => "Invalid crop ID."];
        }
        if ($price_per_unit <= 0) {
            return ["success" => false, "message" => "Invalid price."];
        }

        // Get current product info to preserve image if not changed
        $currentProducts = $this->model->getAllProducts();
        $currentProduct = null;
        foreach ($currentProducts as $prod) {
            if ($prod['product_id'] == $product_id) {
                $currentProduct = $prod;
                break;
            }
        }
        $image_path = $currentProduct ? $currentProduct['image_url'] : null;

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $file = $files['image'];
            if (!in_array($file['type'], $allowedTypes)) {
                return ["success" => false, "message" => "Invalid image type. Only jpg, jpeg, png allowed."];
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueName = uniqid('img_', true) . '.' . $ext;
            $targetPath = $this->uploadDir . $uniqueName;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return ["success" => false, "message" => "Failed to save image file."];
            }
            $image_path = $this->uploadDir . $uniqueName;
        }

        $result = $this->model->updateProduct($product_id, $crop_id, $description, $price_per_unit, $image_path);
        $baseUrl = $this->getBaseUrlFromEnv();
        $result['image_url'] = $image_path ? $baseUrl . '/' . ltrim($image_path, '/') : '';
        return $result;
    }

    private function getValidCropIds() {
        // Fetch valid crop_ids from crop_inventory
        $conn = new mysqli("localhost", "root", "", "farm_master#");
        $ids = [];
        $res = $conn->query("SELECT crop_id FROM crop_inventory");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $ids[] = intval($row['crop_id']);
            }
            $res->free();
        }
        $conn->close();
        return $ids;
    }

    public function deleteProduct($data) {
        $product_id = intval($data["product_id"] ?? 0);
        if ($product_id <= 0) {
            return ["success" => false, "message" => "Invalid product ID."];
        }
        return $this->model->deleteProduct($product_id);
    }

    public function getNewCrops() {
        $conn = new mysqli("localhost", "root", "", "farm_master#");
        $sql = "SELECT ci.crop_id, ci.crop_name, ci.quantity, ci.crop_duration
                FROM crop_inventory ci
                LEFT JOIN product p ON ci.crop_id = p.crop_id
                WHERE p.crop_id IS NULL";
        $result = $conn->query($sql);
        $newCrops = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $newCrops[] = $row;
            }
        }
        $conn->close();
        return $newCrops;
    }

    public function getAllCrops() {
        $conn = new mysqli("localhost", "root", "", "farm_master#");
        $sql = "SELECT crop_id, crop_name, quantity, crop_duration FROM crop_inventory";
        $result = $conn->query($sql);
        $crops = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $crops[] = $row;
            }
        }
        $conn->close();
        return $crops;
    }
}
?>