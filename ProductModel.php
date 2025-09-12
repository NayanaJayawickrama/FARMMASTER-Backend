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
        $sql = "SELECT product_id, crop_name, description, price_per_unit, quantity, status, image_url FROM product";
        $result = $this->conn->query($sql);
        $products = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Only store relative image path in DB
                $products[] = $row;
            }
        }
        return $products;
    }

    public function addProduct($crop_name, $description, $price_per_unit, $quantity, $status, $image_path) {
        $sql = "INSERT INTO product (crop_name, description, price_per_unit, quantity, status, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return ["success" => false, "message" => "Failed to prepare statement."];
        }
        $stmt->bind_param("ssdiss", $crop_name, $description, $price_per_unit, $quantity, $status, $image_path);
        if ($stmt->execute()) {
            $result = ["success" => true, "message" => "Product added successfully.", "product_id" => $stmt->insert_id];
        } else {
            $result = ["success" => false, "message" => "Database insert failed: " . $stmt->error];
        }
        $stmt->close();
        return $result;
    }

    public function updateProduct($product_id, $crop_name, $description, $price_per_unit, $quantity, $status, $image_path) {
        $sql = "UPDATE product SET crop_name = ?, description = ?, price_per_unit = ?, quantity = ?, status = ?, image_url = ? WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return ["success" => false, "message" => "Failed to prepare statement."];
        }
        $stmt->bind_param("ssdissi", $crop_name, $description, $price_per_unit, $quantity, $status, $image_path, $product_id);
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