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
        $sql = "SELECT product_id, crop_name, description, price_per_unit, quantity, status FROM product";
        $result = $this->conn->query($sql);
        $products = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

   
    public function updateProduct($product_id, $crop_name, $description, $price_per_unit, $quantity, $status) {
        $sql = "UPDATE product SET 
                    crop_name = ?, 
                    description = ?, 
                    price_per_unit = ?, 
                    quantity = ?, 
                    status = ? 
                WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return ["success" => false, "message" => "Failed to prepare statement."];
        }
        $stmt->bind_param("ssdiss", $crop_name, $description, $price_per_unit, $quantity, $status, $product_id);
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

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>