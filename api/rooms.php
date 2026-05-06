<?php
/**
 * Room Management API Endpoints
 * Handles CRUD operations for rooms
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

// Require authentication for all room operations
if (!$sessionManager->isLoggedIn()) {
    sendErrorResponse('Authentication required', 401);
}

// Route the request
switch ($method) {
    case 'GET':
        switch ($action) {
            case 'list':
                handleGetRooms();
                break;
            case 'get':
                handleGetRoom();
                break;
            case 'stats':
                handleGetRoomStats();
                break;
            default:
                handleGetRooms(); // Default to listing rooms
        }
        break;
    case 'POST':
        switch ($action) {
            case 'create':
                handleCreateRoom();
                break;
            default:
                sendErrorResponse('Invalid action', 400);
        }
        break;
    case 'PUT':
        switch ($action) {
            case 'update':
                handleUpdateRoom();
                break;
            case 'toggle':
                handleToggleRoomStatus();
                break;
            default:
                sendErrorResponse('Invalid action', 400);
        }
        break;
    case 'DELETE':
        switch ($action) {
            case 'delete':
                handleDeleteRoom();
                break;
            default:
                sendErrorResponse('Invalid action', 400);
        }
        break;
    default:
        sendErrorResponse('Method not allowed', 405);
}

/**
 * Handle getting all rooms
 */
function handleGetRooms() {
    global $database;
    
    try {
        // Get query parameters for filtering
        $search = sanitize($_GET['search'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $building = sanitize($_GET['building'] ?? '');
        
        // Build base query
        $sql = "SELECT id, name, building, capacity, status, created_at, updated_at FROM rooms WHERE 1=1";
        $params = [];
        
        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR building LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Add status filter
        if (!empty($status) && in_array($status, ['available', 'occupied'])) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        // Add building filter
        if (!empty($building)) {
            $sql .= " AND building LIKE ?";
            $params[] = "%{$building}%";
        }
        
        // Add ordering
        $sql .= " ORDER BY building, name";
        
        $rooms = $database->fetchAll($sql, $params);
        
        sendSuccessResponse('Rooms retrieved successfully', ['rooms' => $rooms]);
        
    } catch (Exception $e) {
        sendErrorResponse('Error retrieving rooms: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle getting a single room
 */
function handleGetRoom() {
    global $database;
    
    $roomId = (int)($_GET['id'] ?? 0);
    
    if ($roomId <= 0) {
        sendErrorResponse('Invalid room ID', 400);
    }
    
    try {
        $room = $database->fetch(
            "SELECT id, name, building, capacity, status, created_at, updated_at FROM rooms WHERE id = ?",
            [$roomId]
        );
        
        if (!$room) {
            sendErrorResponse('Room not found', 404);
        }
        
        sendSuccessResponse('Room retrieved successfully', ['room' => $room]);
        
    } catch (Exception $e) {
        sendErrorResponse('Error retrieving room: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle getting room statistics
 */
function handleGetRoomStats() {
    global $database;
    
    try {
        $stats = $database->fetch(
            "SELECT 
                COUNT(*) as total_rooms,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms,
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms,
                SUM(capacity) as total_capacity
            FROM rooms"
        );
        
        // Get buildings list
        $buildings = $database->fetchAll("SELECT DISTINCT building FROM rooms ORDER BY building");
        $stats['buildings'] = array_column($buildings, 'building');
        
        sendSuccessResponse('Room statistics retrieved', ['stats' => $stats]);
        
    } catch (Exception $e) {
        sendErrorResponse('Error retrieving room statistics: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle creating a new room
 */
function handleCreateRoom() {
    global $database, $sessionManager;
    
    // Only instructors can create rooms
    if (!$sessionManager->hasRole('instructor')) {
        sendErrorResponse('Only instructors can create rooms', 403);
    }
    
    // Get JSON input
    $data = getJsonInput();
    
    // Validate input
    $errors = validateRoomData($data);
    if (!empty($errors)) {
        sendErrorResponse('Validation failed: ' . implode(', ', $errors), 400);
    }
    
    try {
        // Insert new room
        $roomId = $database->insert(
            "INSERT INTO rooms (name, building, capacity, status) VALUES (?, ?, ?, ?)",
            [
                sanitize($data['name']),
                sanitize($data['building']),
                (int)$data['capacity'],
                sanitize($data['status'] ?? 'available')
            ]
        );
        
        if ($roomId) {
            // Get the created room
            $room = $database->fetch(
                "SELECT id, name, building, capacity, status, created_at, updated_at FROM rooms WHERE id = ?",
                [$roomId]
            );
            
            sendSuccessResponse('Room created successfully', ['room' => $room]);
        } else {
            sendErrorResponse('Failed to create room', 500);
        }
        
    } catch (Exception $e) {
        sendErrorResponse('Error creating room: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle updating a room
 */
function handleUpdateRoom() {
    global $database, $sessionManager;
    
    // Only instructors can update rooms
    if (!$sessionManager->hasRole('instructor')) {
        sendErrorResponse('Only instructors can update rooms', 403);
    }
    
    $roomId = (int)($_GET['id'] ?? 0);
    
    if ($roomId <= 0) {
        sendErrorResponse('Invalid room ID', 400);
    }
    
    // Get JSON input
    $data = getJsonInput();
    
    // Validate input
    $errors = validateRoomData($data);
    if (!empty($errors)) {
        sendErrorResponse('Validation failed: ' . implode(', ', $errors), 400);
    }
    
    try {
        // Check if room exists
        $existingRoom = $database->fetch("SELECT id FROM rooms WHERE id = ?", [$roomId]);
        if (!$existingRoom) {
            sendErrorResponse('Room not found', 404);
        }
        
        // Update room
        $affectedRows = $database->update(
            "UPDATE rooms SET name = ?, building = ?, capacity = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [
                sanitize($data['name']),
                sanitize($data['building']),
                (int)$data['capacity'],
                sanitize($data['status'] ?? 'available'),
                $roomId
            ]
        );
        
        if ($affectedRows > 0) {
            // Get the updated room
            $room = $database->fetch(
                "SELECT id, name, building, capacity, status, created_at, updated_at FROM rooms WHERE id = ?",
                [$roomId]
            );
            
            sendSuccessResponse('Room updated successfully', ['room' => $room]);
        } else {
            sendErrorResponse('No changes made to room', 400);
        }
        
    } catch (Exception $e) {
        sendErrorResponse('Error updating room: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle toggling room status
 */
function handleToggleRoomStatus() {
    global $database, $sessionManager;
    
    // Only instructors can toggle room status
    if (!$sessionManager->hasRole('instructor')) {
        sendErrorResponse('Only instructors can change room status', 403);
    }
    
    $roomId = (int)($_GET['id'] ?? 0);
    
    if ($roomId <= 0) {
        sendErrorResponse('Invalid room ID', 400);
    }
    
    try {
        // Check if room exists
        $room = $database->fetch("SELECT id, status FROM rooms WHERE id = ?", [$roomId]);
        if (!$room) {
            sendErrorResponse('Room not found', 404);
        }
        
        // Toggle status
        $newStatus = $room['status'] === 'available' ? 'occupied' : 'available';
        
        $affectedRows = $database->update(
            "UPDATE rooms SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$newStatus, $roomId]
        );
        
        if ($affectedRows > 0) {
            // Get the updated room
            $updatedRoom = $database->fetch(
                "SELECT id, name, building, capacity, status, created_at, updated_at FROM rooms WHERE id = ?",
                [$roomId]
            );
            
            sendSuccessResponse('Room status updated successfully', ['room' => $updatedRoom]);
        } else {
            sendErrorResponse('Failed to update room status', 500);
        }
        
    } catch (Exception $e) {
        sendErrorResponse('Error toggling room status: ' . $e->getMessage(), 500);
    }
}

/**
 * Handle deleting a room
 */
function handleDeleteRoom() {
    global $database, $sessionManager;
    
    // Only instructors can delete rooms
    if (!$sessionManager->hasRole('instructor')) {
        sendErrorResponse('Only instructors can delete rooms', 403);
    }
    
    $roomId = (int)($_GET['id'] ?? 0);
    
    if ($roomId <= 0) {
        sendErrorResponse('Invalid room ID', 400);
    }
    
    try {
        // Check if room exists
        $room = $database->fetch("SELECT id, name FROM rooms WHERE id = ?", [$roomId]);
        if (!$room) {
            sendErrorResponse('Room not found', 404);
        }
        
        // Delete room
        $affectedRows = $database->delete("DELETE FROM rooms WHERE id = ?", [$roomId]);
        
        if ($affectedRows > 0) {
            sendSuccessResponse('Room deleted successfully', ['room' => $room]);
        } else {
            sendErrorResponse('Failed to delete room', 500);
        }
        
    } catch (Exception $e) {
        sendErrorResponse('Error deleting room: ' . $e->getMessage(), 500);
    }
}
?>
