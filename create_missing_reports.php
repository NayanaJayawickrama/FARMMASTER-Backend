<?php
/**
 * Script to create missing land reports for paid land assessments
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
    
    // Get specific user ID if provided, otherwise default to 32
    $userId = $_GET['user_id'] ?? 32;
    
    echo "Processing land reports for user ID: $userId\n\n";
    
    // Find paid land requests that don't have corresponding land reports
    $sql = "SELECT l.land_id, l.user_id, l.location, l.size, l.payment_date 
            FROM land l 
            LEFT JOIN land_report lr ON l.land_id = lr.land_id 
            WHERE l.user_id = :user_id 
            AND l.payment_status = 'paid' 
            AND lr.land_id IS NULL
            ORDER BY l.land_id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $paidLandsWithoutReports = $stmt->fetchAll();
    
    if (empty($paidLandsWithoutReports)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'No missing land reports found. All paid lands already have reports.',
            'user_id' => $userId
        ]);
        exit();
    }
    
    echo "Found " . count($paidLandsWithoutReports) . " paid lands without reports:\n";
    
    $createdReports = [];
    
    foreach ($paidLandsWithoutReports as $land) {
        // Create a land report for this paid land
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
        
        // Generate sample data based on location
        $sampleData = generateSampleReportData($land['location']);
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':land_id' => $land['land_id'],
            ':user_id' => $land['user_id'],
            ':report_date' => $land['payment_date'] ?? date('Y-m-d'),
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
        
        $createdReports[] = [
            'report_id' => $reportId,
            'land_id' => $land['land_id'],
            'location' => $land['location'],
            'size' => $land['size']
        ];
        
        echo "Created report #$reportId for land #{$land['land_id']} - {$land['location']}\n";
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully created ' . count($createdReports) . ' land reports for paid assessments',
        'user_id' => $userId,
        'created_reports' => $createdReports
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
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