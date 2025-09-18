<?php

require_once __DIR__ . '/../config/Database.php';

class BuyerModel extends BaseModel {
    protected $table = 'cart_orders';

    public function __construct() {
        parent::__construct();
    }

    public function getAllRecentOrdersByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND order_status IN ('pending','confirmed','processing','shipped') 
                ORDER BY created_at DESC";
        $params = [':user_id' => $userId];
        return $this->executeQuery($sql, $params);
    }

    public function getAllPurchaseHistoryByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND order_status = 'delivered' ORDER BY created_at DESC";
        $params = [':user_id' => $userId];
        return $this->executeQuery($sql, $params);
    }
    public function getRecentActivitiesByUserId($userId) {
        //increase the limit amount for more activities, if you want all activities remove the LIMIT clause
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
        $params = [':user_id' => $userId];
        return $this->executeQuery($sql, $params);
    }

    public function getAllOrdersWithItemsByUserId($userId) {
        $sql = "SELECT o.id, o.order_number, o.created_at as date, o.total_amount as total, o.order_status as status,
                       IFNULL(GROUP_CONCAT(i.product_name SEPARATOR ', '), 'No products') as product
                FROM cart_orders o
                LEFT JOIN cart_order_items i ON o.id = i.cart_order_id
                WHERE o.user_id = :user_id
                GROUP BY o.id
                ORDER BY o.created_at DESC";
        $params = [':user_id' => $userId];
        // Ensure associative array return
        return $this->executeQuery($sql, $params, true);
    }
}

?>