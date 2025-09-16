<?php
require_once __DIR__ . '/../config/Database.php';

class CartOrderModel extends BaseModel {
    protected $table = 'cart_orders';
    protected $itemsTable = 'cart_order_items';

    public function __construct() {
        parent::__construct();
    }

    public function createOrderWithItems($orderData, $orderItems) {
        try {
            $this->db->beginTransaction();

            // Insert order
            $data = [
                'user_id' => $orderData['user_id'],
                'order_number' => $orderData['order_number'],
                'total_amount' => $orderData['total_amount'],
                'order_status' => $orderData['order_status'] ?? 'pending',
                'payment_status' => $orderData['payment_status'] ?? 'pending',
                'shipping_address' => $orderData['shipping_address'],
                'order_notes' => $orderData['order_notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $orderId = $this->create($data);
            if (!$orderId) {
                throw new Exception("Failed to create order");
            }

            // Insert order items
            foreach ($orderItems as $item) {
                $itemData = [
                    'cart_order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'product_image' => $item['product_image'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $sql = "INSERT INTO {$this->itemsTable} (cart_order_id, product_id, product_name, quantity, unit_price, total_price, product_image, created_at) 
                        VALUES (:cart_order_id, :product_id, :product_name, :quantity, :unit_price, :total_price, :product_image, :created_at)";
                
                $stmt = $this->db->prepare($sql);
                if (!$stmt->execute($itemData)) {
                    throw new Exception("Failed to create order item");
                }
            }

            $this->db->commit();
            return [
                "success" => true,
                "message" => "Order created successfully",
                "order_id" => $orderId
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getOrderWithItems($orderId) {
        try {
            // Get order details
            $sql = "SELECT co.*, u.first_name, u.last_name, u.email 
                    FROM {$this->table} co 
                    LEFT JOIN user u ON co.user_id = u.user_id 
                    WHERE co.id = :order_id";
            
            $order = $this->executeQuery($sql, [':order_id' => $orderId]);
            if (!$order) {
                return null;
            }
            $order = $order[0];

            // Get order items
            $itemsSql = "SELECT * FROM {$this->itemsTable} WHERE cart_order_id = :order_id ORDER BY created_at";
            $items = $this->executeQuery($itemsSql, [':order_id' => $orderId]);

            $order['items'] = $items ?: [];
            $order['total_items'] = count($items ?: []);

            return $order;
        } catch (Exception $e) {
            error_log("Error getting order with items: " . $e->getMessage());
            return null;
        }
    }

    public function getUserOrders($userId) {
        try {
            $sql = "SELECT co.*, 
                           COUNT(coi.id) as total_items
                    FROM {$this->table} co 
                    LEFT JOIN {$this->itemsTable} coi ON co.id = coi.cart_order_id
                    WHERE co.user_id = :user_id 
                    GROUP BY co.id
                    ORDER BY co.created_at DESC";
            
            return $this->executeQuery($sql, [':user_id' => $userId]) ?: [];
        } catch (Exception $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            return [];
        }
    }

    public function getAllOrders($filters = []) {
        try {
            $conditions = [];
            $params = [];

            if (isset($filters['order_status']) && !empty($filters['order_status'])) {
                $conditions[] = 'co.order_status = :order_status';
                $params[':order_status'] = $filters['order_status'];
            }

            if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
                $conditions[] = 'co.payment_status = :payment_status';
                $params[':payment_status'] = $filters['payment_status'];
            }

            if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                $conditions[] = 'DATE(co.created_at) >= :date_from';
                $params[':date_from'] = $filters['date_from'];
            }

            if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                $conditions[] = 'DATE(co.created_at) <= :date_to';
                $params[':date_to'] = $filters['date_to'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $sql = "SELECT co.*, 
                           u.first_name, u.last_name, u.email,
                           COUNT(coi.id) as total_items
                    FROM {$this->table} co 
                    LEFT JOIN user u ON co.user_id = u.user_id
                    LEFT JOIN {$this->itemsTable} coi ON co.id = coi.cart_order_id
                    {$whereClause}
                    GROUP BY co.id
                    ORDER BY co.created_at DESC";

            return $this->executeQuery($sql, $params) ?: [];
        } catch (Exception $e) {
            error_log("Error getting all orders: " . $e->getMessage());
            return [];
        }
    }

    public function updateOrderStatus($orderId, $status, $notes = null) {
        try {
            $data = ['order_status' => $status];
            if ($notes) {
                $data['order_notes'] = $notes;
            }
            $data['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->update($orderId, $data);
            if ($result) {
                return ["success" => true, "message" => "Order status updated successfully"];
            } else {
                return ["success" => false, "message" => "Order not found"];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updatePaymentStatus($orderId, $paymentStatus) {
        try {
            $data = [
                'payment_status' => $paymentStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->update($orderId, $data);
            if ($result) {
                return ["success" => true, "message" => "Payment status updated successfully"];
            } else {
                return ["success" => false, "message" => "Order not found"];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getOrderByNumber($orderNumber) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE order_number = :order_number";
            $result = $this->executeQuery($sql, [':order_number' => $orderNumber]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error getting order by number: " . $e->getMessage());
            return null;
        }
    }
}