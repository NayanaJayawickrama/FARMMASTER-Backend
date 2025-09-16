<?php
require_once __DIR__ . '/../models/CartOrderModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class OrderController {
    private $orderModel;
    private $productModel;
    private $validOrderStatus = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    private $validPaymentStatus = ['pending', 'completed', 'failed', 'refunded'];

    public function __construct() {
        $this->orderModel = new CartOrderModel();
        $this->productModel = new ProductModel();
    }

    public function createOrder() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Buyer']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $userId = SessionManager::getCurrentUserId();
            $cartItems = Validator::required($data['cart_items'] ?? [], 'Cart items');
            $shippingAddress = Validator::required($data['shipping_address'] ?? '', 'Shipping address');
            $orderNotes = isset($data['order_notes']) ? Validator::sanitizeString($data['order_notes']) : null;

            if (empty($cartItems) || !is_array($cartItems)) {
                Response::error("Cart items must be a non-empty array");
            }

            // Calculate total amount and validate items
            $totalAmount = 0;
            $validatedItems = [];

            foreach ($cartItems as $item) {
                // Validate required fields
                $productId = Validator::required($item['product_id'] ?? '', 'Product ID');
                $quantity = Validator::numeric($item['quantity'] ?? 0, 'Quantity', 0.01);

                // Get product details
                $product = $this->productModel->getProductById($productId);
                if (!$product) {
                    Response::error("Product with ID {$productId} not found");
                }

                if ($product['status'] !== 'Available') {
                    Response::error("Product {$product['crop_name']} is not available");
                }

                if ($quantity > $product['quantity']) {
                    Response::error("Insufficient stock for product {$product['crop_name']}. Available: {$product['quantity']}, Requested: {$quantity}");
                }

                $itemTotal = $product['price_per_unit'] * $quantity;
                $totalAmount += $itemTotal;

                $validatedItems[] = [
                    'product_id' => $productId,
                    'product_name' => $product['crop_name'],
                    'quantity' => $quantity,
                    'unit_price' => $product['price_per_unit'],
                    'total_price' => $itemTotal,
                    'product_image' => $product['image_url']
                ];
            }

            // Add shipping cost
            $shippingCost = $totalAmount > 0 ? 250.00 : 0;
            $totalAmount += $shippingCost;

            // Generate order number
            $orderNumber = 'ORD' . date('Ymd') . sprintf('%06d', rand(1, 999999));

            // Create order
            $orderData = [
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'shipping_address' => $shippingAddress,
                'order_notes' => $orderNotes,
                'order_status' => 'pending',
                'payment_status' => 'pending'
            ];

            $result = $this->orderModel->createOrderWithItems($orderData, $validatedItems);

            if ($result['success']) {
                Response::success($result['message'], [
                    'order_id' => $result['order_id'],
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount,
                    'shipping_cost' => $shippingCost
                ], 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getOrder($orderId) {
        try {
            SessionManager::requireAuth();

            $order = $this->orderModel->getOrderWithItems($orderId);
            if (!$order) {
                Response::notFound("Order not found");
            }

            $userId = SessionManager::getCurrentUserId();
            $userRole = SessionManager::getCurrentUserRole();

            // Check permission - buyers can only see their own orders, managers can see all
            if ($order['user_id'] != $userId && !in_array($userRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }

            Response::success("Order retrieved successfully", $order);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getUserOrders($userId) {
        try {
            SessionManager::requireAuth();

            $currentUserId = SessionManager::getCurrentUserId();
            $userRole = SessionManager::getCurrentUserRole();

            // Check permission
            if ($userId != $currentUserId && !in_array($userRole, ['Financial_Manager', 'Operational_Manager'])) {
                Response::forbidden("Access denied");
            }

            $orders = $this->orderModel->getUserOrders($userId);
            Response::success("Orders retrieved successfully", $orders);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getAllOrders() {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $filters = [];
            if (isset($_GET['status'])) {
                $filters['order_status'] = $_GET['status'];
            }
            if (isset($_GET['payment_status'])) {
                $filters['payment_status'] = $_GET['payment_status'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }

            $orders = $this->orderModel->getAllOrders($filters);
            Response::success("Orders retrieved successfully", $orders);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateOrderStatus($orderId) {
        try {
            SessionManager::requireRole(['Financial_Manager', 'Operational_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data || !isset($data['order_status'])) {
                Response::error("Order status is required");
            }

            $orderStatus = Validator::inArray($data['order_status'], $this->validOrderStatus, 'Order status');
            $notes = isset($data['order_notes']) ? Validator::sanitizeString($data['order_notes']) : null;

            $result = $this->orderModel->updateOrderStatus($orderId, $orderStatus, $notes);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}