<?php

require_once __DIR__ . '/../models/PaymentModel.php';
require_once __DIR__ . '/../models/CartOrderModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class FinancialAnalyticsController {
    private $paymentModel;
    private $orderModel;
    private $productModel;
    private $db;

    public function __construct() {
        $this->paymentModel = new PaymentModel();
        $this->orderModel = new CartOrderModel();
        $this->productModel = new ProductModel();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get marketplace financial analytics
     */
    public function getMarketplaceAnalytics() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager']);

            // Get date range from query parameters (default to last 30 days)
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $period = $_GET['period'] ?? 'month'; // day, week, month, year

            // Validate dates
            if (!strtotime($startDate) || !strtotime($endDate)) {
                Response::error("Invalid date format. Use YYYY-MM-DD format.");
            }

            $analytics = [
                'summary' => $this->getSalesSummary($startDate, $endDate),
                'transactions' => $this->getRecentTransactions($startDate, $endDate),
                'product_performance' => $this->getProductPerformance($startDate, $endDate),
                'revenue_trends' => $this->getRevenueTrends($startDate, $endDate, $period),
                'payment_status_breakdown' => $this->getPaymentStatusBreakdown($startDate, $endDate)
            ];

            Response::success("Marketplace analytics retrieved successfully", $analytics);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get sales summary
     */
    private function getSalesSummary($startDate, $endDate) {
        try {
            $db = $this->db;
            
            $sql = "SELECT 
                        COUNT(DISTINCT p.id) as total_transactions,
                        SUM(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE 0 END) as total_revenue,
                        AVG(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE NULL END) as avg_transaction_value,
                        SUM(CASE WHEN p.payment_status = 'completed' THEN p.total_items ELSE 0 END) as total_items_sold,
                        COUNT(DISTINCT CASE WHEN p.payment_status = 'completed' THEN p.user_id END) as active_buyers,
                        COUNT(CASE WHEN p.payment_status = 'completed' THEN 1 END) as completed_payments,
                        COUNT(CASE WHEN p.payment_status = 'pending' THEN 1 END) as pending_payments,
                        COUNT(CASE WHEN p.payment_status = 'failed' THEN 1 END) as failed_payments,
                        COUNT(CASE WHEN p.payment_status = 'refunded' THEN 1 END) as refunded_payments
                    FROM payments p 
                    WHERE p.payment_type = 'cart_purchase' 
                    AND DATE(p.created_at) BETWEEN ? AND ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate additional metrics
            $successRate = $result['total_transactions'] > 0 
                ? round(($result['completed_payments'] / $result['total_transactions']) * 100, 2) 
                : 0;

            return [
                'total_revenue' => floatval($result['total_revenue'] ?? 0),
                'total_transactions' => intval($result['total_transactions']),
                'avg_transaction_value' => round(floatval($result['avg_transaction_value'] ?? 0), 2),
                'total_items_sold' => intval($result['total_items_sold'] ?? 0),
                'active_buyers' => intval($result['active_buyers'] ?? 0),
                'payment_success_rate' => $successRate,
                'payment_breakdown' => [
                    'completed' => intval($result['completed_payments'] ?? 0),
                    'pending' => intval($result['pending_payments'] ?? 0),
                    'failed' => intval($result['failed_payments'] ?? 0),
                    'refunded' => intval($result['refunded_payments'] ?? 0)
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception("Error fetching sales summary: " . $e->getMessage());
        }
    }

    /**
     * Get recent marketplace transactions
     */
    private function getRecentTransactions($startDate, $endDate, $limit = 20) {
        try {
            $db = $this->db;
            
            $sql = "SELECT 
                        p.id,
                        p.transaction_id,
                        p.amount,
                        p.payment_status,
                        p.payment_method,
                        p.created_at,
                        p.total_items,
                        u.first_name,
                        u.last_name,
                        u.email,
                        co.order_number,
                        co.order_status,
                        p.cart_items
                    FROM payments p
                    JOIN user u ON p.user_id = u.user_id
                    LEFT JOIN cart_orders co ON p.order_id = co.id
                    WHERE p.payment_type = 'cart_purchase' 
                    AND DATE(p.created_at) BETWEEN ? AND ?
                    ORDER BY p.created_at DESC 
                    LIMIT ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate, $limit]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process cart items and format data
            foreach ($transactions as &$transaction) {
                $transaction['buyer_name'] = $transaction['first_name'] . ' ' . $transaction['last_name'];
                $transaction['amount'] = floatval($transaction['amount']);
                
                // Parse cart items if available
                if (!empty($transaction['cart_items'])) {
                    $cartItems = json_decode($transaction['cart_items'], true);
                    if ($cartItems && is_array($cartItems)) {
                        $transaction['products'] = array_map(function($item) {
                            return [
                                'name' => $item['product_name'] ?? 'Unknown Product',
                                'quantity' => $item['quantity'] ?? 0,
                                'unit_price' => floatval($item['unit_price'] ?? 0),
                                'total_price' => floatval($item['total_price'] ?? 0)
                            ];
                        }, $cartItems);
                    } else {
                        $transaction['products'] = [];
                    }
                } else {
                    $transaction['products'] = [];
                }
                
                // Remove raw cart_items from response
                unset($transaction['cart_items']);
                unset($transaction['first_name']);
                unset($transaction['last_name']);
            }
            
            return $transactions;
            
        } catch (Exception $e) {
            throw new Exception("Error fetching recent transactions: " . $e->getMessage());
        }
    }

    /**
     * Get product performance analytics
     */
    private function getProductPerformance($startDate, $endDate) {
        try {
            $db = $this->db;
            
            $sql = "SELECT 
                        JSON_UNQUOTE(JSON_EXTRACT(p.cart_items, '$[*].product_name')) as product_names,
                        JSON_UNQUOTE(JSON_EXTRACT(p.cart_items, '$[*].product_id')) as product_ids,
                        JSON_UNQUOTE(JSON_EXTRACT(p.cart_items, '$[*].quantity')) as quantities,
                        JSON_UNQUOTE(JSON_EXTRACT(p.cart_items, '$[*].total_price')) as total_prices,
                        p.cart_items
                    FROM payments p
                    WHERE p.payment_type = 'cart_purchase' 
                    AND p.payment_status = 'completed'
                    AND DATE(p.created_at) BETWEEN ? AND ?
                    AND p.cart_items IS NOT NULL";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $productStats = [];
            
            foreach ($results as $result) {
                if (empty($result['cart_items'])) continue;
                
                $cartItems = json_decode($result['cart_items'], true);
                if (!$cartItems || !is_array($cartItems)) continue;
                
                foreach ($cartItems as $item) {
                    $productName = $item['product_name'] ?? 'Unknown';
                    $quantity = floatval($item['quantity'] ?? 0);
                    $revenue = floatval($item['total_price'] ?? 0);
                    
                    if (!isset($productStats[$productName])) {
                        $productStats[$productName] = [
                            'product_name' => $productName,
                            'total_quantity_sold' => 0,
                            'total_revenue' => 0,
                            'transaction_count' => 0,
                            'avg_quantity_per_order' => 0
                        ];
                    }
                    
                    $productStats[$productName]['total_quantity_sold'] += $quantity;
                    $productStats[$productName]['total_revenue'] += $revenue;
                    $productStats[$productName]['transaction_count']++;
                }
            }
            
            // Calculate averages and sort by revenue
            foreach ($productStats as &$stat) {
                $stat['avg_quantity_per_order'] = $stat['transaction_count'] > 0 
                    ? round($stat['total_quantity_sold'] / $stat['transaction_count'], 2) 
                    : 0;
                $stat['total_revenue'] = round($stat['total_revenue'], 2);
            }
            
            // Sort by total revenue descending
            usort($productStats, function($a, $b) {
                return $b['total_revenue'] <=> $a['total_revenue'];
            });
            
            return array_slice($productStats, 0, 10); // Top 10 products
            
        } catch (Exception $e) {
            throw new Exception("Error fetching product performance: " . $e->getMessage());
        }
    }

    /**
     * Get revenue trends over time
     */
    private function getRevenueTrends($startDate, $endDate, $period = 'day') {
        try {
            $db = $this->db;
            
            // Define SQL based on period
            $groupBy = match($period) {
                'hour' => "DATE_FORMAT(p.created_at, '%Y-%m-%d %H:00:00')",
                'day' => "DATE(p.created_at)",
                'week' => "YEARWEEK(p.created_at, 1)",
                'month' => "DATE_FORMAT(p.created_at, '%Y-%m')",
                'year' => "YEAR(p.created_at)",
                default => "DATE(p.created_at)"
            };
            
            $sql = "SELECT 
                        {$groupBy} as period,
                        COUNT(*) as transaction_count,
                        SUM(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE 0 END) as revenue,
                        COUNT(CASE WHEN p.payment_status = 'completed' THEN 1 END) as completed_transactions,
                        COUNT(CASE WHEN p.payment_status = 'failed' THEN 1 END) as failed_transactions
                    FROM payments p
                    WHERE p.payment_type = 'cart_purchase'
                    AND DATE(p.created_at) BETWEEN ? AND ?
                    GROUP BY {$groupBy}
                    ORDER BY period ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the results
            foreach ($trends as &$trend) {
                $trend['revenue'] = floatval($trend['revenue']);
                $trend['transaction_count'] = intval($trend['transaction_count']);
                $trend['completed_transactions'] = intval($trend['completed_transactions']);
                $trend['failed_transactions'] = intval($trend['failed_transactions']);
                $trend['success_rate'] = $trend['transaction_count'] > 0 
                    ? round(($trend['completed_transactions'] / $trend['transaction_count']) * 100, 2) 
                    : 0;
            }
            
            return $trends;
            
        } catch (Exception $e) {
            throw new Exception("Error fetching revenue trends: " . $e->getMessage());
        }
    }

    /**
     * Get payment status breakdown
     */
    private function getPaymentStatusBreakdown($startDate, $endDate) {
        try {
            $db = $this->db;
            
            $sql = "SELECT 
                        p.payment_status,
                        COUNT(*) as count,
                        SUM(p.amount) as total_amount,
                        AVG(p.amount) as avg_amount
                    FROM payments p
                    WHERE p.payment_type = 'cart_purchase'
                    AND DATE(p.created_at) BETWEEN ? AND ?
                    GROUP BY p.payment_status
                    ORDER BY count DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($breakdown as &$item) {
                $item['count'] = intval($item['count']);
                $item['total_amount'] = floatval($item['total_amount']);
                $item['avg_amount'] = round(floatval($item['avg_amount']), 2);
            }
            
            return $breakdown;
            
        } catch (Exception $e) {
            throw new Exception("Error fetching payment status breakdown: " . $e->getMessage());
        }
    }

    /**
     * Get buyer analytics
     */
    public function getBuyerAnalytics() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager']);

            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            $db = $this->db;
            
            $sql = "SELECT 
                        u.user_id,
                        u.first_name,
                        u.last_name,
                        u.email,
                        COUNT(p.id) as total_orders,
                        SUM(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE 0 END) as total_spent,
                        AVG(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE NULL END) as avg_order_value,
                        MAX(p.created_at) as last_order_date,
                        SUM(CASE WHEN p.payment_status = 'completed' THEN p.total_items ELSE 0 END) as total_items_purchased
                    FROM user u
                    JOIN payments p ON u.user_id = p.user_id
                    WHERE p.payment_type = 'cart_purchase'
                    AND DATE(p.created_at) BETWEEN ? AND ?
                    AND u.user_role = 'Buyer'
                    GROUP BY u.user_id, u.first_name, u.last_name, u.email
                    ORDER BY total_spent DESC
                    LIMIT 20";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($buyers as &$buyer) {
                $buyer['buyer_name'] = $buyer['first_name'] . ' ' . $buyer['last_name'];
                $buyer['total_spent'] = floatval($buyer['total_spent']);
                $buyer['avg_order_value'] = round(floatval($buyer['avg_order_value'] ?? 0), 2);
                $buyer['total_orders'] = intval($buyer['total_orders']);
                $buyer['total_items_purchased'] = intval($buyer['total_items_purchased']);
                
                unset($buyer['first_name']);
                unset($buyer['last_name']);
            }

            Response::success("Buyer analytics retrieved successfully", $buyers);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get detailed transaction report
     */
    public function getTransactionReport() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager']);

            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $status = $_GET['status'] ?? null;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

            $db = $this->db;
            
            $whereClause = "WHERE p.payment_type = 'cart_purchase' AND DATE(p.created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            
            if ($status) {
                $whereClause .= " AND p.payment_status = ?";
                $params[] = $status;
            }
            
            $sql = "SELECT 
                        p.id,
                        p.transaction_id,
                        p.amount,
                        p.payment_status,
                        p.payment_method,
                        p.created_at,
                        p.updated_at,
                        p.total_items,
                        p.gateway_response,
                        u.first_name,
                        u.last_name,
                        u.email,
                        co.order_number,
                        co.order_status,
                        co.shipping_address
                    FROM payments p
                    JOIN user u ON p.user_id = u.user_id
                    LEFT JOIN cart_orders co ON p.order_id = co.id
                    {$whereClause}
                    ORDER BY p.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total FROM payments p {$whereClause}";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute(array_slice($params, 0, -2)); // Remove LIMIT and OFFSET
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            foreach ($transactions as &$transaction) {
                $transaction['buyer_name'] = $transaction['first_name'] . ' ' . $transaction['last_name'];
                $transaction['amount'] = floatval($transaction['amount']);
                $transaction['total_items'] = intval($transaction['total_items']);
                
                // Keep first_name and last_name for frontend compatibility
                // Don't unset them so frontend can access them
            }

            Response::success("Transaction report retrieved successfully", [
                'transactions' => $transactions,
                'pagination' => [
                    'total' => intval($totalCount),
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $totalCount
                ]
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Get land report payments
     */
    public function getLandReportPayments() {
        try {
            SessionManager::requireAuth();
            SessionManager::requireRole(['Financial_Manager']);

            // Get date range from query parameters (default to last 30 days)
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

            $sql = "SELECT 
                        p.id,
                        p.transaction_id,
                        p.amount,
                        p.payment_status,
                        p.payment_method,
                        p.created_at,
                        p.land_id,
                        u.first_name,
                        u.last_name,
                        u.email
                    FROM payments p
                    JOIN user u ON p.user_id = u.user_id
                    WHERE p.payment_type = 'land_report'
                    AND DATE(p.created_at) BETWEEN ? AND ?
                    ORDER BY p.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate, $limit, $offset]);
            $landReportPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format the data for frontend
            foreach ($landReportPayments as &$payment) {
                $payment['id'] = intval($payment['id']);
                $payment['amount'] = floatval($payment['amount']);
                $payment['land_id'] = intval($payment['land_id']);
            }

            // Get summary statistics
            $summarySQL = "SELECT 
                            COUNT(*) as total_transactions,
                            SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                            COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_payments,
                            COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_payments,
                            COUNT(CASE WHEN payment_status = 'failed' THEN 1 END) as failed_payments
                        FROM payments 
                        WHERE payment_type = 'land_report'
                        AND DATE(created_at) BETWEEN ? AND ?";
            
            $summaryStmt = $this->db->prepare($summarySQL);
            $summaryStmt->execute([$startDate, $endDate]);
            $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

            $result = [
                'payments' => $landReportPayments,
                'summary' => [
                    'total_revenue' => floatval($summary['total_revenue'] ?? 0),
                    'total_transactions' => intval($summary['total_transactions'] ?? 0),
                    'completed_payments' => intval($summary['completed_payments'] ?? 0),
                    'pending_payments' => intval($summary['pending_payments'] ?? 0),
                    'failed_payments' => intval($summary['failed_payments'] ?? 0)
                ],
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => intval($summary['total_transactions'] ?? 0)
                ]
            ];

            Response::success("Land report payments retrieved successfully", $result);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

?>