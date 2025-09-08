<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Only POST method is allowed"]);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['land_id', 'user_id', 'land_description', 'crop_recommendation'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        echo json_encode(["error" => "Missing required field: $field"]);
        exit;
    }
}

$land_id = intval($input['land_id']);
$user_id = intval($input['user_id']);
$land_description = trim($input['land_description']);
$crop_recommendation = trim($input['crop_recommendation']);

// Optional soil analysis fields
$ph_value = isset($input['ph_value']) && !empty($input['ph_value']) ? floatval($input['ph_value']) : null;
$organic_matter = isset($input['organic_matter']) && !empty($input['organic_matter']) ? floatval($input['organic_matter']) : null;
$nitrogen_level = isset($input['nitrogen_level']) && !empty($input['nitrogen_level']) ? trim($input['nitrogen_level']) : null;
$phosphorus_level = isset($input['phosphorus_level']) && !empty($input['phosphorus_level']) ? trim($input['phosphorus_level']) : null;
$potassium_level = isset($input['potassium_level']) && !empty($input['potassium_level']) ? trim($input['potassium_level']) : null;
$environmental_notes = isset($input['environmental_notes']) && !empty($input['environmental_notes']) ? trim($input['environmental_notes']) : null;

try {
    // Check if land exists and belongs to user
    $check_sql = "SELECT land_id FROM land WHERE land_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $land_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(["error" => "Land not found or doesn't belong to this user"]);
        exit;
    }

    // Check if report already exists
    $existing_sql = "SELECT report_id FROM land_report WHERE land_id = ? AND user_id = ?";
    $existing_stmt = $conn->prepare($existing_sql);
    $existing_stmt->bind_param("ii", $land_id, $user_id);
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();

    if ($existing_result->num_rows > 0) {
        // Update existing report
        $update_sql = "UPDATE land_report SET 
                        land_description = ?,
                        crop_recomendation = ?,
                        ph_value = ?,
                        organic_matter = ?,
                        nitrogen_level = ?,
                        phosphorus_level = ?,
                        potassium_level = ?,
                        environmental_notes = ?,
                        report_date = CURDATE(),
                        status = 'Approved',
                        updated_at = CURRENT_TIMESTAMP
                      WHERE land_id = ? AND user_id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssdsssssii", 
            $land_description, 
            $crop_recommendation,
            $ph_value,
            $organic_matter,
            $nitrogen_level,
            $phosphorus_level,
            $potassium_level,
            $environmental_notes,
            $land_id, 
            $user_id
        );

        if ($update_stmt->execute()) {
            $existing_report = $existing_result->fetch_assoc();
            echo json_encode([
                "success" => true,
                "message" => "Land report updated successfully",
                "report_id" => $existing_report['report_id']
            ]);
        } else {
            echo json_encode(["error" => "Failed to update land report"]);
        }
    } else {
        // Insert new report
        $insert_sql = "INSERT INTO land_report 
                        (land_id, user_id, report_date, land_description, crop_recomendation, 
                         ph_value, organic_matter, nitrogen_level, phosphorus_level, potassium_level, 
                         environmental_notes, status) 
                      VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, 'Approved')";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iissdsssss", 
            $land_id, 
            $user_id, 
            $land_description, 
            $crop_recommendation,
            $ph_value,
            $organic_matter,
            $nitrogen_level,
            $phosphorus_level,
            $potassium_level,
            $environmental_notes
        );

        if ($insert_stmt->execute()) {
            $report_id = $conn->insert_id;
            echo json_encode([
                "success" => true,
                "message" => "Land report submitted successfully",
                "report_id" => $report_id
            ]);
        } else {
            echo json_encode(["error" => "Failed to submit land report"]);
        }
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
?>
