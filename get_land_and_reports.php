<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "farm_master#");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// Get all supervisors
$supervisors = [];
$supRes = $conn->query("SELECT user_id, CONCAT(first_name, ' ', last_name) AS name FROM user WHERE user_role = 'Supervisor'");
while ($row = $supRes->fetch_assoc()) {
    $supervisors[] = [
        "id" => "SR" . str_pad($row['user_id'], 3, "0", STR_PAD_LEFT),
        "name" => $row['name']
    ];
}

// Get land data (with landowner name)
$lands = [];
$landRes = $conn->query(
    "SELECT l.land_id, l.location, l.created_at AS date, l.user_id AS landowner_id, CONCAT(u.first_name, ' ', u.last_name) AS landowner_name, l.supervisor_id, l.payment_status
     FROM land l
     JOIN user u ON l.user_id = u.user_id"
);
while ($row = $landRes->fetch_assoc()) {
    // Get supervisor name if assigned
    $supervisorName = "Unassigned";
    $supervisorId = "";
    if (!empty($row['supervisor_id'])) {
        $supQuery = $conn->query("SELECT CONCAT(first_name, ' ', last_name) AS name FROM user WHERE user_id = " . intval($row['supervisor_id']));
        if ($supRow = $supQuery->fetch_assoc()) {
            $supervisorName = $supRow['name'];
            $supervisorId = "SR" . str_pad($row['supervisor_id'], 3, "0", STR_PAD_LEFT);
        }
    }
    $lands[] = [
        "id" => "#2024-LR-" . str_pad($row['land_id'], 3, "0", STR_PAD_LEFT),
        "location" => $row['location'],
        "name" => $row['landowner_name'],
        "date" => substr($row['date'], 0, 10),
        "supervisorId" => $supervisorId,
        "supervisor" => $supervisorName,
        "status" => empty($supervisorId) ? "Unassigned" : "Assigned"
    ];
}

// Get land report data (with landowner and supervisor names)
$reports = [];
$reportRes = $conn->query(
    "SELECT lr.report_id, lr.land_id, lr.user_id, lr.status, l.location, CONCAT(u.first_name, ' ', u.last_name) AS landowner_name
     FROM land_report lr
     JOIN land l ON lr.land_id = l.land_id
     JOIN user u ON lr.user_id = u.user_id"
);
while ($row = $reportRes->fetch_assoc()) {
    // Get supervisor name if assigned
    $supervisorName = "Unassigned";
    $supervisorId = "";
    $landSupRes = $conn->query("SELECT supervisor_id FROM land WHERE land_id = " . intval($row['land_id']));
    if ($landSupRow = $landSupRes->fetch_assoc()) {
        if (!empty($landSupRow['supervisor_id'])) {
            $supQuery = $conn->query("SELECT CONCAT(first_name, ' ', last_name) AS name FROM user WHERE user_id = " . intval($landSupRow['supervisor_id']));
            if ($supRow = $supQuery->fetch_assoc()) {
                $supervisorName = $supRow['name'];
                $supervisorId = "SR" . str_pad($landSupRow['supervisor_id'], 3, "0", STR_PAD_LEFT);
            }
        }
    }
    $reports[] = [
        "id" => "#2024-LR-" . str_pad($row['report_id'], 3, "0", STR_PAD_LEFT),
        "location" => $row['location'],
        "name" => $row['landowner_name'],
        "supervisorId" => $supervisorId,
        "supervisor" => $supervisorName,
        "status" => $row['status']
    ];
}

echo json_encode([
    "lands" => $lands,
    "reports" => $reports,
    "supervisors" => $supervisors
]);
$conn->close();
?>
