<?php
// Test crop recommendations with level-based nutrient data

require_once 'config/Database.php';
require_once 'controllers/LandReportController.php';

// Test report data
$reportData = [
    'report_id' => 41,
    'ph_value' => 6.8,
    'organic_matter' => 4.0,
    'nitrogen_level' => 'medium',
    'phosphorus_level' => 'low', 
    'potassium_level' => 'high',
    'environmental_notes' => 'Test with level-based nutrients'
];

echo "Testing Crop Recommendations with Level-Based Nutrient Data\n";
echo "============================================================\n\n";

echo "Report Data:\n";
echo "- pH Value: " . $reportData['ph_value'] . "\n";
echo "- Organic Matter: " . $reportData['organic_matter'] . "%\n";
echo "- Nitrogen Level: " . $reportData['nitrogen_level'] . "\n";
echo "- Phosphorus Level: " . $reportData['phosphorus_level'] . "\n";
echo "- Potassium Level: " . $reportData['potassium_level'] . "\n\n";

// Test the conversion function
$controller = new LandReportController();

// Use reflection to access private method
$reflectionClass = new ReflectionClass($controller);
$convertMethod = $reflectionClass->getMethod('convertLevelToNumeric');
$convertMethod->setAccessible(true);

echo "Level to Numeric Conversion:\n";
echo "- Low: " . $convertMethod->invoke($controller, 'low') . "\n";
echo "- Medium: " . $convertMethod->invoke($controller, 'medium') . "\n";
echo "- High: " . $convertMethod->invoke($controller, 'high') . "\n";
echo "- Invalid: " . $convertMethod->invoke($controller, 'invalid') . "\n\n";

// Test the crop recommendation logic
$recommendationMethod = $reflectionClass->getMethod('generateCropRecommendationLogic');
$recommendationMethod->setAccessible(true);

echo "Generating Crop Recommendations...\n";
$recommendations = $recommendationMethod->invoke($controller, $reportData);

echo "Top Recommended Crops:\n";
foreach ($recommendations['recommendations'] as $index => $crop) {
    echo ($index + 1) . ". " . $crop['crop'] . " (Score: " . $crop['score'] . "%)\n";
    echo "   Yield: " . $crop['yield_per_acre'] . "\n";
    echo "   Market Price: " . $crop['market_price'] . "\n";
    echo "   Reasons: " . implode(", ", $crop['reasons']) . "\n\n";
}

echo "Soil Improvements Needed:\n";
foreach ($recommendations['soil_improvements'] as $improvement) {
    echo "- " . $improvement . "\n";
}

echo "\nSoil Summary:\n";
foreach ($recommendations['soil_summary'] as $key => $value) {
    echo "- " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
}

echo "\nDetailed Analysis:\n";
echo $recommendations['detailed_text'] . "\n";