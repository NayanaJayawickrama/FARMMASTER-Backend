<?php
/**
 * Test script for Land Report Management endpoints
 * This script tests the new backend functionality for Operational Manager
 */

// Test configuration
$baseUrl = 'http://localhost/FARMMASTER-Backend/api.php';

// Test functions
function testEndpoint($url, $method = 'GET', $data = null) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ]
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Test endpoints
echo "=== Testing Land Report Management Endpoints ===\n\n";

// 1. Test getting assignment reports
echo "1. Testing GET /land-reports/assignments-public\n";
$result = testEndpoint($baseUrl . '/land-reports/assignments-public');
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// 2. Test getting review reports
echo "2. Testing GET /land-reports/reviews-public\n";
$result = testEndpoint($baseUrl . '/land-reports/reviews-public');
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// 3. Test getting available supervisors
echo "3. Testing GET /land-reports/supervisors-public\n";
$result = testEndpoint($baseUrl . '/land-reports/supervisors-public');
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// 4. Test assigning supervisor (if there are reports)
echo "4. Testing PUT /land-reports/1/assign-public\n";
$assignData = [
    'supervisor_name' => 'Test Supervisor',
    'supervisor_id' => '31'
];
$result = testEndpoint($baseUrl . '/land-reports/1/assign-public', 'PUT', $assignData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// 5. Test submitting review
echo "5. Testing PUT /land-reports/1/review-public\n";
$reviewData = [
    'decision' => 'Approve',
    'feedback' => 'Test feedback from automated test'
];
$result = testEndpoint($baseUrl . '/land-reports/1/review-public', 'PUT', $reviewData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

echo "=== Test completed ===\n";

?>