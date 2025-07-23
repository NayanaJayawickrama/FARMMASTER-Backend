<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'database.php';

$sql = "SELECT * FROM crop_inventory";
$result = $conn->query($sql);

$crops = [];

while ($row = $result->fetch_assoc()) {
    $crops[] = [
        "crop_id" => $row["crop_id"],
        "crop_name" => $row["crop_name"],
        "crop_duration" => $row["crop_duration"],
        "quantity" => $row["quantity"],
        "status" => "Available" // optional field for display like in user table
    ];
}

echo json_encode($crops);
?>
