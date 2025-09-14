<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'database.php';

// Update proposals records to use existing landowner (user_id 21)
$update_query = "UPDATE proposals SET user_id = 21 WHERE user_id = 32";
$result = $conn->query($update_query);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Proposals data updated successfully',
        'affected_rows' => $conn->affected_rows
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating proposals data: ' . $conn->error
    ]);
}

$conn->close();
?>
