<?php
require_once 'ProductModel.php';

class ProductController {
    private $model;

    public function __construct() {
        $this->model = new ProductModel();
    }

    public function getProducts() {
        try {
            $products = $this->model->getAllProducts();
            return $products;
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function updateProduct($data) {
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

        if (!in_array($crop_name, $validCrops)) {
            return ["success" => false, "message" => "Invalid crop name."];
        }
        if (!in_array($status, $validStatus)) {
            return ["success" => false, "message" => "Invalid status value."];
        }

        // Call model to update
        return $this->model->updateProduct($product_id, $crop_name, $description, $price_per_unit, $quantity, $status);
    }

}
?>