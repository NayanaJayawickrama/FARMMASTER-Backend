<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "farm_master#");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}
$sql = "SELECT l.land_id AS id, l.location, CONCAT(u.first_name, ' ', u.last_name) AS name, 
               l.created_at AS date, l.payment_status AS status
        FROM land l
        JOIN user u ON l.user_id = u.user_id";
$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) {
    $row['id'] = "#2024-LR-" . str_pad($row['id'], 3, "0", STR_PAD_LEFT); // mimic frontend ID format
    $row['supervisor'] = "Unassigned"; // Placeholder, update if supervisor info is available
    $row['supervisorId'] = "";
    $data[] = $row;
}
echo json_encode($data);
$conn->close();
?>
