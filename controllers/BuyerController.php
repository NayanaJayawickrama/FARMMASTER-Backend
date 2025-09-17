<?php

require_once __DIR__ . '/../models/BuyerModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class BuyerController {
    private $model;
    private $uploadDir = 'uploads/';
    private $validCrops = ['Carrot', 'Leeks', 'Tomato', 'Cabbage'];
    private $validStatus = ['Available', 'Sold', 'Unavailable'];
    private $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

    public function __construct() {
        $this->model = new BuyerModel();
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function getDashboardData() {
    try {
        // Read JSON input instead of $_POST
        $input = json_decode(file_get_contents("php://input"), true);
        $userId = $input['userId'] ?? null;

        if (!$userId) {
            Response::error("Missing userId", 400);
            return;
        }

        // Call model methods
        $recentOrders     = $this->model->getAllRecentOrdersByUserId($userId);
        $purchaseHistory  = $this->model->getAllPurchaseHistoryByUserId($userId);
        $recentActivities  = $this->model->getRecentActivitiesByUserId($userId);

        // Build response
        $response = [
            'recent_orders'     => $recentOrders,
            'purchase_history'  => $purchaseHistory,
            'recent_activities' => $recentActivities
        ];

        Response::success("Buyer dashboard data retrieved successfully", $response);

    } catch (Exception $e) {
        Response::error("Internal error: " . $e->getMessage(), 500);
    }
}

    public function getOrders() {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            $userId = $input['userId'] ?? null;

            if (!$userId) {
                Response::error("Missing userId", 400);
                return;
            }

            $orders = $this->model->getAllOrdersWithItemsByUserId($userId);

            if (!$orders || count($orders) === 0) {
                Response::success("No orders found.", ['orders' => []]);
                return;
            }

            Response::success("Orders fetched successfully", ['orders' => $orders]);
        } catch (Exception $e) {
            Response::error("Internal error: " . $e->getMessage(), 500);
        }
    }

    private function getBaseHostUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "http" : "http";
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . '/FARMMASTER-Backend';
    }
}

?>