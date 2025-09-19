<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class UserController {
    private $userModel;
    private $validRoles = ['Landowner', 'Field Supervisor', 'Buyer', 'Operational_Manager', 'Financial_Manager'];
    private $roleMapping = [
        'Landowner' => 'Landowner',
        'Field Supervisor' => 'Field Supervisor', 
        'Buyer' => 'Buyer',
        'Operational_Manager' => 'Operational Manager',
        'Financial_Manager' => 'Financial Manager'
    ];

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Validate input
            $email = Validator::email($data['email'] ?? '');
            $password = Validator::required($data['password'] ?? '', 'Password');

            // Get user from database
            $user = $this->userModel->getUserByEmail($email);

            if (!$user) {
                Response::error("Invalid email or password", 401);
            }

            // Check if account is active
            if ((int)$user['is_active'] === 0) {
                Response::error("Your account is inactive. Please contact administrator", 403);
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                Response::error("Invalid email or password", 401);
            }

            // Create session
            SessionManager::createUserSession($user);

            // Get frontend-friendly role name
            $frontendRole = $this->roleMapping[$user['user_role']] ?? $user['user_role'];

            Response::success("Login successful", [
                "user_id" => $user['user_id'],
                "user_role" => $frontendRole,
                "first_name" => $user['first_name'],
                "last_name" => $user['last_name'],
                "email" => $user['email'],
                "phone" => $user['phone'],
                "session_id" => session_id(),
                "session_data" => SessionManager::getUserSession()
            ]);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function register() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Validate input data
            $firstName = Validator::name($data['first_name'] ?? '', 'First name');
            $lastName = Validator::name($data['last_name'] ?? '', 'Last name');
            $email = Validator::email($data['email'] ?? '');
            $phone = Validator::phone($data['phone'] ?? '');
            $password = Validator::password($data['password'] ?? '');
            $accountType = Validator::inArray($data['user_role'] ?? '', ['Landowner', 'Buyer'], 'Account type');

            // Check for existing email
            if ($this->userModel->emailExists($email)) {
                Response::error("Email already registered");
            }

            // Check for existing phone
            if ($this->userModel->phoneExists($phone)) {
                Response::error("Phone number already registered");
            }

            // Create user data
            $userData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'user_role' => $accountType
            ];

            // Create user
            $userId = $this->userModel->createUser($userData);

            if (!$userId) {
                Response::error("Registration failed. Please try again", 500);
            }

            // Generate custom user ID
            $prefix = $accountType === 'Landowner' ? 'L-' : 'B-';
            $customUserId = $prefix . str_pad($userId, 3, '0', STR_PAD_LEFT);

            Response::success("Registration successful", [
                "user_id" => $customUserId
            ], 201);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function createUser() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Validate input data
            $firstName = Validator::name($data['first_name'] ?? '', 'First name');
            $lastName = Validator::name($data['last_name'] ?? '', 'Last name');
            $email = Validator::email($data['email'] ?? '');
            $phone = Validator::phone($data['phone'] ?? '');
            $password = Validator::password($data['password'] ?? '');
            $userRole = Validator::inArray($data['user_role'] ?? '', $this->validRoles, 'User role');

            // Check for existing email
            if ($this->userModel->emailExists($email)) {
                Response::error("Email already registered");
            }

            // Check for existing phone
            if ($this->userModel->phoneExists($phone)) {
                Response::error("Phone number already registered");
            }

            // Create user data
            $userData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'user_role' => $userRole,
                'is_active' => 1
            ];

            $userId = $this->userModel->createUser($userData);

            if ($userId) {
                Response::success("User created successfully", [
                    'user_id' => $userId,
                    'message' => 'User has been added to the system'
                ]);
            } else {
                Response::error("Failed to create user");
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function logout() {
        SessionManager::destroySession();
        Response::success("Logout successful");
    }

    public function checkSession() {
        if (SessionManager::isSessionExpired()) {
            SessionManager::destroySession();
            Response::error("Session expired. Please login again", 401);
        }

        if (SessionManager::isLoggedIn()) {
            SessionManager::updateLastActivity();
            Response::success("Session is active", [
                "user_data" => SessionManager::getUserSession()
            ]);
        }

        Response::error("No active session", 401);
    }

    public function getAllUsers() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);

            $filters = [];
            if (isset($_GET['role'])) {
                $filters['role'] = $_GET['role'];
            }
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            $users = $this->userModel->getAllUsers($filters);

            // Map roles to frontend format
            foreach ($users as &$user) {
                $user['user_role'] = $this->roleMapping[$user['user_role']] ?? $user['user_role'];
            }

            Response::success("Users retrieved successfully", $users);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getUserById($userId) {
        try {
            SessionManager::requireAuth();

            // Users can view their own profile, managers can view any
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($currentUserId != $userId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager'])) {
                Response::forbidden("Access denied");
            }

            $user = $this->userModel->getUserById($userId);

            if (!$user) {
                Response::notFound("User not found");
            }

            // Map role to frontend format
            $user['user_role'] = $this->roleMapping[$user['user_role']] ?? $user['user_role'];
            
            Response::success("User retrieved successfully", $user);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateUser($userId) {
        try {
            SessionManager::requireAuth();

            // Users can update their own profile, managers can update any
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($currentUserId != $userId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager'])) {
                Response::forbidden("Access denied");
            }

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $updateData = [];

            if (isset($data['first_name'])) {
                $updateData['first_name'] = Validator::name($data['first_name'], 'First name');
            }
            if (isset($data['last_name'])) {
                $updateData['last_name'] = Validator::name($data['last_name'], 'Last name');
            }
            if (isset($data['email'])) {
                $email = Validator::email($data['email']);
                if ($this->userModel->emailExists($email, $userId)) {
                    Response::error("Email already exists");
                }
                $updateData['email'] = $email;
            }
            if (isset($data['phone'])) {
                $phone = Validator::phone($data['phone']);
                if ($this->userModel->phoneExists($phone, $userId)) {
                    Response::error("Phone number already exists");
                }
                $updateData['phone'] = $phone;
            }
            if (isset($data['user_role']) && in_array($currentRole, ['Operational_Manager', 'Financial_Manager'])) {
                $updateData['user_role'] = Validator::inArray($data['user_role'], $this->validRoles, 'User role');
            }

            if (empty($updateData)) {
                Response::error("No valid fields to update");
            }

            $result = $this->userModel->updateUser($userId, $updateData);

            if ($result === false) {
                Response::error("Update failed", 500);
            } elseif ($result === 0) {
                Response::error("No changes were made");
            }

            Response::success("User updated successfully");

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateUserStatus($userId) {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['status'])) {
                Response::error("Status is required");
            }

            // Handle both numeric (0,1) and string ('active','inactive') formats
            $statusValue = $data['status'];
            if ($statusValue === 'active') {
                $status = 1;
            } elseif ($statusValue === 'inactive') {
                $status = 0;
            } else {
                $status = Validator::inArray($statusValue, [0, 1, '0', '1'], 'Status');
                $status = (int)$status;
            }

            $result = $this->userModel->updateUserStatus($userId, $status);

            if ($result === false) {
                Response::error("Status update failed", 500);
            } elseif ($result === 0) {
                Response::error("No changes were made");
            }

            $message = $status ? "User activated successfully" : "User deactivated successfully";
            Response::success($message);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function deleteUser($userId) {
        try {
            SessionManager::requireRole(['Operational_Manager']);

            $result = $this->userModel->deleteUser($userId);

            if (!$result) {
                Response::error("User deletion failed", 500);
            }

            Response::success("User deleted successfully");

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function changePassword($userId) {
        try {
            SessionManager::requireAuth();

            // Users can change their own password, managers can change any
            $currentUserId = SessionManager::getCurrentUserId();
            $currentRole = SessionManager::getCurrentUserRole();
            
            if ($currentUserId != $userId && !in_array($currentRole, ['Operational_Manager', 'Financial_Manager'])) {
                Response::forbidden("Access denied");
            }

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            if ($currentUserId == $userId) {
                // User changing their own password - require current password
                $currentPassword = Validator::required($data['current_password'] ?? '', 'Current password');
                if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
                    Response::error("Current password is incorrect");
                }
            }

            $newPassword = Validator::password($data['new_password'] ?? '');

            $result = $this->userModel->updatePassword($userId, $newPassword);

            if (!$result) {
                Response::error("Password update failed", 500);
            }

            Response::success("Password updated successfully");

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchUsers() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);

            $searchTerm = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? null;

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $users = $this->userModel->searchUsers($searchTerm, $role);

            // Map roles to frontend format
            foreach ($users as &$user) {
                $user['user_role'] = $this->roleMapping[$user['user_role']] ?? $user['user_role'];
            }

            Response::success("Search completed", $users);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getUserStats() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager']);

            $stats = $this->userModel->getUserStats();

            // Map roles to frontend format
            foreach ($stats as &$stat) {
                $stat['user_role'] = $this->roleMapping[$stat['user_role']] ?? $stat['user_role'];
            }

            Response::success("User statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function forgotPassword() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $email = Validator::email($data['email'] ?? '');
            $frontendUrl = $data['frontendUrl'] ?? 'http://localhost:5173';

            $user = $this->userModel->getUserByEmail($email);

            if (!$user) {
                // Don't reveal whether email exists or not for security
                Response::success("If the email exists, a reset link will be sent");
                return;
            }

            // Generate secure reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token (you'll need to create this method in UserModel)
            $this->userModel->storePasswordResetToken($user['user_id'], $token, $expiry);

            // Create reset link
            $resetLink = $frontendUrl . "/reset-password?token=" . $token . "&email=" . urlencode($email);

            // Send email (using PHPMailer)
            require_once __DIR__ . '/../vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'] ?? 'radeeshapraneeth531@gmail.com';
            $mail->Password = $_ENV['SMTP_PASS'] ?? 'nilbgvvrladdtfzk';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('noreply@farmmaster.com', 'FarmMaster');
            $mail->addAddress($email);
            $mail->Subject = 'Password Reset Request - FarmMaster';
            $mail->Body = "Hi {$user['first_name']},\n\nYou requested to reset your password. Click the link below to reset it:\n\n{$resetLink}\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nFarmMaster Team";

            $mail->send();
            
            Response::success("Password reset email sent successfully");

        } catch (Exception $e) {
            Response::error("Failed to send reset email: " . $e->getMessage());
        }
    }

    public function resetPassword() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $email = Validator::email($data['email'] ?? '');
            $token = Validator::required($data['token'] ?? '', 'Reset token');
            $newPassword = Validator::password($data['password'] ?? '');
            
            // Verify token and get user
            $user = $this->userModel->getUserByResetToken($email, $token);
            
            if (!$user) {
                Response::error("Invalid or expired reset token", 400);
            }

            // Update password
            $result = $this->userModel->updatePassword($user['user_id'], $newPassword);
            
            if (!$result) {
                Response::error("Failed to update password", 500);
            }

            // Clear reset token
            $this->userModel->clearPasswordResetToken($user['user_id']);

            Response::success("Password reset successfully");

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getDashboardStats() {
        try {
            // Get various statistics for operational manager dashboard
            $stats = $this->userModel->getDashboardStatistics();
            
            if ($stats === false) {
                Response::error("Failed to fetch dashboard statistics", 500);
            }

            Response::success("Dashboard statistics retrieved successfully", $stats);

        } catch (Exception $e) {
            error_log("Error in getDashboardStats: " . $e->getMessage());
            Response::error("Failed to fetch dashboard statistics: " . $e->getMessage());
        }
    }

    public function getRecentActivity() {
        try {
            $activities = $this->userModel->getRecentActivity(5);
            Response::success("Recent activity retrieved successfully", $activities);

        } catch (Exception $e) {
            error_log("Error in getRecentActivity: " . $e->getMessage());
            Response::error("Failed to fetch recent activity: " . $e->getMessage());
        }
    }
}

?>