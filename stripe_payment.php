<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "database.php";
include "stripe.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received."]);
    exit();
}

$action = $data['action'] ?? '';

try {
    // Initialize Stripe API
    $stripe = new FarmMasterStripe();
    
    switch ($action) {
        case 'create_payment_intent':
            $user_id = intval($data['user_id'] ?? 0);
            $land_id = intval($data['land_id'] ?? 0);
            $amount = floatval($data['amount'] ?? 0);
            
            if (!$user_id || !$land_id || !$amount) {
                throw new Exception("Missing required fields for payment intent creation");
            }
            
            // Create payment intent using real Stripe API
            $payment_intent = $stripe->createPaymentIntent(
                $amount, 
                STRIPE_CURRENCY,
                [
                    'user_id' => $user_id,
                    'land_id' => $land_id,
                    'type' => 'land_assessment'
                ]
            );
            
            // Store the payment intent in database for tracking
            $stmt = $conn->prepare("
                INSERT INTO payment_intents (user_id, land_id, stripe_payment_intent_id, amount, status, created_at) 
                VALUES (?, ?, ?, ?, 'created', NOW())
            ");
            $stmt->bind_param("iisi", $user_id, $land_id, $payment_intent['id'], $amount);
            $stmt->execute();
            
            echo json_encode([
                "status" => "success",
                "client_secret" => $payment_intent['client_secret'],
                "payment_intent_id" => $payment_intent['id']
            ]);
            break;
            
        case 'confirm_payment':
            $payment_intent_id = $data['payment_intent_id'] ?? '';
            $user_id = intval($data['user_id'] ?? 0);
            $land_id = intval($data['land_id'] ?? 0);
            
            if (!$payment_intent_id || !$user_id || !$land_id) {
                throw new Exception("Missing required fields for payment confirmation");
            }
            
            // Retrieve payment intent status from Stripe
            $payment_intent = $stripe->retrievePaymentIntent($payment_intent_id);
            
            if ($payment_intent['status'] === 'succeeded') {
                // Start database transaction
                $conn->autocommit(false);
                
                // Create transaction ID
                $transaction_id = 'stripe_' . $payment_intent_id;
                
                // Insert payment record
                $stmt = $conn->prepare("
                    INSERT INTO payments (user_id, land_id, transaction_id, amount, payment_method, payment_status, stripe_payment_intent_id, created_at) 
                    VALUES (?, ?, ?, ?, 'stripe', 'completed', ?, NOW())
                ");
                $amount_received = $payment_intent['amount'] / 100; // Convert from cents
                $stmt->bind_param("iisds", $user_id, $land_id, $transaction_id, $amount_received, $payment_intent_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to record payment: " . $stmt->error);
                }
                
                // Update land record
                $stmt = $conn->prepare("UPDATE land SET payment_status = 'paid', payment_date = NOW() WHERE land_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $land_id, $user_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update land record: " . $stmt->error);
                }
                
                // Update payment intent status
                $stmt = $conn->prepare("UPDATE payment_intents SET status = 'succeeded' WHERE stripe_payment_intent_id = ?");
                $stmt->bind_param("s", $payment_intent_id);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                echo json_encode([
                    "status" => "success",
                    "message" => "Payment processed successfully with Stripe",
                    "transaction_id" => $transaction_id,
                    "amount" => $amount_received,
                    "payment_intent_id" => $payment_intent_id
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Payment not completed. Status: " . $payment_intent['status']
                ]);
            }
            break;
            
        default:
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
    }
    
} catch (Exception $e) {
    // Rollback transaction if started
    if (!$conn->autocommit(NULL)) {
        $conn->rollback();
    }
    
    echo json_encode([
        "status" => "error",
        "message" => "Stripe payment error: " . $e->getMessage()
    ]);
} finally {
    $conn->autocommit(true);
}

$conn->close();
?>
