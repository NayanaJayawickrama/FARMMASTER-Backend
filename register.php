<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include "database.php";

class UserValidator {
    
    public static function validateName($name, $fieldName) {
        if (empty(trim($name))) {
            throw new Exception("$fieldName is required.");
        }
        
        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            throw new Exception("$fieldName should contain only letters and spaces.");
        }
        
        return trim($name);
    }
    
    public static function validateEmail($email) {
        $email = trim($email);
        
        if (empty($email)) {
            throw new Exception("Email is required.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }
        
        return $email;
    }
    
    public static function validatePassword($password) {
        if (empty($password)) {
            throw new Exception("Password is required.");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long.");
        }
        
        if (!preg_match("/^(?=.*[a-zA-Z])(?=.*\d)/", $password)) {
            throw new Exception("Password must contain at least one letter and one number.");
        }
        
        return $password;
    }
    
    public static function validatePhone($phone) {
        if (empty($phone)) {
            return null; // Phone is optional
        }
        
        $phone = trim($phone);
        
        // Remove +94 prefix if present for validation
        $phone_digits = $phone;
        if (strpos($phone, '+94') === 0) {
            $phone_digits = substr($phone, 3);
        }
        
        // Check if remaining digits are exactly 9 and all numeric
        if (!preg_match("/^\d{9}$/", $phone_digits)) {
            throw new Exception("Phone number must be 9 digits after +94.");
        }
        
        // Ensure phone starts with +94
        return '+94' . $phone_digits;
    }
    
    public static function validateAccountType($accountType) {
        $accountType = trim($accountType);
        
        if (empty($accountType)) {
            throw new Exception("Account type is required.");
        }
        
        if (!in_array($accountType, ['Landowner', 'Buyer'])) {
            throw new Exception("Invalid account type.");
        }
        
        return $accountType;
    }
}

class UserRepository {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    
    public function phoneExists($phone) {
        if (empty($phone)) {
            return false;
        }
        
        $stmt = $this->conn->prepare("SELECT user_id FROM user WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    
    public function createUser($userData) {
        $stmt = $this->conn->prepare("INSERT INTO user (first_name, last_name, email, phone, password, user_role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", 
            $userData['first_name'], 
            $userData['last_name'], 
            $userData['email'], 
            $userData['phone'], 
            $userData['password'], 
            $userData['user_role']
        );
        
        if ($stmt->execute()) {
            $userId = $this->conn->insert_id;
            $stmt->close();
            return $userId;
        } else {
            $stmt->close();
            throw new Exception("Registration failed. Please try again.");
        }
    }
}

class UserService {
    private $userRepository;
    
    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }
    
    public function registerUser($data) {
        try {
            // Validate all input data
            $firstName = UserValidator::validateName($data['first_name'] ?? '', 'First name');
            $lastName = UserValidator::validateName($data['last_name'] ?? '', 'Last name');
            $email = UserValidator::validateEmail($data['email'] ?? '');
            $phone = UserValidator::validatePhone($data['phone'] ?? '');
            $password = UserValidator::validatePassword($data['password'] ?? '');
            $accountType = UserValidator::validateAccountType($data['account_type'] ?? '');
            
            // Check for existing email
            if ($this->userRepository->emailExists($email)) {
                throw new Exception("Email already registered.");
            }
            
            // Check for existing phone
            if ($this->userRepository->phoneExists($phone)) {
                throw new Exception("Phone number already registered.");
            }
            
            // Prepare user data
            $userData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'user_role' => $accountType
            ];
            
            // Create user
            $userId = $this->userRepository->createUser($userData);
            
            // Generate custom user ID
            $prefix = $accountType === 'Landowner' ? 'L-' : 'B-';
            $customUserId = $prefix . str_pad($userId, 3, '0', STR_PAD_LEFT);
            
            return [
                "status" => "success",
                "message" => "Registration successful.",
                "user_id" => $customUserId
            ];
            
        } catch (Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
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
    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        ApiResponse::send(["status" => "error", "message" => "Invalid JSON data."]);
    }
    
    // Initialize dependencies
    $userRepository = new UserRepository($conn);
    $userService = new UserService($userRepository);
    
    // Process registration
    $result = $userService->registerUser($data);
    
    // Send response
    ApiResponse::send($result);
    
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