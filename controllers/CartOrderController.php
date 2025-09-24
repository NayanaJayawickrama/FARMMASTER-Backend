<?php

require_once __DIR__ . '/../models/CartOrderModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class CartOrderController {
    private $model;

    public function __construct() {
        $this->model = new CartOrderModel();
    }

    public function createOrder() {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input) {
                Response::error("Invalid JSON data", 400);
                return;
            }

            $userId = $input['user_id'] ?? null;
            $cartItems = $input['cart_items'] ?? [];
            $shippingAddress = $input['shipping_address'] ?? '';
            $orderNotes = $input['order_notes'] ?? null;

            if (!$userId || empty($cartItems) || !$shippingAddress) {
                Response::error("Missing required fields", 400);
                return;
            }

            // Validate cart items have required fields
            foreach ($cartItems as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    Response::error("Invalid cart item format", 400);
                    return;
                }
            }

            // Get product details and validate quantities
            $orderItemsData = [];
            $totalAmount = 0;

            foreach ($cartItems as $cartItem) {
                $productId = $cartItem['product_id'];
                $quantity = (int)$cartItem['quantity'];

                // Get product details
                $product = $this->model->getProductDetails($productId);
                if (!$product) {
                    Response::error("Product not found: " . $productId, 404);
                    return;
                }

                // Check if enough quantity is available
                if ($quantity > $product['quantity']) {
                    Response::error("Insufficient stock for " . $product['crop_name'] . ". Available: " . $product['quantity'] . " Kg", 400);
                    return;
                }

                $unitPrice = (float)$product['price_per_unit'];
                $totalPrice = $unitPrice * $quantity;
                $totalAmount += $totalPrice;

                $orderItemsData[] = [
                    'product_id' => $productId,
                    'product_name' => $product['crop_name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'product_image' => $product['image_url']
                ];
            }

            // Add shipping cost
            $shippingCost = $totalAmount > 0 ? 250.00 : 0.00;
            $totalAmount += $shippingCost;

            // Generate order number
            $orderNumber = 'ORD' . time() . rand(100, 999);

            $orderData = [
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'order_status' => 'confirmed',
                'payment_status' => 'pending',
                'shipping_address' => $shippingAddress,
                'order_notes' => $orderNotes
            ];

            $result = $this->model->createOrderWithItems($orderData, $orderItemsData);

            if ($result['success']) {
                // Update inventory quantities after successful order creation
                foreach ($cartItems as $cartItem) {
                    $this->updateInventoryQuantity($cartItem['product_id'], $cartItem['quantity']);
                }
                
                Response::success($result['message'], [
                    'order_id' => $result['order_id'],
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount
                ], 201);
            } else {
                Response::error($result['message'], 400);
            }

        } catch (Exception $e) {
            error_log("Create order error: " . $e->getMessage());
            Response::error($e->getMessage(), 500);
        }
    }

    private function updateInventoryQuantity($productId, $purchasedQuantity) {
        try {
            // Get current product details
            $product = $this->model->getProductDetails($productId);
            if (!$product) {
                error_log("Product not found for inventory update: " . $productId);
                return;
            }

            $newQuantity = max(0, $product['quantity'] - $purchasedQuantity);
            
            // Update crop_inventory table
            $sql = "UPDATE crop_inventory ci 
                    JOIN product p ON ci.crop_id = p.crop_id 
                    SET ci.quantity = :new_quantity,
                        ci.status = CASE 
                            WHEN :new_quantity <= 0 THEN 'Sold' 
                            ELSE ci.status 
                        END,
                        ci.updated_at = NOW()
                    WHERE p.product_id = :product_id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':new_quantity' => $newQuantity,
                ':product_id' => $productId
            ]);

            if ($result) {
                error_log("Updated inventory for product {$productId}: {$product['quantity']} -> {$newQuantity}");
            } else {
                error_log("Failed to update inventory for product {$productId}");
            }
        } catch (Exception $e) {
            error_log("Error updating inventory: " . $e->getMessage());
        }
    }
}

?>