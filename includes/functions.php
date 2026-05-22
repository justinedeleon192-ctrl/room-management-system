<?php
/**
 * Helper Functions for School Management System
 * Common utilities for authentication, validation, and data processing
 */

/**
 * Send JSON response
 * @param bool $success
 * @param string $message
 * @param array $data
 * @param int $httpCode
 */
function sendJsonResponse($success, $message, $data = [], $httpCode = 200) {
    header('Content-Type: application/json');
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Send error response
 * @param string $message
 * @param int $httpCode
 */
function sendErrorResponse($message, $httpCode = 400) {
    sendJsonResponse(false, $message, [], $httpCode);
}

/**
 * Send success response
 * @param string $message
 * @param array $data
 */
function sendSuccessResponse($message, $data = []) {
    sendJsonResponse(true, $message, $data);
}

/**
 * Get POST data as JSON
 * @return array
 */
function getJsonInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?: [];
}

/**
 * Get POST data with fallback
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getPost($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data with fallback
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getGet($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Sanitize input data
 * @param string $input
 * @return string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate username format
 * @param string $username
 * @return bool
 */
function isValidUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/**
 * Validate password strength
 * @param string $password
 * @return array
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if (strlen($password) > 50) {
        $errors[] = 'Password cannot exceed 50 characters';
    }
    
    return $errors;
}

/**
 * Validate full name
 * @param string $fullname
 * @return array
 */
function validateFullname($fullname) {
    $errors = [];
    
    if (empty(trim($fullname))) {
        $errors[] = 'Full name is required';
    } elseif (strlen(trim($fullname)) < 2) {
        $errors[] = 'Full name must be at least 2 characters long';
    } elseif (strlen(trim($fullname)) > 255) {
        $errors[] = 'Full name cannot exceed 255 characters';
    }
    
    return $errors;
}

/**
 * Validate user registration data
 * @param array $data
 * @return array
 */
function validateRegistration($data) {
    $errors = [];
    
    // Validate fullname
    $fullnameErrors = validateFullname($data['fullname'] ?? '');
    $errors = array_merge($errors, $fullnameErrors);
    
    // Validate email
    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($data['email'])) {
        $errors[] = 'Invalid email format';
    }
    
    // Validate username
    if (empty($data['username'])) {
        $errors[] = 'Username is required';
    } elseif (!isValidUsername($data['username'])) {
        $errors[] = 'Username must be 3-20 characters and contain only letters, numbers, and underscores';
    }
    
    // Validate password
    $passwordErrors = validatePassword($data['password'] ?? '');
    $errors = array_merge($errors, $passwordErrors);
    
    // Validate role
    if (empty($data['role']) || !in_array($data['role'], ['student', 'instructor'])) {
        $errors[] = 'Invalid role specified';
    }
    
    return $errors;
}

/**
 * Validate login data
 * @param array $data
 * @return array
 */
function validateLogin($data) {
    $errors = [];
    
    if (empty($data['username'])) {
        $errors[] = 'Username is required';
    }
    
    if (empty($data['password'])) {
        $errors[] = 'Password is required';
    }
    
    return $errors;
}

/**
 * Hash password using PHP's built-in functions
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 * @param int $length
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Redirect to URL
 * @param string $url
 * @param bool $permanent
 */
function redirect($url, $permanent = false) {
    if ($permanent) {
        header('Location: ' . $url, true, 301);
    } else {
        header('Location: ' . $url);
    }
    exit();
}

/**
 * Get current URL
 * @return string
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if request is AJAX
 * @return bool
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 * @return string
 */
function getClientIp() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Log activity for debugging
 * @param string $message
 * @param string $level
 */
function logActivity($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIp();
    $logMessage = "[{$timestamp}] [{$level}] [{$ip}] {$message}" . PHP_EOL;
    
    error_log($logMessage, 3, __DIR__ . '/../logs/app.log');
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M d, Y h:i A') {
    return date($format, strtotime($date));
}

/**
 * Generate pagination links
 * @param int $currentPage
 * @param int $totalPages
 * @param string $baseUrl
 * @return array
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $pagination = [];
    
    if ($totalPages <= 1) {
        return $pagination;
    }
    
    // Previous page
    if ($currentPage > 1) {
        $pagination[] = [
            'type' => 'prev',
            'url' => $baseUrl . '?page=' . ($currentPage - 1),
            'disabled' => false
        ];
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $pagination[] = ['type' => 'page', 'url' => $baseUrl . '?page=1', 'page' => 1];
        if ($start > 2) {
            $pagination[] = ['type' => 'ellipsis'];
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $pagination[] = [
            'type' => 'page',
            'url' => $baseUrl . '?page=' . $i,
            'page' => $i,
            'active' => $i === $currentPage
        ];
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $pagination[] = ['type' => 'ellipsis'];
        }
        $pagination[] = ['type' => 'page', 'url' => $baseUrl . '?page=' . $totalPages, 'page' => $totalPages];
    }
    
    // Next page
    if ($currentPage < $totalPages) {
        $pagination[] = [
            'type' => 'next',
            'url' => $baseUrl . '?page=' . ($currentPage + 1),
            'disabled' => false
        ];
    }
    
    return $pagination;
}

/**
 * Clean and validate room data
 * @param array $data
 * @return array
 */
function validateRoomData($data) {
    $errors = [];
    
    // Validate room name
    if (empty(trim($data['name']))) {
        $errors[] = 'Room name is required';
    } elseif (strlen(trim($data['name'])) > 255) {
        $errors[] = 'Room name cannot exceed 255 characters';
    }
    
    // Validate building
    if (empty(trim($data['building']))) {
        $errors[] = 'Building is required';
    } elseif (strlen(trim($data['building'])) > 255) {
        $errors[] = 'Building name cannot exceed 255 characters';
    }
    
    // Validate capacity
    if (!isset($data['capacity']) || !is_numeric($data['capacity']) || $data['capacity'] <= 0) {
        $errors[] = 'Capacity must be a positive number';
    } elseif ($data['capacity'] > 1000) {
        $errors[] = 'Capacity cannot exceed 1000';
    }
    
    // Validate status
    if (!empty($data['status']) && !in_array($data['status'], ['available', 'occupied'])) {
        $errors[] = 'Invalid room status';
    }
    
    return $errors;
}
?>
