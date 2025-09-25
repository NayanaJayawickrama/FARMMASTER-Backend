<?php

require_once __DIR__ . '/../config/Database.php';

class BuyerModel extends BaseModel {
    protected $table = 'cart_orders';

    public function __construct() {
        parent::__construct();
    }

    // Get unique recent orders (no duplicates)
    public function getAllRecentOrdersByUserId($userId) {
        $sql = "SELECT DISTINCT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        $params = [':user_id' => $userId];
        return $this->executeQuery($sql, $params);
    }

    // Remove duplicate method - use getAllRecentOrdersByUserId instead
    public function getAllPurchaseHistoryByUserId($userId) {
        // This should return empty to avoid duplicates since frontend combines them
        return [];
    }

    public function getRecentActivitiesByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
        $params = [':user_id' => $userId];
        return $this->executeQuery($sql, $params);
    }

    // Add method to get total order count
    public function getTotalOrderCountByUserId($userId) {
        $sql = "SELECT COUNT(DISTINCT id) as total_orders FROM {$this->table} WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        $result = $this->executeQuery($sql, $params);
        return $result[0]['total_orders'] ?? 0;
    }

    // Add method to get total spending
    public function getTotalSpendingByUserId($userId) {
        $sql = "SELECT SUM(total_amount) as total_spending FROM {$this->table} WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        $result = $this->executeQuery($sql, $params);
        return $result[0]['total_spending'] ?? 0;
    }

    public function getAllOrdersWithItemsByUserId($userId) {
        $sql = "SELECT o.id, o.order_number, o.created_at as date, o.total_amount as total,
                       IFNULL(GROUP_CONCAT(i.product_name SEPARATOR ', '), 'No products') as product
                FROM cart_orders o
                LEFT JOIN cart_order_items i ON o.id = i.cart_order_id
                WHERE o.user_id = :user_id
                GROUP BY o.id
                ORDER BY o.created_at DESC LIMIT 30";
        $params = [':user_id' => $userId];
        return $this->executeQuery($sql, $params, true);
    }
}

?>