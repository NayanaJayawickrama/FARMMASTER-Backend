<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'database.php';

$sql = "SELECT * FROM user";
$result = $conn->query($sql);

$users = [];

while($row = $result->fetch_assoc()) {
    $users[] = [
        "user_id" => $row["user_id"],
        "name" => $row["first_name"] . " " . $row["last_name"],
        "email" => $row["email"],
        "role" => $row["user_role"],
        "status" => ($row["is_active"] == 1) ? "Active" : "Inactive",
    ];
}

echo json_encode($users);
?>
