<?php
// Test the role switching API with actual HTTP requests
$baseUrl = 'http://localhost/FARMMASTER-Backend';

// First, login as a user to get a valid session
$loginData = [
    'email' => 'lo@gmail.com',  // User 32
    'password' => '123456' // The correct password
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, ''); // Enable cookies
curl_setopt($ch, CURLOPT_COOKIEJAR, ''); // Store cookies

echo "Attempting login...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Login Response (HTTP $httpCode): $response\n\n";

if ($httpCode === 200) {
    // Try to get available roles
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/available-roles');
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    
    echo "Getting available roles...\n";
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Available Roles Response (HTTP $httpCode): $response\n\n";
    
    // Try to switch role
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/switch-role');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['role' => 'Landowner']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    echo "Switching to Landowner role...\n";
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Switch Role Response (HTTP $httpCode): $response\n\n";
}

curl_close($ch);
?>