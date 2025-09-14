<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "farm_master#");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$land_id = isset($data['land_id']) ? intval($data['land_id']) : 0;
$supervisor_id = isset($data['supervisor_id']) ? intval($data['supervisor_id']) : 0;

if ($land_id && $supervisor_id) {
    $result = $conn->query("UPDATE land SET supervisor_id = $supervisor_id WHERE land_id = $land_id");
    if ($result) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
}
$conn->close();
?>
