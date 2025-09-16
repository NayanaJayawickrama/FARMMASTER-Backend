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
}

?>