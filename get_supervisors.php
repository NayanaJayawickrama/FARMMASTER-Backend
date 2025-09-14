<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "farm_master#");
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$result = $conn->query("SELECT user_id, CONCAT(first_name, ' ', last_name) AS name FROM user WHERE user_role = 'Supervisor'");
$supervisors = [];
while ($row = $result->fetch_assoc()) {
    $supervisors[] = [
        "id" => "SR" . str_pad($row['user_id'], 3, "0", STR_PAD_LEFT),
        "name" => $row['name']
    ];
}
echo json_encode($supervisors);
$conn->close();
?>
