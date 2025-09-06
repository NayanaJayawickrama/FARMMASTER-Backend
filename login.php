<?php

session_start(); // Start session

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include "database.php";

class LoginValidator {
    
    public static function validateEmail($email) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (empty($email)) {
            throw new Exception("Email is required.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }
        
        return $email;
    }
    
    public static function validatePassword($password) {
        $password = trim($password);
        
        if (empty($password)) {
            throw new Exception("Password is required.");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long.");
        }
        
        return $password;
    }
}

class UserRepository {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT user_id, first_name, last_name, email, password, user_role, phone, is_active FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
}

class SessionManager {
    
    public static function createUserSession($userData) {
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['user_role'] = $userData['user_role'];
        $_SESSION['first_name'] = $userData['first_name'];
        $_SESSION['last_name'] = $userData['last_name'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['phone'] = $userData['phone'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function getUserSession() {
        if (self::isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'user_role' => $_SESSION['user_role'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name'],
                'email' => $_SESSION['email'],
                'phone' => $_SESSION['phone'],
                'login_time' => $_SESSION['login_time'],
                'last_activity' => $_SESSION['last_activity']
            ];
        }
        return null;
    }
    
    public static function updateLastActivity() {
        if (self::isLoggedIn()) {
            $_SESSION['last_activity'] = time();
        }
    }
    
    public static function destroySession() {
        session_unset();
        session_destroy();
    }
    
    public static function isSessionExpired($timeout = 3600) { // 1 hour default
        if (!self::isLoggedIn()) {
            return true;
        }
        
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            return true;
        }
        
        return false;
    }
}

class RoleMapper {
    private static $dbToFrontendRole = [
        'Landowner' => 'Landowner',
        'Supervisor' => 'Supervisor', 
        'Buyer' => 'Buyer',
        'Operational_Manager' => 'Operational Manager',
        'Financial_Manager' => 'Financial Manager'
    ];
    
    public static function getFrontendRole($dbRole) {
        return self::$dbToFrontendRole[$dbRole] ?? $dbRole;
    }
}

class AuthService {
    private $userRepository;
    
    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }
    
    public function login($data) {
        try {
            // Validate input
            $email = LoginValidator::validateEmail($data['email'] ?? '');
            $password = LoginValidator::validatePassword($data['password'] ?? '');
            
            // Get user from database
            $user = $this->userRepository->getUserByEmail($email);
            
            if (!$user) {
                throw new Exception("Invalid email or password.");
            }
            
            // Check if account is active
            if ((int)$user['is_active'] === 0) {
                throw new Exception("Your account is inactive. Please contact administrator.");
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                throw new Exception("Invalid email or password.");
            }
            
            // Create session
            SessionManager::createUserSession($user);
            
            // Get frontend-friendly role name
            $frontendRole = RoleMapper::getFrontendRole($user['user_role']);
            
            return [
                "status" => "success",
                "message" => "Login successful.",
                "user_id" => $user['user_id'],
                "user_role" => $frontendRole,
                "first_name" => $user['first_name'],
                "last_name" => $user['last_name'],
                "email" => $user['email'],
                "phone" => $user['phone'],
                "session_id" => session_id(),
                "session_data" => SessionManager::getUserSession()
            ];
            
        } catch (Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }
    
    public function logout() {
        SessionManager::destroySession();
        return [
            "status" => "success",
            "message" => "Logout successful."
        ];
    }
    
    public function checkSession() {
        if (SessionManager::isSessionExpired()) {
            SessionManager::destroySession();
            return [
                "status" => "error",
                "message" => "Session expired. Please login again."
            ];
        }
        
        if (SessionManager::isLoggedIn()) {
            SessionManager::updateLastActivity();
            return [
                "status" => "success",
                "message" => "Session is active.",
                "user_data" => SessionManager::getUserSession()
            ];
        }
        
        return [
            "status" => "error",
            "message" => "No active session."
        ];
    }
}

class ApiResponse {
    public static function send($data) {
        echo json_encode($data);
        exit();
    }
}

// Main execution
try {
    // Get request method and data
    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Initialize dependencies
    $userRepository = new UserRepository($conn);
    $authService = new AuthService($userRepository);
    
    switch ($method) {
        case 'POST':
            // Handle login
            if (!$data) {
                ApiResponse::send(["status" => "error", "message" => "Invalid JSON data."]);
            }
            
            $result = $authService->login($data);
            ApiResponse::send($result);
            break;
            
        case 'GET':
            // Handle session check
            $result = $authService->checkSession();
            ApiResponse::send($result);
            break;
            
        case 'DELETE':
            // Handle logout
            $result = $authService->logout();
            ApiResponse::send($result);
            break;
            
        default:
            ApiResponse::send([
                "status" => "error",
                "message" => "Method not allowed."
            ]);
            break;
    }
    
} catch (Exception $e) {
    ApiResponse::send([
        "status" => "error", 
        "message" => "Server error. Please try again."
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>