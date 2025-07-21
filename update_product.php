<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_master#";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid input."]);
    exit;
}

// Sanitize and validate inputs
$product_id = intval($data["product_id"] ?? 0);
$crop_name = $conn->real_escape_string(trim($data["crop_name"] ?? ""));
$description = $conn->real_escape_string(trim($data["description"] ?? ""));
$price_per_unit = floatval($data["price_per_unit"] ?? 0);
$quantity = floatval($data["quantity"] ?? 0);
$status = $conn->real_escape_string(trim($data["status"] ?? ""));

// Validate product_id
if ($product_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product ID."]);
    exit;
}

// ENUM validation with exact casing
$validCrops = ['Carrots', 'Leeks', 'Tomatoes', 'Cabbage'];
$validStatus = ['Available', 'Sold', 'Unavailable'];

if (!in_array($crop_name, $validCrops)) {
    echo json_encode(["success" => false, "message" => "Invalid crop name."]);
    exit;
}

if (!in_array($status, $validStatus)) {
    echo json_encode(["success" => false, "message" => "Invalid status value."]);
    exit;
}

// Prepare update query
$sql = "UPDATE product SET 
            crop_name = ?, 
            description = ?, 
            price_per_unit = ?, 
            quantity = ?, 
            status = ? 
        WHERE product_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Failed to prepare statement."]);
    exit;
}

$stmt->bind_param("ssdiss", $crop_name, $description, $price_per_unit, $quantity, $status, $product_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Product updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made or product not found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Database update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
