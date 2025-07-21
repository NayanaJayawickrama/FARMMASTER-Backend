<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_master#";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die(json_encode(["error" => "Database connection failed"]));
}

$sql = "SELECT product_id, crop_name, description, price_per_unit, quantity, status FROM product";
$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
}

$conn->close();
echo json_encode($products);
?>
