<?php
/**
 * Payment Integration Script
 * Automatically creates land reports when payment is completed
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'farm_master#';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Handle GET request for testing
        $landId = $_GET['land_id'] ?? null;
        $userId = $_GET['user_id'] ?? null;
    } else {
        // Handle POST request
        $landId = $input['land_id'] ?? null;
        $userId = $input['user_id'] ?? null;
    }
    
    if (!$landId || !$userId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Land ID and User ID are required'
        ]);
        exit();
    }
    
    // Check if payment is completed for this land
    $checkPaymentSql = "SELECT land_id, user_id, location, size, payment_status, payment_date 
                        FROM land 
                        WHERE land_id = :land_id AND user_id = :user_id AND payment_status = 'paid'";
    
    $checkStmt = $pdo->prepare($checkPaymentSql);
    $checkStmt->execute([
        ':land_id' => $landId,
        ':user_id' => $userId
    ]);
    
    $paidLand = $checkStmt->fetch();
    
    if (!$paidLand) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No paid land found with the specified ID for this user'
        ]);
        exit();
    }
    
    // Check if report already exists
    $checkReportSql = "SELECT report_id FROM land_report WHERE land_id = :land_id";
    $checkReportStmt = $pdo->prepare($checkReportSql);
    $checkReportStmt->execute([':land_id' => $landId]);
    
    if ($checkReportStmt->fetch()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Land report already exists for this land',
            'land_id' => $landId
        ]);
        exit();
    }
    
    // Generate sample report data
    $sampleData = generateSampleReportData($paidLand['location']);
    
    // Create land report
    $insertSql = "INSERT INTO land_report (
        land_id, 
        user_id, 
        report_date, 
        land_description, 
        crop_recomendation, 
        status,
        ph_value,
        organic_matter,
        nitrogen_level,
        phosphorus_level,
        potassium_level,
        environmental_notes,
        created_at,
        updated_at
    ) VALUES (
        :land_id,
        :user_id,
        :report_date,
        :land_description,
        :crop_recommendation,
        'Pending',
        :ph_value,
        :organic_matter,
        :nitrogen_level,
        :phosphorus_level,
        :potassium_level,
        :environmental_notes,
        NOW(),
        NOW()
    )";
    
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        ':land_id' => $landId,
        ':user_id' => $userId,
        ':report_date' => $paidLand['payment_date'] ?? date('Y-m-d'),
        ':land_description' => $sampleData['land_description'],
        ':crop_recommendation' => $sampleData['crop_recommendation'],
        ':ph_value' => $sampleData['ph_value'],
        ':organic_matter' => $sampleData['organic_matter'],
        ':nitrogen_level' => $sampleData['nitrogen_level'],
        ':phosphorus_level' => $sampleData['phosphorus_level'],
        ':potassium_level' => $sampleData['potassium_level'],
        ':environmental_notes' => $sampleData['environmental_notes']
    ]);
    
    $reportId = $pdo->lastInsertId();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Land report created successfully after payment',
        'report_id' => $reportId,
        'land_id' => $landId,
        'user_id' => $userId,
        'location' => $paidLand['location']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error creating land report: ' . $e->getMessage()
    ]);
}

function generateSampleReportData($location) {
    // Generate sample data based on location characteristics
    $isCoastal = stripos($location, 'western') !== false || stripos($location, 'southern') !== false;
    $isCentral = stripos($location, 'central') !== false || stripos($location, 'kandy') !== false;
    $isNorth = stripos($location, 'north') !== false || stripos($location, 'anuradhapura') !== false;
    
    // Set pH based on region
    if ($isCentral) {
        $ph = number_format(rand(55, 65) / 10, 1); // 5.5-6.5 (slightly acidic for tea regions)
    } elseif ($isCoastal) {
        $ph = number_format(rand(60, 75) / 10, 1); // 6.0-7.5 (neutral to slightly alkaline)
    } else {
        $ph = number_format(rand(58, 72) / 10, 1); // 5.8-7.2 (moderate range)
    }
    
    $organicMatter = number_format(rand(20, 55) / 10, 2); // 2.0-5.5%
    
    $nutrientLevels = ['Low', 'Medium', 'High'];
    
    return [
        'land_description' => "Agricultural land in " . explode(',', $location)[0] . " with good potential for crop cultivation. The soil composition shows suitable characteristics for organic farming with proper management.",
        'crop_recommendation' => "Based on the location and soil conditions:\n\n1. Rice cultivation recommended\n2. Vegetable farming suitable\n3. Coconut cultivation possible\n4. Consider organic farming methods\n\nEstimated yield potential: Good to Excellent",
        'ph_value' => $ph,
        'organic_matter' => $organicMatter,
        'nitrogen_level' => $nutrientLevels[array_rand($nutrientLevels)],
        'phosphorus_level' => $nutrientLevels[array_rand($nutrientLevels)],
        'potassium_level' => $nutrientLevels[array_rand($nutrientLevels)],
        'environmental_notes' => "Land assessment completed for " . explode(',', $location)[0] . ". Environmental conditions are suitable for agricultural activities with proper soil management and water conservation practices."
    ];
}
?>