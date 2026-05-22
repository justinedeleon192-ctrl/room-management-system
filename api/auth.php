<?php
/**
 * Authentication API Endpoints
 * Handles user registration, login, logout, and session management
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Enable CORS for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize database and session
try {
    $database = new Database();
    $sessionManager = new SessionManager($database);
} catch (Exception $e) {
    sendErrorResponse('Database connection failed: ' . $e->getMessage(), 500);
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Support friendly routes like /api/login, /api/register, /api/user, /api/logout
// by inferring action from the requested path when action query param is not provided.
if (empty($action)) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $base = rtrim(dirname(__DIR__), '/\\'); // .../api/.. = project root
    $path = $uriPath;

    // Examples:
    // /api/login
    // /api/register
    // /api/user
    // /api/logout
    if (preg_match('#/api/(login|register|user|logout)$#', $path, $m)) {
        $action = $m[1];
    }
}

// Route the request
switch ($method) {
    case 'POST':
        switch ($action) {
            case 'register':
                handleRegister();
                break;
            case 'login':
                handleLogin();
                break;
            case 'logout':
                handleLogout();
                break;
            default:
                sendErrorResponse('Invalid action', 400);
        }
        break;
    case 'GET':
        switch ($action) {
            case 'user':
                handleGetCurrentUser();
                break;
            case 'check':
                handleCheckAuth();
                break;
            default:
                sendErrorResponse('Invalid action', 400);
        }
        break;
    default:
        sendErrorResponse('Method not allowed', 405);
}

/**
 * Handle user registration
 */
function handleRegister() {
    global $database, $sessionManager;
    
    // Get JSON input
    $data = getJsonInput();
    
    // Validate input
    $errors = validateRegistration($data);
    if (!empty($errors)) {
        sendErrorResponse('Validation failed: ' . implode(', ', $errors), 400);
    }
    
    try {
        // Check if email or username already exists
        $emailCheck = $database->exists(
            "SELECT id FROM students WHERE email = ? UNION SELECT id FROM instructors WHERE email = ?",
            [$data['email'], $data['email']]
        );
        
        $usernameCheck = $database->exists(
            "SELECT id FROM students WHERE username = ? UNION SELECT id FROM instructors WHERE username = ?",
            [$data['username'], $data['username']]
        );
        
        if ($emailCheck) {
            sendErrorResponse('Email is already registered', 409);
        }
        
        if ($usernameCheck) {
            sendErrorResponse('Username is already taken', 409);
        }
        
        // Hash password
        $hashedPassword = hashPassword($data['password']);
        
        // Insert into appropriate table based on role
        $table = $data['role'] === 'student' ? 'students' : 'instructors';
        $userId = $database->insert(
            "INSERT INTO {$table} (fullname, email, username, password) VALUES (?, ?, ?, ?)",
            [
                sanitize($data['fullname']),
                sanitize($data['email']),
                sanitize($data['username']),
                $hashedPassword
            ]
        );
        
        if ($userId) {
            // Get user data for session
            $user = $database->fetch(
                "SELECT id, fullname, email, username, ? as role FROM {$table} WHERE id = ?",
                [$data['role'], $userId]
            );
            
            // Auto-login after registration
            $sessionManager->login($user);
            
            sendSuccessResponse('Registration successful', [
                'user' => $user
            ]);
        } else {
            sendErrorResponse('Registration failed', 500);
        }
        
    } catch (Exception $e) {
        sendErrorResponse('Registration error: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle user login
 */
function handleLogin() {
    global $database, $sessionManager;
    
    // Get JSON input
    $data = getJsonInput();
    
    // Validate input
    $errors = validateLogin($data);
    if (!empty($errors)) {
        sendErrorResponse('Validation failed: ' . implode(', ', $errors), 400);
    }
    
    try {
        // Check students table first
        $user = $database->fetch(
            "SELECT id, fullname, email, username, password, 'student' as role FROM students WHERE username = ?",
            [$data['username']]
        );
        
        // If not found in students, check instructors
        if (!$user) {
            $user = $database->fetch(
                "SELECT id, fullname, email, username, password, 'instructor' as role FROM instructors WHERE username = ?",
                [$data['username']]
            );
        }
        
        // Verify user exists and password is correct
        if (!$user || !verifyPassword($data['password'], $user['password'])) {
            sendErrorResponse('Invalid username or password', 401);
        }
        
        // Remove password from user data before sending to client
        unset($user['password']);
        
        // Create session
        $sessionManager->login($user);
        
        sendSuccessResponse('Login successful', [
            'user' => $user
        ]);
        
    } catch (Exception $e) {
        sendErrorResponse('Login error: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle user logout
 */
function handleLogout() {
    global $sessionManager;
    
    try {
        $sessionManager->logout();
        sendSuccessResponse('Logout successful');
    } catch (Exception $e) {
        sendErrorResponse('Logout error: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle getting current user
 */
function handleGetCurrentUser() {
    global $sessionManager;
    
    if (!$sessionManager->isLoggedIn()) {
        sendErrorResponse('Not authenticated', 401);
    }
    
    $user = $sessionManager->getCurrentUser();
    sendSuccessResponse('User data retrieved', ['user' => $user]);
}

/**
 * Handle authentication check
 */
function handleCheckAuth() {
    global $sessionManager;
    
    if (!$sessionManager->isLoggedIn() || $sessionManager->isSessionExpired()) {
        sendErrorResponse('Not authenticated or session expired', 401);
    }
    
    $user = $sessionManager->getCurrentUser();
    $timeout = $sessionManager->getSessionTimeout();
    
    sendSuccessResponse('Authentication valid', [
        'user' => $user,
        'timeout' => $timeout
    ]);
}
?>
