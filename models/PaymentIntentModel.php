<?php

require_once __DIR__ . '/../config/Database.php';

class PaymentIntentModel extends BaseModel {
    protected $table = 'payment_intents';

    public function __construct() {
        parent::__construct();
    }

    public function createPaymentIntent($intentData) {
        try {
            $data = [
                'user_id' => $intentData['user_id'],
                'land_id' => $intentData['land_id'] ?? null,
                'stripe_payment_intent_id' => $intentData['stripe_payment_intent_id'],
                'amount' => $intentData['amount'],
                'status' => $intentData['status'] ?? 'created',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $intentId = $this->create($data);
            
            if ($intentId) {
                return [
                    "success" => true, 
                    "message" => "Payment intent created successfully.", 
                    "intent_id" => $intentId
                ];
            } else {
                return ["success" => false, "message" => "Failed to create payment intent."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updatePaymentIntentStatus($stripeIntentId, $status) {
        try {
            $sql = "UPDATE {$this->table} SET status = :status WHERE stripe_payment_intent_id = :stripe_intent_id";
            $result = $this->executeStatement($sql, [
                ':status' => $status,
                ':stripe_intent_id' => $stripeIntentId
            ]);
            
            if ($result) {
                return ["success" => true, "message" => "Payment intent status updated successfully."];
            } else {
                return ["success" => false, "message" => "Payment intent not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getPaymentIntentByStripeId($stripeIntentId) {
        $sql = "SELECT * FROM {$this->table} WHERE stripe_payment_intent_id = :stripe_intent_id";
        $result = $this->executeQuery($sql, [':stripe_intent_id' => $stripeIntentId]);
        return $result ? $result[0] : null;
    }

    public function getUserPaymentIntents($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->executeQuery($sql, [':user_id' => $userId]);
    }
}

?>