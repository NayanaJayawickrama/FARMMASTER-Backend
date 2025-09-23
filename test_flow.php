<?php
// Test the complete role switching flow
$baseUrl = 'http://localhost/FARMMASTER-Backend';

// Login data
$loginData = [
    'email' => 'lo@gmail.com',
    'password' => '123456'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, ''); 
curl_setopt($ch, CURLOPT_COOKIEJAR, '');

// Step 1: Login
echo "=== STEP 1: LOGIN ===\n";
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$data = json_decode($response, true);
echo "Current role: " . $data['data']['user_role'] . "\n\n";

// Step 2: Check available roles
echo "=== STEP 2: AVAILABLE ROLES ===\n";
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/available-roles');
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);

$response = curl_exec($ch);
$data = json_decode($response, true);
echo "Available roles: " . implode(', ', $data['data']) . "\n\n";

// Step 3: Switch to Landowner
echo "=== STEP 3: SWITCH TO LANDOWNER ===\n";
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/switch-role');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['role' => 'Landowner']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$data = json_decode($response, true);
echo "Switched to: " . $data['data']['role'] . "\n\n";

// Step 4: Check session after switch
echo "=== STEP 4: CHECK SESSION ===\n";
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/session');
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);

$response = curl_exec($ch);
$data = json_decode($response, true);
echo "Current session role: " . $data['data']['user_data']['user_role'] . "\n";
echo "Current active role: " . ($data['data']['user_data']['current_active_role'] ?? 'none') . "\n\n";

// Step 5: Switch back to Buyer
echo "=== STEP 5: SWITCH TO BUYER ===\n";
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/switch-role');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['role' => 'Buyer']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$data = json_decode($response, true);
echo "Switched to: " . $data['data']['role'] . "\n\n";

// Step 6: Reset role
echo "=== STEP 6: RESET ROLE ===\n";
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/reset-role');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$data = json_decode($response, true);
echo "Reset to: " . $data['data']['role'] . "\n\n";

// Step 7: Final session check
echo "=== STEP 7: FINAL SESSION CHECK ===\n";
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api.php/users/session');
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);

$response = curl_exec($ch);
$data = json_decode($response, true);
echo "Final session role: " . $data['data']['user_data']['user_role'] . "\n";
echo "Final active role: " . ($data['data']['user_data']['current_active_role'] ?? 'none') . "\n";

curl_close($ch);
?>