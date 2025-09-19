<?php

require_once __DIR__ . '/../config/Database.php';

class UserModel extends BaseModel {
    protected $table = 'user';

    public function __construct() {
        parent::__construct();
    }

    public function getAllUsers($filters = []) {
        $conditions = [];
        $params = [];

        if (isset($filters['role']) && !empty($filters['role'])) {
            $conditions[] = 'user_role = :role';
            $params[':role'] = $filters['role'];
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $conditions[] = 'is_active = :status';
            $params[':status'] = $filters['status'];
        }

        $sql = "SELECT user_id, first_name, last_name, email, phone, user_role, is_active FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY user_id DESC";        return $this->executeQuery($sql, $params);
    }

    public function getUserById($userId) {
        $sql = "SELECT user_id, first_name, last_name, email, phone, user_role, is_active FROM {$this->table} WHERE user_id = :user_id";
        $result = $this->executeQuery($sql, [':user_id' => $userId]);
        return $result ? $result[0] : null;
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $result = $this->executeQuery($sql, [':email' => $email]);
        return $result ? $result[0] : null;
    }

    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT user_id FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeUserId) {
            $sql .= " AND user_id != :user_id";
            $params[':user_id'] = $excludeUserId;
        }

        $result = $this->executeQuery($sql, $params);
        return !empty($result);
    }

    public function phoneExists($phone, $excludeUserId = null) {
        if (empty($phone)) {
            return false;
        }

        $sql = "SELECT user_id FROM {$this->table} WHERE phone = :phone";
        $params = [':phone' => $phone];

        if ($excludeUserId) {
            $sql .= " AND user_id != :user_id";
            $params[':user_id'] = $excludeUserId;
        }

        $result = $this->executeQuery($sql, $params);
        return !empty($result);
    }

    public function createUser($userData) {
        $data = [
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            'password' => password_hash($userData['password'], PASSWORD_BCRYPT),
            'user_role' => $userData['user_role'],
            'is_active' => 1
        ];

        return $this->create($data);
    }

    public function updateUser($userId, $userData) {
        $data = [];

        if (isset($userData['first_name'])) {
            $data['first_name'] = $userData['first_name'];
        }
        if (isset($userData['last_name'])) {
            $data['last_name'] = $userData['last_name'];
        }
        if (isset($userData['email'])) {
            $data['email'] = $userData['email'];
        }
        if (isset($userData['phone'])) {
            $data['phone'] = $userData['phone'];
        }
        if (isset($userData['user_role'])) {
            $data['user_role'] = $userData['user_role'];
        }
        if (isset($userData['password'])) {
            $data['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
        }

        return $this->update($userId, $data, 'user_id');
    }

    public function updateUserStatus($userId, $status) {
        return $this->update($userId, ['is_active' => $status], 'user_id');
    }

    public function deleteUser($userId) {
        return $this->delete($userId, 'user_id');
    }

    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($userId, ['password' => $hashedPassword], 'user_id');
    }

    public function verifyPassword($userId, $password) {
        $sql = "SELECT password FROM {$this->table} WHERE user_id = :user_id";
        $result = $this->executeQuery($sql, [':user_id' => $userId]);
        
        if (empty($result)) {
            return false;
        }

        return password_verify($password, $result[0]['password']);
    }

    public function getUserStats() {
        $sql = "SELECT 
                    user_role,
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                FROM {$this->table} 
                GROUP BY user_role";
        
        return $this->executeQuery($sql);
    }

    public function searchUsers($searchTerm, $role = null) {
        $conditions = ["(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)"];
        $params = [':search' => "%{$searchTerm}%"];

        if ($role) {
            $conditions[] = "user_role = :role";
            $params[':role'] = $role;
        }

        $sql = "SELECT user_id, first_name, last_name, email, phone, user_role, is_active 
                FROM {$this->table} 
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY first_name, last_name";

        return $this->executeQuery($sql, $params);
    }

    public function storePasswordResetToken($userId, $token, $expiry) {
        // First, clear any existing tokens for this user
        $this->clearPasswordResetToken($userId);
        
        // Insert new token - you might need to create a password_resets table
        // For now, we'll store it in the user table with additional columns
        $sql = "UPDATE {$this->table} SET reset_token = :token, reset_token_expiry = :expiry WHERE user_id = :user_id";
        return $this->executeStatement($sql, [
            ':token' => $token,
            ':expiry' => $expiry,
            ':user_id' => $userId
        ]);
    }

    public function getUserByResetToken($email, $token) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = :email 
                AND reset_token = :token 
                AND reset_token_expiry > NOW()";
        $result = $this->executeQuery($sql, [
            ':email' => $email,
            ':token' => $token
        ]);
        return $result ? $result[0] : null;
    }

    public function clearPasswordResetToken($userId) {
        $sql = "UPDATE {$this->table} SET reset_token = NULL, reset_token_expiry = NULL WHERE user_id = :user_id";
        return $this->executeStatement($sql, [':user_id' => $userId]);
    }

    /**
     * Check if user has a secondary role
     */
    public function userHasSecondaryRole($userId, $role) {
        $sql = "SELECT COUNT(*) as count FROM user_roles WHERE user_id = :user_id AND role = :role";
        $result = $this->executeQuery($sql, [
            ':user_id' => $userId,
            ':role' => $role
        ]);
        return $result && $result[0]['count'] > 0;
    }

    /**
     * Grant secondary role to user
     */
    public function grantSecondaryRole($userId, $role) {
        try {
            $sql = "INSERT INTO user_roles (user_id, role, is_active) VALUES (:user_id, :role, FALSE) 
                    ON DUPLICATE KEY UPDATE created_date = CURRENT_TIMESTAMP";
            return $this->executeStatement($sql, [
                ':user_id' => $userId,
                ':role' => $role
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to grant secondary role: " . $e->getMessage());
        }
    }

    /**
     * Set active role for user (null means use original role)
     */
    public function setActiveRole($userId, $role) {
        try {
            // First deactivate all secondary roles for this user
            $this->executeStatement(
                "UPDATE user_roles SET is_active = FALSE WHERE user_id = :user_id",
                [':user_id' => $userId]
            );

            // Update current active role in user table
            $sql = "UPDATE {$this->table} SET current_active_role = :role WHERE user_id = :user_id";
            $result = $this->executeStatement($sql, [
                ':user_id' => $userId,
                ':role' => $role
            ]);

            // If switching to a secondary role, mark it as active
            if ($role && in_array($role, ['Buyer', 'Landowner'])) {
                $this->executeStatement(
                    "UPDATE user_roles SET is_active = TRUE WHERE user_id = :user_id AND role = :role",
                    [':user_id' => $userId, ':role' => $role]
                );
            }

            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to set active role: " . $e->getMessage());
        }
    }

    /**
     * Get current active role for user (returns active role or original role)
     */
    public function getCurrentRole($userId) {
        $sql = "SELECT user_role, current_active_role FROM {$this->table} WHERE user_id = :user_id";
        $result = $this->executeQuery($sql, [':user_id' => $userId]);
        
        if (!$result) {
            return null;
        }

        $user = $result[0];
        return $user['current_active_role'] ?: $user['user_role'];
    }

    /**
     * Get user's role switching history
     */
    public function getRoleSwitchHistory($userId) {
        $sql = "SELECT role, is_active, created_date FROM user_roles 
                WHERE user_id = :user_id 
                ORDER BY created_date DESC";
        return $this->executeQuery($sql, [':user_id' => $userId]);
    }
}

?>