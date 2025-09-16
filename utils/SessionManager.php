<?php

class SessionManager {
    
    private static function ensureSessionStarted() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function createUserSession($userData) {
        self::ensureSessionStarted();
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['user_role'] = $userData['user_role'];
        $_SESSION['first_name'] = $userData['first_name'];
        $_SESSION['last_name'] = $userData['last_name'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['phone'] = $userData['phone'] ?? null;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    public static function isLoggedIn() {
        self::ensureSessionStarted();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function getUserSession() {
        self::ensureSessionStarted();
        if (self::isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'user_role' => $_SESSION['user_role'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name'],
                'email' => $_SESSION['email'],
                'phone' => $_SESSION['phone'] ?? null,
                'login_time' => $_SESSION['login_time'],
                'last_activity' => $_SESSION['last_activity']
            ];
        }
        return null;
    }
    
    public static function updateLastActivity() {
        self::ensureSessionStarted();
        if (self::isLoggedIn()) {
            $_SESSION['last_activity'] = time();
        }
    }
    
    public static function destroySession() {
        self::ensureSessionStarted();
        session_unset();
        session_destroy();
    }
    
    public static function isSessionExpired($timeout = 3600) { // 1 hour default
        self::ensureSessionStarted();
        if (!self::isLoggedIn()) {
            return true;
        }
        
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            return true;
        }
        
        return false;
    }

    public static function requireAuth() {
        if (!self::isLoggedIn() || self::isSessionExpired()) {
            Response::unauthorized('Authentication required');
        }
        self::updateLastActivity();
    }

    public static function requireRole($allowedRoles) {
        self::requireAuth();
        
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        if (!in_array($_SESSION['user_role'], $allowedRoles)) {
            Response::forbidden('Insufficient permissions');
        }
    }

    public static function getCurrentUserId() {
        self::requireAuth();
        return $_SESSION['user_id'];
    }

    public static function getCurrentUserRole() {
        self::requireAuth();
        return $_SESSION['user_role'];
    }
}

?>