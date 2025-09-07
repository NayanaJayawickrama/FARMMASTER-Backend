<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'database.php';

// First, get landowner user IDs
$landowner_query = "SELECT user_id FROM user WHERE user_role = 'Landowner' LIMIT 1";
$result = $conn->query($landowner_query);

if ($result->num_rows > 0) {
    $landowner = $result->fetch_assoc();
    $landowner_id = $landowner['user_id'];
    
    // Update harvest records to use existing landowner
    $update_query = "UPDATE harvest SET user_id = ? WHERE user_id = 32";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $landowner_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Harvest data updated successfully',
            'landowner_id' => $landowner_id,
            'affected_rows' => $stmt->affected_rows
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating harvest data: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No landowner found in users table'
    ]);
}

$conn->close();
?>
