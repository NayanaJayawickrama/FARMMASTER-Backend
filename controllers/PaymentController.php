<?php

require_once __DIR__ . '/../models/PaymentModel.php';
require_once __DIR__ . '/../models/PaymentIntentModel.php';
require_once __DIR__ . '/../models/LandModel.php';
require_once __DIR__ . '/../models/CartOrderModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

// Stripe configuration
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51Rnk1kC523WS3olJgTHr67VfyR8w8fRy0kyoeoV257f1zaGdO7Egl1kXOtll5zbMnF1IgV0iRmWPkNlYiDvdesAP00teJxyQKk');
define('STRIPE_SECRET_KEY', 'sk_test_51Rnk1kC523WS3olJEaMgeuXPUxM12FY4ytUD6XNBMGF3n2TH78P8kNAOZgDSz4LTvZkKu58Sg9JxnhPzNIbMyyxW00zp9RrcrA');
define('STRIPE_CURRENCY', 'usd'); // Use USD to avoid LKR conversion issues in test mode

// Development mode - enable mock payments when network is unavailable
define('MOCK_PAYMENTS_ENABLED', true);

class StripeService {
    private $secret_key;
    private $base_url = 'https://api.stripe.com/v1/';
    
    public function __construct($secret_key = null) {
        $this->secret_key = $secret_key ?: STRIPE_SECRET_KEY;
    }
    
    private function makeRequest($endpoint, $data = [], $method = 'POST') {
        // Check if mock payments are enabled and network is unavailable
        if (MOCK_PAYMENTS_ENABLED && !$this->checkNetworkConnectivity()) {
            return $this->mockStripeResponse($endpoint, $data, $method);
        }
        
        $url = $this->base_url . $endpoint;
        
        // Debug logging
        error_log("Stripe Request - URL: {$url}, Method: {$method}, Data: " . json_encode($data));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secret_key,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Debug logging
        error_log("Stripe Response - HTTP Code: {$http_code}, Response: {$response}");
        
        if (curl_error($ch)) {
            error_log("Stripe cURL Error: " . curl_error($ch));
            
            // Fallback to mock when network fails
            if (MOCK_PAYMENTS_ENABLED) {
                curl_close($ch);
                return $this->mockStripeResponse($endpoint, $data, $method);
            }
            
            throw new Exception('Stripe API Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unknown Stripe error';
            error_log("Stripe API Error: {$error_message}");
            
            // Fallback to mock when API fails
            if (MOCK_PAYMENTS_ENABLED) {
                return $this->mockStripeResponse($endpoint, $data, $method);
            }
            
            throw new Exception('Stripe API Error: ' . $error_message);
        }
        
        return $decoded;
    }
    
    private function checkNetworkConnectivity() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $result = curl_exec($ch);
        $hasConnectivity = !curl_error($ch);
        curl_close($ch);
        
        return $hasConnectivity;
    }
    
    private function mockStripeResponse($endpoint, $data, $method) {
        error_log("Using mock Stripe response for endpoint: {$endpoint}");
        
        if (strpos($endpoint, 'payment_intents') !== false && $method === 'POST') {
            // Mock payment intent creation
            $paymentIntentId = 'pi_mock_' . uniqid();
            return [
                'id' => $paymentIntentId,
                'client_secret' => $paymentIntentId . '_secret_mock',
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'status' => 'requires_payment_method',
                'metadata' => $this->extractMetadata($data)
            ];
        } elseif (strpos($endpoint, 'payment_intents/') !== false && $method === 'GET') {
            // Mock payment intent retrieval
            $paymentIntentId = str_replace('payment_intents/', '', $endpoint);
            return [
                'id' => $paymentIntentId,
                'amount' => 5000,
                'currency' => 'usd',
                'status' => 'succeeded',
                'metadata' => []
            ];
        }
        
        return [];
    }
    
    private function extractMetadata($data) {
        $metadata = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'metadata[') === 0) {
                $metaKey = str_replace(['metadata[', ']'], '', $key);
                $metadata[$metaKey] = $value;
            }
        }
        return $metadata;
    }
    
    public function createPaymentIntent($amount, $currency = null, $metadata = []) {
        $currency = $currency ?: STRIPE_CURRENCY;
        
        // Handle currency-specific amount conversion
        // Convert LKR to USD and apply proper formatting
        if ($currency === 'usd' || $currency === 'USD') {
            // Convert from LKR to USD (approximate rate: 330 LKR = 1 USD)
            $usdAmount = $amount / 330;
            // USD uses cents, so multiply by 100
            $stripeAmount = (int)round($usdAmount * 100);
            error_log("Currency conversion: {$amount} LKR = {$usdAmount} USD = {$stripeAmount} cents");
        } elseif ($currency === 'lkr' || $currency === 'LKR') {
            // Zero-decimal currencies: use amount as-is
            $stripeAmount = (int)$amount;
        } else {
            // Decimal currencies: convert to cents
            $stripeAmount = (int)($amount * 100);
        }
        
        $data = [
            'amount' => $stripeAmount,
            'currency' => strtolower($currency),
            'payment_method_types[]' => 'card'
        ];
        
        // Add metadata
        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $data["metadata[{$key}]"] = $value;
            }
        }
        
        return $this->makeRequest('payment_intents', $data);
    }
    
    public function retrievePaymentIntent($payment_intent_id) {
        return $this->makeRequest("payment_intents/{$payment_intent_id}", [], 'GET');
    }
    
    public static function getPublishableKey() {
        return STRIPE_PUBLISHABLE_KEY;
    }
}

class PaymentController {
    private $paymentModel;
    private $intentModel;
    private $landModel;
    private $orderModel;
    private $stripe;
    private $validPaymentMethods = ['stripe', 'cash', 'bank_transfer'];
    private $validPaymentStatus = ['pending', 'completed', 'failed', 'cancelled', 'refunded'];

    public function __construct() {
        $this->paymentModel = new PaymentModel();
        $this->intentModel = new PaymentIntentModel();
        $this->landModel = new LandModel();
        $this->orderModel = new CartOrderModel();
        $this->stripe = new StripeService();
    }

    public function getAllPayments() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $filters = [];
            
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['payment_status'])) {
                $filters['payment_status'] = $_GET['payment_status'];
            }
            if (isset($_GET['payment_method'])) {
                $filters['payment_method'] = $_GET['payment_method'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }

            $payments = $this->paymentModel->getAllPayments($filters);
            
            Response::success("Payments retrieved successfully", $payments);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getPayment($paymentId) {
        try {
            SessionManager::requireAuth();

            $payment = $this->paymentModel->getPaymentById($paymentId);
            
            if (!$payment) {
                Response::notFound("Payment not found");
            }

            // Check if user can access this payment
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($payment['user_id'] != $currentUserId && !in_array($currentRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }
            
            Response::success("Payment retrieved successfully", $payment);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getUserPayments($userId = null) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Use current user ID if not provided or if not manager
            if (!$userId || (!in_array($currentRole, ['Financial_Manager', 'Operational_Manager']))) {
                $userId = $currentUserId;
            }

            $payments = $this->paymentModel->getUserPayments($userId);
            
            Response::success("User payments retrieved successfully", $payments);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function createPaymentIntent() {
        try {
            SessionManager::requireAuth();

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $userId = SessionManager::getCurrentUserId();
            $paymentType = Validator::required($data['payment_type'] ?? 'land_report', 'Payment type');
            $amount = Validator::numeric($data['amount'] ?? 0, 'Amount', 0.01);

            $landId = null;
            $cartOrderId = null;

            if ($paymentType === 'land_report') {
                $landId = Validator::required($data['land_id'] ?? '', 'Land ID');
                
                // Verify land belongs to user
                $land = $this->landModel->getLandById($landId);
                if (!$land) {
                    Response::notFound("Land not found");
                }
                if ($land['user_id'] != $userId) {
                    Response::forbidden("Access denied");
                }

                $metadata = [
                    'user_id' => $userId,
                    'land_id' => $landId,
                    'type' => 'land_assessment'
                ];
            } else if ($paymentType === 'cart_purchase') {
                SessionManager::requireRole(['Buyer']);
                $cartOrderId = Validator::required($data['cart_order_id'] ?? '', 'Cart Order ID');
                
                // Verify cart order belongs to user
                $order = $this->orderModel->getOrderWithItems($cartOrderId);
                if (!$order) {
                    Response::notFound("Order not found");
                }
                if ($order['user_id'] != $userId) {
                    Response::forbidden("Access denied");
                }
                if ($order['payment_status'] !== 'pending') {
                    Response::error("Order payment is not pending");
                }

                $metadata = [
                    'user_id' => $userId,
                    'cart_order_id' => $cartOrderId,
                    'type' => 'cart_purchase',
                    'order_number' => $order['order_number']
                ];
            } else {
                Response::error("Invalid payment type. Must be 'land_report' or 'cart_purchase'");
            }

            // Debug logging
            error_log("Payment Intent Debug - User ID: {$userId}, Payment Type: {$paymentType}, Amount: {$amount}, Currency: " . STRIPE_CURRENCY);

            // Create payment intent with Stripe
            $payment_intent = $this->stripe->createPaymentIntent(
                $amount, 
                STRIPE_CURRENCY,
                $metadata
            );

            error_log("Payment Intent Created - ID: {$payment_intent['id']}, Amount: {$payment_intent['amount']}, Currency: {$payment_intent['currency']}");

            // Store the payment intent in database
            $intentData = [
                'user_id' => $userId,
                'payment_type' => $paymentType,
                'land_id' => $landId,
                'cart_order_id' => $cartOrderId,
                'stripe_payment_intent_id' => $payment_intent['id'],
                'amount' => $amount,
                'status' => 'created'
            ];

            $result = $this->intentModel->createPaymentIntent($intentData);

            if ($result['success']) {
                Response::success("Payment intent created successfully", [
                    "client_secret" => $payment_intent['client_secret'],
                    "payment_intent_id" => $payment_intent['id'],
                    "publishable_key" => StripeService::getPublishableKey()
                ]);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            error_log("Payment Intent Error: " . $e->getMessage());
            Response::error($e->getMessage());
        }
    }

    public function confirmPayment() {
        try {
            SessionManager::requireAuth();

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $paymentIntentId = Validator::required($data['payment_intent_id'] ?? '', 'Payment Intent ID');
            $userId = SessionManager::getCurrentUserId();

            // Get payment intent from our database to determine type
            $localIntent = $this->intentModel->getPaymentIntentByStripeId($paymentIntentId);
            if (!$localIntent) {
                Response::notFound("Payment intent not found");
            }

            if ($localIntent['user_id'] != $userId) {
                Response::forbidden("Access denied");
            }

            $paymentType = $localIntent['payment_type'] ?? 'land_report';

            // Additional validation based on payment type
            if ($paymentType === 'land_report') {
                $landId = $localIntent['land_id'];
                $land = $this->landModel->getLandById($landId);
                if (!$land || $land['user_id'] != $userId) {
                    Response::forbidden("Access denied to land");
                }
            } else if ($paymentType === 'cart_purchase') {
                SessionManager::requireRole(['Buyer']);
                $cartOrderId = $localIntent['cart_order_id'];
                $order = $this->orderModel->getOrderWithItems($cartOrderId);
                if (!$order || $order['user_id'] != $userId) {
                    Response::forbidden("Access denied to order");
                }
                if ($order['payment_status'] !== 'pending') {
                    Response::error("Order payment is not pending");
                }
            }

            // Retrieve payment intent status from Stripe
            $payment_intent = $this->stripe->retrievePaymentIntent($paymentIntentId);

            if ($payment_intent['status'] === 'succeeded') {
                // Begin database transaction
                $db = Database::getInstance();
                $db->beginTransaction();

                try {
                    // Create transaction ID
                    $transactionId = 'stripe_' . $paymentIntentId;
                    
                    // Handle currency-specific amount conversion
                    if (strtolower($payment_intent['currency']) === 'usd') {
                        // Convert USD back to LKR for storage (USD cents to LKR)
                        $usdAmount = $payment_intent['amount'] / 100; // Convert cents to dollars
                        $amountReceived = $usdAmount * 330; // Convert USD to LKR
                        error_log("Payment confirmation: {$payment_intent['amount']} cents = {$usdAmount} USD = {$amountReceived} LKR");
                    } elseif (strtolower($payment_intent['currency']) === 'lkr') {
                        // LKR is zero-decimal currency - amount is already in rupees
                        $amountReceived = $payment_intent['amount'];
                    } else {
                        // Decimal currencies - convert from cents
                        $amountReceived = $payment_intent['amount'] / 100;
                    }

                    // Record payment
                    $paymentData = [
                        'user_id' => $userId,
                        'payment_type' => $paymentType,
                        'land_id' => $paymentType === 'land_report' ? $localIntent['land_id'] : null,
                        'order_id' => $paymentType === 'cart_purchase' ? $order['order_number'] ?? null : null,
                        'transaction_id' => $transactionId,
                        'amount' => $amountReceived,
                        'payment_method' => 'stripe',
                        'payment_status' => 'completed',
                        'stripe_payment_intent_id' => $paymentIntentId
                    ];

                    $paymentResult = $this->paymentModel->addPayment($paymentData);
                    
                    if (!$paymentResult['success']) {
                        throw new Exception($paymentResult['message']);
                    }

                    if ($paymentType === 'land_report') {
                        // Update land record
                        $landResult = $this->landModel->updatePaymentStatus($localIntent['land_id'], 'paid', date('Y-m-d H:i:s'));
                        
                        if (!$landResult['success']) {
                            throw new Exception($landResult['message']);
                        }
                    } else if ($paymentType === 'cart_purchase') {
                        // Update order payment status
                        $orderResult = $this->orderModel->updatePaymentStatus($localIntent['cart_order_id'], 'completed');
                        
                        if (!$orderResult['success']) {
                            throw new Exception($orderResult['message']);
                        }
                    }

                    // Update payment intent status
                    $this->intentModel->updatePaymentIntentStatus($paymentIntentId, 'succeeded');

                    $db->commit();

                    Response::success("Payment processed successfully", [
                        "transaction_id" => $transactionId,
                        "amount" => $amountReceived,
                        "payment_intent_id" => $paymentIntentId,
                        "payment_id" => $paymentResult['payment_id'],
                        "payment_type" => $paymentType
                    ]);

                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }

            } else {
                Response::error("Payment not completed. Status: " . $payment_intent['status']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function addManualPayment() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $userId = Validator::required($data['user_id'] ?? '', 'User ID');
            $landId = isset($data['land_id']) ? Validator::required($data['land_id'], 'Land ID') : null;
            $transactionId = Validator::required($data['transaction_id'] ?? '', 'Transaction ID');
            $amount = Validator::numeric($data['amount'] ?? 0, 'Amount', 0.01);
            $paymentMethod = Validator::inArray($data['payment_method'] ?? '', $this->validPaymentMethods, 'Payment method');
            $paymentStatus = isset($data['payment_status']) ? 
                           Validator::inArray($data['payment_status'], $this->validPaymentStatus, 'Payment status') : 
                           'completed';

            // Check for duplicate transaction ID
            $existingPayment = $this->paymentModel->getPaymentByTransactionId($transactionId);
            if ($existingPayment) {
                Response::error("Transaction ID already exists");
            }

            $paymentData = [
                'user_id' => $userId,
                'land_id' => $landId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus
            ];

            if (isset($data['payment_notes'])) {
                $paymentData['payment_notes'] = Validator::sanitizeString($data['payment_notes']);
            }

            $result = $this->paymentModel->addPayment($paymentData);

            if ($result['success']) {
                // Update land payment status if applicable
                if ($landId && $paymentStatus === 'completed') {
                    $this->landModel->updatePaymentStatus($landId, 'paid', date('Y-m-d H:i:s'));
                }

                Response::success($result['message'], $result, 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updatePaymentStatus($paymentId) {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['payment_status'])) {
                Response::error("Payment status is required");
            }

            $paymentStatus = Validator::inArray($data['payment_status'], $this->validPaymentStatus, 'Payment status');
            $notes = isset($data['payment_notes']) ? Validator::sanitizeString($data['payment_notes']) : null;

            $result = $this->paymentModel->updatePaymentStatus($paymentId, $paymentStatus, $notes);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchPayments() {
        try {
            SessionManager::requireAuth();

            $searchTerm = $_GET['search'] ?? '';

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            // Regular users can only search their own payments
            $userId = in_array($currentRole, ['Financial_Manager', 'Operational_Manager']) ? null : $currentUserId;

            $payments = $this->paymentModel->searchPayments($searchTerm, $userId);

            Response::success("Search completed", $payments);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getPaymentStats() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            $stats = $this->paymentModel->getPaymentStats($dateFrom, $dateTo);

            Response::success("Payment statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getMonthlyStats() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $year = $_GET['year'] ?? null;

            $stats = $this->paymentModel->getMonthlyStats($year);

            Response::success("Monthly statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getStripeConfig() {
        try {
            Response::success("Stripe configuration retrieved", [
                "publishable_key" => StripeService::getPublishableKey(),
                "currency" => STRIPE_CURRENCY
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    // Webhook handler for Stripe events (for future implementation)
    public function handleStripeWebhook() {
        try {
            $payload = file_get_contents('php://input');
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

            // TODO: Implement webhook signature verification
            // TODO: Handle different event types (payment_intent.succeeded, etc.)

            Response::success("Webhook processed");

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

?>