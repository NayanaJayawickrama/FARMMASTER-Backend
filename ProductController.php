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

    public function getProducts() {
        try {
            $products = $this->model->getAllProducts();
            // Add computed image_url for each product
            $baseUrl = $this->getBaseUrl();
            foreach ($products as &$product) {
                $product['image_url'] = $product['image_url'] ? $baseUrl . '/' . ltrim($product['image_url'], '/') : '';
            }
            return $products;
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        return $protocol . '://' . $host . $scriptDir;
    }

    public function addProduct($data, $files) {
        $crop_name = trim($data["crop_name"] ?? "");
        $description = trim($data["description"] ?? "");
        $price_per_unit = floatval($data["price_per_unit"] ?? 0);
        $quantity = floatval($data["quantity"] ?? 0);
        $status = trim($data["status"] ?? "");

        $validCrops = ['Carrot','Leeks','Tomato','Cabbage'];
        $validStatus = ['Available', 'Sold', 'Unavailable'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        if (!in_array($crop_name, $validCrops)) {
            return ["success" => false, "message" => "Invalid crop name."];
        }
        if (!in_array($status, $validStatus)) {
            return ["success" => false, "message" => "Invalid status value."];
        }
        if ($price_per_unit <= 0 || $quantity < 0) {
            return ["success" => false, "message" => "Invalid price or quantity."];
        }

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

        return $this->model->addProduct($crop_name, $description, $price_per_unit, $quantity, $status, $image_path);
    }

    public function updateProduct($data, $files) {
        $product_id = intval($data["product_id"] ?? 0);
        $crop_name = trim($data["crop_name"] ?? "");
        $description = trim($data["description"] ?? "");
        $price_per_unit = floatval($data["price_per_unit"] ?? 0);
        $quantity = floatval($data["quantity"] ?? 0);
        $status = trim($data["status"] ?? "");

        if ($product_id <= 0) {
            return ["success" => false, "message" => "Invalid product ID."];
        }
        $validCrops = ['Carrot','Leeks','Tomato','Cabbage'];
        $validStatus = ['Available', 'Sold', 'Unavailable'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($crop_name, $validCrops)) {
            return ["success" => false, "message" => "Invalid crop name."];
        }
        if (!in_array($status, $validStatus)) {
            return ["success" => false, "message" => "Invalid status value."];
        }
        if ($price_per_unit <= 0 || $quantity < 0) {
            return ["success" => false, "message" => "Invalid price or quantity."];
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
        // If no image uploaded, keep existing image_path

        $result = $this->model->updateProduct($product_id, $crop_name, $description, $price_per_unit, $quantity, $status, $image_path);
        // Always return the computed image_url for frontend
        $baseUrl = $this->getBaseUrl();
        $result['image_url'] = $image_path ? $baseUrl . '/' . ltrim($image_path, '/') : '';
        return $result;
    }

    public function deleteProduct($data) {
        $product_id = intval($data["product_id"] ?? 0);
        if ($product_id <= 0) {
            return ["success" => false, "message" => "Invalid product ID."];
        }
        return $this->model->deleteProduct($product_id);
    }
}
?>