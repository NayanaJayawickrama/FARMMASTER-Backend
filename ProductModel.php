<?php
class ProductModel {
    private $conn;

    public function __construct() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "farm_master#";
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed");
        }
    }

    public function getAllProducts() {
        // Join with crop_inventory to get crop_name and quantity
        $sql = "SELECT p.product_id, p.crop_id, ci.crop_name, ci.quantity AS crop_quantity, ci.crop_duration, p.price_per_unit, p.description, p.image_url
                FROM product p
                JOIN crop_inventory ci ON p.crop_id = ci.crop_id";
        $result = $this->conn->query($sql);
        $products = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Always store image_url as relative path
                if (!empty($row['image_url'])) {
                    $row['image_url'] = preg_replace('#^https?://[^/]+/#', '', $row['image_url']);
                }
                $products[] = $row;
            }
        }
        return $products;
    }

    public function addProduct($crop_id, $description, $price_per_unit, $image_path) {
        $sql = "INSERT INTO product (crop_id, description, price_per_unit, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return ["success" => false, "message" => "Failed to prepare statement."];
        }
        $stmt->bind_param("isds", $crop_id, $description, $price_per_unit, $image_path);
        if ($stmt->execute()) {
            $result = ["success" => true, "message" => "Product added successfully.", "product_id" => $stmt->insert_id];
        } else {
            $result = ["success" => false, "message" => "Database insert failed: " . $stmt->error];
        }
        $stmt->close();
        return $result;
    }

    public function updateProduct($product_id, $crop_id, $description, $price_per_unit, $image_path) {
        $sql = "UPDATE product SET crop_id = ?, description = ?, price_per_unit = ?, image_url = ? WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return ["success" => false, "message" => "Failed to prepare statement."];
        }
        $stmt->bind_param("isdsi", $crop_id, $description, $price_per_unit, $image_path, $product_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $result = ["success" => true, "message" => "Product updated successfully."];
            } else {
                $result = ["success" => false, "message" => "No changes made or product not found."];
            }
        } else {
            $result = ["success" => false, "message" => "Database update failed: " . $stmt->error];
        }
        $stmt->close();
        return $result;
    }

    public function deleteProduct($product_id) {
        $sql = "DELETE FROM product WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return ["success" => false, "message" => "Failed to prepare statement."];
        }
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $result = ["success" => true, "message" => "Product deleted successfully."];
            } else {
                $result = ["success" => false, "message" => "Product not found."];
            }
        } else {
            $result = ["success" => false, "message" => "Database delete failed: " . $stmt->error];
        }
        $stmt->close();
        return $result;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>