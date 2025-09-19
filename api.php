<?php
/**
 * FARMMASTER API - Clean MVC Entry Point
 * Single file that routes all API requests to appropriate controllers
 */

// CORS Headers - Allow any localhost port
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include MVC components
require_once 'config/Database.php';
require_once 'utils/Response.php';
require_once 'utils/SessionManager.php';

// Include controllers
require_once 'controllers/UserController.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/CropController.php';
require_once 'controllers/LandController.php';
require_once 'controllers/AssessmentController.php';
require_once 'controllers/ProposalController.php';
require_once 'controllers/LandReportController.php';
require_once 'controllers/HarvestController.php';
require_once 'controllers/PaymentController.php';
require_once 'controllers/OrderController.php';

// Simple Router
class APIRouter {
    public function route() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Handle method override for forms (POST with _method field)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        // Remove query parameters and get path
        $path = parse_url($uri, PHP_URL_PATH);
        $path = trim($path, '/');
        
        // Remove base path if running from subdirectory
        if (strpos($path, 'v/FARMMASTER-Backend/api.php') === 0) {
            $path = substr($path, strlen('v/FARMMASTER-Backend/api.php'));
            $path = trim($path, '/');
        } elseif (strpos($path, 'v/FARMMASTER-Backend/') === 0) {
            $path = substr($path, strlen('v/FARMMASTER-Backend/'));
            $path = trim($path, '/');
        } elseif (strpos($path, 'FARMMASTER-Backend/api.php') === 0) {
            $path = substr($path, strlen('FARMMASTER-Backend/api.php'));
            $path = trim($path, '/');
        } elseif (strpos($path, 'FARMMASTER-Backend/') === 0) {
            $path = substr($path, strlen('FARMMASTER-Backend/'));
            $path = trim($path, '/');
        }
        
        // Split path into segments
        $segments = explode('/', $path);

        try {
            // Handle /api/endpoint URLs properly
            if ($segments[0] === 'api' && isset($segments[1])) {
                $endpoint = $segments[1];
                // Remove 'api' from segments for proper handling
                $segments = array_slice($segments, 1);
            } else {
                // Check if first segment is api.php, if so use segments[1]
                $endpoint = $segments[0] === 'api.php' ? $segments[1] ?? '' : $segments[0];
            }
            
            // Add this block for /buyer/orders POST endpoint
            if ($segments[0] === 'buyer' && isset($segments[1]) && $segments[1] === 'orders' && $method === 'POST') {
                require_once 'controllers/BuyerController.php';
                $controller = new BuyerController();
                $controller->getOrders();
                return;
            }

            switch ($endpoint) {
                case 'auth':
                    $this->handleAuth($method, $segments);
                    break;
                case 'users':
                    $this->handleUsers($method, $segments);
                    break;
                case 'products':
                    $this->handleProducts($method, $segments);
                    break;
                case 'crops':
                    $this->handleCrops($method, $segments);
                    break;
                case 'lands':
                    $this->handleLands($method, $segments);
                    break;
                case 'assessments':
                    $this->handleAssessments($method, $segments);
                    break;
                case 'proposals':
                    $this->handleProposals($method, $segments);
                    break;
                case 'land-reports':
                    $this->handleLandReports($method, $segments);
                    break;
                case 'harvest':
                    $this->handleHarvest($method, $segments);
                    break;
                case 'payments':
                    $this->handlePayments($method, $segments);
                    break;
                case 'orders':
                    $this->handleOrders($method, $segments);
                    break;
                case 'reports':
                    $this->handleReports($method, $segments);
                    break;
                case 'buyerDashboard':
                    $this->handleBuyerDashboard($method, $segments);
                    break;
                case 'dashboard':
                    $this->handleDashboard($method, $segments);
                    break;
                default:
                    Response::error('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            Response::error('Internal server error: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleAuth($method, $segments) {
        $controller = new UserController();
        
        if (count($segments) > 1 && $segments[1] === 'login' && $method === 'POST') {
            $controller->login();
        } elseif (count($segments) > 1 && $segments[1] === 'register' && $method === 'POST') {
            $controller->register();
        } else {
            Response::error('Invalid auth endpoint', 404);
        }
    }
    
    private function handleUsers($method, $segments) {
        $controller = new UserController();
        
        if (count($segments) > 1 && $segments[1] === 'login' && $method === 'POST') {
            $controller->login();
        } elseif (count($segments) > 1 && $segments[1] === 'register' && $method === 'POST') {
            $controller->register();
        } elseif (count($segments) > 1 && $segments[1] === 'forgot-password' && $method === 'POST') {
            $controller->forgotPassword();
        } elseif (count($segments) > 1 && $segments[1] === 'reset-password' && $method === 'POST') {
            $controller->resetPassword();
        } elseif ($method === 'GET') {
            $controller->getAllUsers();
        } elseif ($method === 'POST') {
            $controller->createUser();
        } elseif ($method === 'PUT' && isset($segments[1])) {
            if (count($segments) > 2 && $segments[2] === 'status') {
                $controller->updateUserStatus($segments[1]);
            } else {
                $controller->updateUser($segments[1]);
            }
        } elseif ($method === 'DELETE' && isset($segments[1])) {
            $controller->deleteUser($segments[1]);
        } else {
            Response::error('Invalid users endpoint', 404);
        }
    }
    
    private function handleProducts($method, $segments) {
        $controller = new ProductController();

        // Add this block for /api/products/new-crops
        if ($method === 'GET' && isset($segments[1]) && $segments[1] === 'new-crops') {
            $controller->getNewCropsForProduct();
            return;
        }

        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    $controller->getProduct($segments[1]);
                } else {
                    $controller->getProducts();
                }
                break;
            case 'POST':
                // Check if this is actually an update via method override
                if (isset($segments[1]) && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
                    $controller->updateProduct($segments[1]);
                } else {
                    $controller->addProduct();
                }
                break;
            case 'PUT':
                if (isset($segments[1])) {
                    $controller->updateProduct($segments[1]);
                } else {
                    Response::error('Product ID required for update', 400);
                }
                break;
            case 'DELETE':
                if (isset($segments[1])) {
                    $controller->deleteProduct($segments[1]);
                } else {
                    Response::error('Product ID required', 400);
                }
                break;
            default:
                Response::error('Invalid products endpoint', 404);
        }
    }
    
    private function handleCrops($method, $segments) {
        $controller = new CropController();
        
        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    $controller->getCrop($segments[1]);
                } else {
                    $controller->getCrops();
                }
                break;
            case 'POST':
                $controller->addCrop();
                break;
            case 'PUT':
                if (isset($segments[1])) {
                    if (count($segments) > 2 && $segments[2] === 'status') {
                        $controller->updateCropStatus($segments[1]);
                    } else {
                        $controller->updateCrop($segments[1]);
                    }
                } else {
                    Response::error('Crop ID required for update', 400);
                }
                break;
            
            default:
                Response::error('Invalid crops endpoint', 404);
        }
    }
    
    private function handleLands($method, $segments) {
        $controller = new LandController();
        
        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    $controller->getLand($segments[1]);
                } else {
                    // Check for user_id parameter for getUserLands
                    if (isset($_GET['user_id'])) {
                        $controller->getUserLands();
                    } else {
                        $controller->getAllLands();
                    }
                }
                break;
            case 'POST':
                $controller->addLand();
                break;
            case 'PUT':
                if (isset($segments[1])) {
                    $controller->updateLand($segments[1]);
                } else {
                    Response::error('Land ID required for update', 400);
                }
                break;
            case 'DELETE':
                if (isset($segments[1])) {
                    $controller->deleteLand($segments[1]);
                } else {
                    Response::error('Land ID required', 400);
                }
                break;
            default:
                Response::error('Invalid lands endpoint', 404);
        }
    }
    
    private function handleAssessments($method, $segments) {
        $controller = new AssessmentController();
        
        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    // Handle specific assessment ID - need to create this method
                    $controller->getAssessment($segments[1]);
                } else {
                    // Check for user_id parameter for getUserAssessments
                    if (isset($_GET['user_id'])) {
                        $controller->getUserAssessments($_GET['user_id']);
                    } else {
                        $controller->getAllAssessments();
                    }
                }
                break;
            case 'POST':
                $controller->createAssessment();
                break;
            case 'PUT':
                $controller->updateAssessment();
                break;
            case 'DELETE':
                if (isset($segments[1])) {
                    $controller->deleteAssessment($segments[1]);
                } else {
                    Response::error('Assessment ID required', 400);
                }
                break;
            default:
                Response::error('Invalid assessments endpoint', 404);
        }
    }
    
    private function handleProposals($method, $segments) {
        $controller = new ProposalController();
        
        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    if ($segments[1] === 'public') {
                        // Public endpoint for testing without authentication
                        $controller->getAllProposalsPublic();
                    } else if (isset($segments[2]) && $segments[2] === 'public') {
                        // Public endpoint for single proposal: proposals/{id}/public
                        $controller->getProposalPublic($segments[1]);
                    } else {
                        $controller->getProposal($segments[1]);
                    }
                } else {
                    // Check for user_id parameter for getUserProposals
                    if (isset($_GET['user_id'])) {
                        $controller->getUserProposals($_GET['user_id']);
                    } else {
                        $controller->getAllProposals();
                    }
                }
                break;
            case 'POST':
                $controller->createProposal();
                break;
            case 'PUT':
                if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'status') {
                    $controller->updateProposalStatus($segments[1]);
                } else if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'status-public') {
                    // Public endpoint for status updates: proposals/{id}/status-public
                    $controller->updateProposalStatusPublic($segments[1]);
                } else if (isset($segments[1])) {
                    $controller->updateProposal($segments[1]);
                } else {
                    Response::error('Proposal ID required for update', 400);
                }
                break;
            case 'DELETE':
                if (isset($segments[1])) {
                    $controller->deleteProposal($segments[1]);
                } else {
                    Response::error('Proposal ID required', 400);
                }
                break;
            default:
                Response::error('Invalid proposals endpoint', 404);
        }
    }
    
    private function handleHarvest($method, $segments) {
        $controller = new HarvestController();
        
        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    $controller->getHarvest($segments[1]);
                } else {
                    // Check for user_id parameter for getUserHarvests
                    if (isset($_GET['user_id'])) {
                        $controller->getUserHarvests($_GET['user_id']);
                    } else {
                        $controller->getAllHarvests();
                    }
                }
                break;
            case 'POST':
                $controller->createHarvest();
                break;
            case 'PUT':
                $controller->updateHarvest();
                break;
            case 'DELETE':
                if (isset($segments[1])) {
                    $controller->deleteHarvest($segments[1]);
                } else {
                    Response::error('Harvest ID required', 400);
                }
                break;
            default:
                Response::error('Invalid harvest endpoint', 404);
        }
    }
    
    private function handlePayments($method, $segments) {
        $controller = new PaymentController();
        
        if (isset($segments[1]) && $segments[1] === 'process') {
            switch ($method) {
                case 'POST':
                    // Check action parameter from request body
                    $data = json_decode(file_get_contents("php://input"), true);
                    $action = $data['action'] ?? 'create_payment_intent';
                    
                    if ($action === 'create_payment_intent') {
                        $controller->createPaymentIntent();
                    } elseif ($action === 'confirm_payment') {
                        $controller->confirmPayment();
                    } else {
                        Response::error('Invalid action parameter', 400);
                    }
                    break;
                default:
                    Response::error('Invalid payment process endpoint', 404);
            }
        } else {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['user_id'])) {
                        $controller->getUserPayments($_GET['user_id']);
                    } else {
                        $controller->getAllPayments();
                    }
                    break;
                case 'POST':
                    $controller->addManualPayment();
                    break;
                default:
                    Response::error('Invalid payments endpoint', 404);
            }
        }
    }
    
    private function handleOrders($method, $segments) {
        $controller = new OrderController();
        
        if (isset($segments[1])) {
            if ($segments[1] === 'user' && isset($segments[2])) {
                // GET /api/orders/user/{user_id}
                if ($method === 'GET') {
                    $controller->getUserOrders($segments[2]);
                } else {
                    Response::error('Invalid method for user orders', 404);
                }
            } else if (is_numeric($segments[1])) {
                // GET /api/orders/{id} - get specific order
                // PUT /api/orders/{id}/status - update order status
                if ($method === 'GET') {
                    $controller->getOrder($segments[1]);
                } else if ($method === 'PUT' && isset($segments[2]) && $segments[2] === 'status') {
                    $controller->updateOrderStatus($segments[1]);
                } else {
                    Response::error('Invalid order endpoint', 404);
                }
            } else {
                Response::error('Invalid orders endpoint', 404);
            }
        } else {
            switch ($method) {
                case 'GET':
                    $controller->getAllOrders();
                    break;
                case 'POST':
                    $controller->createOrder();
                    break;
                default:
                    Response::error('Invalid orders endpoint', 404);
            }
        }
    }
    
    private function handleReports($method, $segments) {
        // Handle reports/land/{id}/pdf endpoint
        if (isset($segments[1]) && $segments[1] === 'land' && isset($segments[2]) && isset($segments[3]) && $segments[3] === 'pdf') {
            if ($method === 'GET') {
                require_once 'controllers/LandController.php';
                $controller = new LandController();
                $controller->generateLandReportPDF($segments[2]);
            } else {
                Response::error('Invalid report method', 404);
            }
        } else {
            Response::error('Invalid reports endpoint', 404);
        }
    }

    private function handleLandReports($method, $segments) {
        require_once 'controllers/LandReportController.php';
        $controller = new LandReportController();
        
        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    if ($segments[1] === 'public') {
                        // Public endpoint for testing without authentication
                        $controller->getAllReportsPublic();
                    } else if ($segments[1] === 'supervisors') {
                        // Get available supervisors: land-reports/supervisors
                        $controller->getAvailableSupervisors();
                    } else if ($segments[1] === 'supervisors-public') {
                        // Public endpoint for supervisors: land-reports/supervisors-public
                        $controller->getAvailableSupervisorsPublic();
                    } else if (isset($segments[2]) && $segments[2] === 'public') {
                        // Public endpoint for single report: land-reports/{id}/public
                        $controller->getReportPublic($segments[1]);
                    } else {
                        $controller->getReport($segments[1]);
                    }
                } else {
                    // Check for user_id parameter for getUserReports
                    if (isset($_GET['user_id'])) {
                        $controller->getUserReports();
                    } else {
                        $controller->getAllReports();
                    }
                }
                break;
            case 'POST':
                $controller->createReport();
                break;
            case 'PUT':
                if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'status') {
                    $controller->updateReportStatus($segments[1]);
                } else if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'status-public') {
                    // Public endpoint for status updates: land-reports/{id}/status-public
                    $controller->updateReportStatusPublic($segments[1]);
                } else if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'assign') {
                    // Assign supervisor: land-reports/{id}/assign
                    $controller->assignSupervisor($segments[1]);
                } else if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'assign-public') {
                    // Public assign supervisor: land-reports/{id}/assign-public
                    $controller->assignSupervisorPublic($segments[1]);
                } else if (isset($segments[1])) {
                    $controller->updateReport($segments[1]);
                } else {
                    Response::error('Report ID required for update', 400);
                }
                break;
            case 'DELETE':
                if (isset($segments[1]) && isset($segments[2]) && $segments[2] === 'public') {
                    // Public delete endpoint: land-reports/{id}/public
                    $controller->deleteAssignmentPublic($segments[1]);
                } else if (isset($segments[1])) {
                    $controller->deleteAssignment($segments[1]);
                } else {
                    Response::error('Report ID required', 400);
                }
                break;
            default:
                Response::error('Invalid land reports endpoint', 404);
        }
    }

    private function handleBuyerDashboard($method, $segments) {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true);
        $buyerId = $input['userId'] ?? null;

        if ($buyerId) {
            require_once 'controllers/BuyerController.php';
            $controller = new BuyerController();
            $controller->getDashboardData($buyerId);
        } else {
            Response::error('Missing userId', 400);
        }
    } else {
        Response::error('Invalid method for buyerDashboard', 405);
    }
}

    private function handleDashboard($method, $segments) {
        if ($method === 'GET' && count($segments) > 1 && $segments[1] === 'stats') {
            $controller = new UserController();
            $controller->getDashboardStats();
        } elseif ($method === 'GET' && count($segments) > 1 && $segments[1] === 'activity') {
            $controller = new UserController();
            $controller->getRecentActivity();
        } else {
            Response::error('Invalid dashboard endpoint', 404);
        }
    }
}

// Route the request
$router = new APIRouter();
$router->route();
?>