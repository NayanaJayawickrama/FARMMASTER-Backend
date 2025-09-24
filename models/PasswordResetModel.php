<?php

require_once __DIR__ . '/../config/Database.php';

class PasswordResetModel extends BaseModel {
    protected $table = 'password_resets';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Create a new password reset token
     */
    public function createResetToken($email, $token, $expiresIn = 3600) {
    // Delete any existing tokens for this email
    $this->deleteEmailTokens($email);

    // Expires in 1 hours from now
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hours'));


    $data = [
        'email' => $email,
        'token' => $token,
        'expires_at' => $expiresAt
    ];

    return $this->create($data);
}

    /**
     * Find a valid reset token
     */
    public function findValidToken($token) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE token = :token 
                AND expires_at < NOW()
                LIMIT 1";
        
        $result = $this->executeQuery($sql, [':token' => $token]);
        return $result ? $result[0] : null;
    }

    /**
     * Delete token after use
     */
    public function deleteToken($token) {
        $sql = "DELETE FROM {$this->table} WHERE token = :token";
        return $this->executeQuery($sql, [':token' => $token]);
    }

    /**
     * Delete all tokens for an email
     */
    public function deleteEmailTokens($email) {
        $sql = "DELETE FROM {$this->table} WHERE email = :email";
        return $this->executeQuery($sql, [':email' => $email]);
    }

    /**
     * Clean up expired tokens (should be run periodically)
     */
    public function cleanupExpiredTokens() {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        return $this->executeQuery($sql);
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Check if email has any recent reset requests (to prevent spam)
     */
    public function hasRecentRequest($email, $minutes = 5) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE email = :email 
                AND expires_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";
        
        $result = $this->executeQuery($sql, [
            ':email' => $email,
            ':minutes' => $minutes
        ]);
        
        return $result && $result[0]['count'] > 0;
    }
}
?>