<?php
require_once __DIR__ . '/controllers/LandReportController.php';

// Test the crop recommendation system
$controller = new LandReportController();

try {
    echo "Testing crop recommendations for report ID 41...\n";
    
    // Call the method directly
    $controller->generateCropRecommendations(41);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>