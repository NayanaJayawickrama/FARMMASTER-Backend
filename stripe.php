<?php
// ==============================================
// FARMMASTER STRIPE INTEGRATION
// Combined configuration and API wrapper
// ==============================================

// ============ STRIPE CONFIGURATION ============
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51Rnk1kC523WS3olJgTHr67VfyR8w8fRy0kyoeoV257f1zaGdO7Egl1kXOtll5zbMnF1IgV0iRmWPkNlYiDvdesAP00teJxyQKk');
define('STRIPE_SECRET_KEY', 'sk_test_51Rnk1kC523WS3olJEaMgeuXPUxM12FY4ytUD6XNBMGF3n2TH78P8kNAOZgDSz4LTvZkKu58Sg9JxnhPzNIbMyyxW00zp9RrcrA');
define('STRIPE_CURRENCY', 'lkr'); // Sri Lankan Rupee

// For production, use environment variables:
// define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY'));
// define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY'));

// ============ STRIPE API WRAPPER ============
class FarmMasterStripe {
    private $secret_key;
    private $base_url = 'https://api.stripe.com/v1/';
    
    public function __construct($secret_key = null) {
        $this->secret_key = $secret_key ?: STRIPE_SECRET_KEY;
    }
    
    /**
     * Make HTTP request to Stripe API
     */
    private function makeRequest($endpoint, $data = [], $method = 'POST') {
        $url = $this->base_url . $endpoint;
        
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
        
        if (curl_error($ch)) {
            throw new Exception('Stripe API Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unknown Stripe error';
            throw new Exception('Stripe API Error: ' . $error_message);
        }
        
        return $decoded;
    }
    
    /**
     * Create a payment intent
     */
    public function createPaymentIntent($amount, $currency = null, $metadata = []) {
        $currency = $currency ?: STRIPE_CURRENCY;
        
        $data = [
            'amount' => $amount * 100, // Convert to cents
            'currency' => $currency,
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
    
    /**
     * Retrieve payment intent details
     */
    public function retrievePaymentIntent($payment_intent_id) {
        return $this->makeRequest("payment_intents/{$payment_intent_id}", [], 'GET');
    }
    
    /**
     * Confirm a payment intent
     */
    public function confirmPaymentIntent($payment_intent_id, $payment_method_id) {
        $data = [
            'payment_method' => $payment_method_id
        ];
        
        return $this->makeRequest("payment_intents/{$payment_intent_id}/confirm", $data);
    }
    
    /**
     * Get publishable key for frontend
     */
    public static function getPublishableKey() {
        return STRIPE_PUBLISHABLE_KEY;
    }
    
    /**
     * Format amount for display (convert from cents)
     */
    public static function formatAmount($cents, $currency = 'LKR') {
        $amount = $cents / 100;
        return $currency . ' ' . number_format($amount, 2);
    }
}

// ============ HELPER FUNCTIONS ============

/**
 * Validate Stripe webhook signature (for future webhook integration)
 */
function verifyStripeWebhook($payload, $sig_header, $webhook_secret) {
    $elements = explode(',', $sig_header);
    $signatureData = [];
    
    foreach ($elements as $element) {
        list($key, $value) = explode('=', $element);
        if ($key === 'v1') {
            $signatureData['v1'] = $value;
        }
        if ($key === 't') {
            $signatureData['t'] = $value;
        }
    }
    
    if (!isset($signatureData['v1']) || !isset($signatureData['t'])) {
        return false;
    }
    
    $signed_payload = $signatureData['t'] . '.' . $payload;
    $expected_signature = hash_hmac('sha256', $signed_payload, $webhook_secret);
    
    return hash_equals($expected_signature, $signatureData['v1']);
}

/**
 * Log Stripe transaction (optional logging function)
 */
function logStripeTransaction($type, $data, $success = true) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'success' => $success,
        'data' => $data
    ];
    
    // You can implement logging to file or database here
    error_log('STRIPE_LOG: ' . json_encode($log_entry));
}
?>
