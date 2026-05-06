<?php
/**
 * Session Management for School Management System
 * Handles secure PHP sessions for authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

class SessionManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Login user and create session
     * @param array $user - User data from database
     * @return bool
     */
    public function login($user) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Logout user and destroy session
     * @return bool
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        
        return true;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check if user has specific role
     * @param string $role
     * @return bool
     */
    public function hasRole($role) {
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Get current user data
     * @return array|null
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'fullname' => $_SESSION['fullname'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    
    /**
     * Update last activity time
     */
    public function updateLastActivity() {
        if ($this->isLoggedIn()) {
            $_SESSION['last_activity'] = time();
        }
    }
    
    /**
     * Check if session has expired (30 minutes timeout)
     * @return bool
     */
    public function isSessionExpired() {
        if (!$this->isLoggedIn()) {
            return true;
        }
        
        $timeout = 30 * 60; // 30 minutes
        return (time() - $_SESSION['last_activity']) > $timeout;
    }
    
    /**
     * Require authentication - redirect to login if not logged in
     */
    public function requireAuth() {
        if (!$this->isLoggedIn() || $this->isSessionExpired()) {
            $this->logout();
            header('Location: /login.php');
            exit();
        }
        
        // Update last activity
        $this->updateLastActivity();
    }
    
    /**
     * Require specific role
     * @param string $role
     */
    public function requireRole($role) {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            // Redirect to appropriate dashboard or show access denied
            if ($this->hasRole('student')) {
                header('Location: /student-dashboard.php');
            } else {
                header('Location: /instructor-dashboard.php');
            }
            exit();
        }
    }
    
    /**
     * Get session timeout in seconds
     * @return int
     */
    public function getSessionTimeout() {
        if (!$this->isLoggedIn()) {
            return 0;
        }
        
        $timeout = 30 * 60; // 30 minutes
        $elapsed = time() - $_SESSION['last_activity'];
        return max(0, $timeout - $elapsed);
    }
    
    /**
     * Validate session integrity
     * @return bool
     */
    public function validateSession() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        // Check if session is expired
        if ($this->isSessionExpired()) {
            $this->logout();
            return false;
        }
        
        // Validate required session variables
        $required = ['user_id', 'username', 'fullname', 'email', 'role'];
        foreach ($required as $key) {
            if (!isset($_SESSION[$key]) || empty($_SESSION[$key])) {
                $this->logout();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Set flash message for next request
     * @param string $type - success, error, warning, info
     * @param string $message
     */
    public function setFlashMessage($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash messages and clear them
     * @return array
     */
    public function getFlashMessages() {
        $messages = [];
        
        if (isset($_SESSION['flash'])) {
            $messages = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        
        return $messages;
    }
    
    /**
     * Get CSRF token for form protection
     * @return string
     */
    public function getCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * @param string $token
     * @return bool
     */
    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Helper functions for global access
function requireAuth() {
    global $sessionManager;
    $sessionManager->requireAuth();
}

function requireRole($role) {
    global $sessionManager;
    $sessionManager->requireRole($role);
}

function getCurrentUser() {
    global $sessionManager;
    return $sessionManager->getCurrentUser();
}

function isLoggedIn() {
    global $sessionManager;
    return $sessionManager->isLoggedIn();
}

function hasRole($role) {
    global $sessionManager;
    return $sessionManager->hasRole($role);
}
?>
