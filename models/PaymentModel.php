<?php

require_once __DIR__ . '/../config/Database.php';

class PaymentModel extends BaseModel {
    protected $table = 'payments';

    public function __construct() {
        parent::__construct();
    }

    public function getAllPayments($filters = []) {
        $conditions = [];
        $params = [];

        if (isset($filters['user_id']) && !empty($filters['user_id'])) {
            $conditions[] = 'p.user_id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
            $conditions[] = 'p.payment_status = :payment_status';
            $params[':payment_status'] = $filters['payment_status'];
        }

        if (isset($filters['payment_method']) && !empty($filters['payment_method'])) {
            $conditions[] = 'p.payment_method = :payment_method';
            $params[':payment_method'] = $filters['payment_method'];
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $conditions[] = 'DATE(p.created_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $conditions[] = 'DATE(p.created_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        $sql = "SELECT 
                    p.*,
                    u.first_name,
                    u.last_name,
                    u.email,
                    l.location,
                    l.size
                FROM {$this->table} p 
                LEFT JOIN user u ON p.user_id = u.user_id
                LEFT JOIN land l ON p.land_id = l.land_id";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $this->executeQuery($sql, $params);
    }

    public function getPaymentById($paymentId) {
        $sql = "SELECT 
                    p.*,
                    u.first_name,
                    u.last_name,
                    u.email,
                    l.location,
                    l.size
                FROM {$this->table} p 
                LEFT JOIN user u ON p.user_id = u.user_id
                LEFT JOIN land l ON p.land_id = l.land_id
                WHERE p.payment_id = :payment_id";
        $result = $this->executeQuery($sql, [':payment_id' => $paymentId]);
        return $result ? $result[0] : null;
    }

    public function getUserPayments($userId) {
        return $this->getAllPayments(['user_id' => $userId]);
    }

    public function addPayment($paymentData) {
        try {
            $data = [
                'user_id' => $paymentData['user_id'],
                'payment_type' => $paymentData['payment_type'] ?? 'land_report',
                'land_id' => $paymentData['land_id'] ?? null,
                'order_id' => $paymentData['order_id'] ?? null,
                'transaction_id' => $paymentData['transaction_id'],
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'payment_status' => $paymentData['payment_status'] ?? 'pending',
                'stripe_payment_intent_id' => $paymentData['stripe_payment_intent_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (isset($paymentData['payment_notes'])) {
                $data['payment_notes'] = $paymentData['payment_notes'];
            }
            if (isset($paymentData['cart_items'])) {
                $data['cart_items'] = $paymentData['cart_items'];
            }
            if (isset($paymentData['shipping_address'])) {
                $data['shipping_address'] = $paymentData['shipping_address'];
            }
            if (isset($paymentData['total_items'])) {
                $data['total_items'] = $paymentData['total_items'];
            }

            $paymentId = $this->create($data);
            
            if ($paymentId) {
                return [
                    "success" => true, 
                    "message" => "Payment recorded successfully.", 
                    "payment_id" => $paymentId
                ];
            } else {
                return ["success" => false, "message" => "Failed to record payment."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updatePaymentStatus($paymentId, $status, $notes = null) {
        try {
            $data = ['payment_status' => $status];
            if ($notes) {
                $data['payment_notes'] = $notes;
            }

            $result = $this->update($paymentId, $data, 'payment_id');
            
            if ($result) {
                return ["success" => true, "message" => "Payment status updated successfully."];
            } else {
                return ["success" => false, "message" => "Payment not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getPaymentByTransactionId($transactionId) {
        $sql = "SELECT * FROM {$this->table} WHERE transaction_id = :transaction_id";
        $result = $this->executeQuery($sql, [':transaction_id' => $transactionId]);
        return $result ? $result[0] : null;
    }

    public function getPaymentByStripeIntentId($stripeIntentId) {
        $sql = "SELECT * FROM {$this->table} WHERE stripe_payment_intent_id = :stripe_intent_id";
        $result = $this->executeQuery($sql, [':stripe_intent_id' => $stripeIntentId]);
        return $result ? $result[0] : null;
    }

    public function getPaymentStats($dateFrom = null, $dateTo = null) {
        $conditions = [];
        $params = [];

        if ($dateFrom) {
            $conditions[] = 'DATE(created_at) >= :date_from';
            $params[':date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $conditions[] = 'DATE(created_at) <= :date_to';
            $params[':date_to'] = $dateTo;
        }

        $whereClause = !empty($conditions) ? " WHERE " . implode(' AND ', $conditions) : "";

        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
                    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                    SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed_payments,
                    SUM(CASE WHEN payment_method = 'stripe' THEN 1 ELSE 0 END) as stripe_payments,
                    SUM(CASE WHEN payment_method = 'cash' THEN 1 ELSE 0 END) as cash_payments,
                    AVG(amount) as average_amount
                FROM {$this->table}" . $whereClause;
        
        $result = $this->executeQuery($sql, $params);
        return $result ? $result[0] : [];
    }

    public function getMonthlyStats($year = null) {
        $year = $year ?: date('Y');
        
        $sql = "SELECT 
                    MONTH(created_at) as month,
                    MONTHNAME(created_at) as month_name,
                    COUNT(*) as total_payments,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as completed_amount
                FROM {$this->table} 
                WHERE YEAR(created_at) = :year
                GROUP BY MONTH(created_at)
                ORDER BY MONTH(created_at)";
        
        return $this->executeQuery($sql, [':year' => $year]);
    }

    public function searchPayments($searchTerm, $userId = null) {
        $conditions = ["(p.transaction_id LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search OR l.location LIKE :search)"];
        $params = [':search' => "%{$searchTerm}%"];

        if ($userId) {
            $conditions[] = "p.user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $sql = "SELECT 
                    p.*,
                    u.first_name,
                    u.last_name,
                    u.email,
                    l.location,
                    l.size
                FROM {$this->table} p 
                LEFT JOIN user u ON p.user_id = u.user_id
                LEFT JOIN land l ON p.land_id = l.land_id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY p.created_at DESC";

        return $this->executeQuery($sql, $params);
    }
}

?>