<?php
// api.php - Backend API for Room Management System

// ==================== CONFIGURATION ====================
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// ==================== DATABASE CONNECTION ====================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// ==================== HEADERS ====================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==================== HELPER FUNCTIONS ====================
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true);
}

// ==================== ROUTER ====================
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($endpoint) {
    case 'rooms':
        handleRooms($method);
        break;
    case 'instructors':
        handleInstructors($method);
        break;
    case 'bookings':
        handleBookings($method);
        break;
    case 'debug':
        handleDebug();
        break;
    default:
        sendResponse(['error' => 'Invalid endpoint'], 404);
}

// ==================== DEBUG ====================
function handleDebug() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT id, name, capacity, status, booking_date, start_time, end_time, notes FROM rooms ORDER BY id");
        sendResponse(['success' => true, 'rooms' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

// ==================== ROOMS HANDLER ====================
function handleRooms($method) {
    global $pdo;
    
    switch ($method) {
        case 'GET':
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            
            try {
                if ($status) {
                    $stmt = $pdo->prepare("SELECT id, name, capacity, status FROM rooms WHERE status = :status ORDER BY name");
                    $stmt->execute([':status' => $status]);
                } else {
                    $stmt = $pdo->query("SELECT id, name, capacity, status FROM rooms ORDER BY name");
                }
                sendResponse(['success' => true, 'rooms' => $stmt->fetchAll()]);
            } catch (PDOException $e) {
                sendResponse(['error' => 'Failed to fetch rooms: ' . $e->getMessage()], 500);
            }
            break;
        
        case 'POST':
            $data = getRequestBody();
            
            if (!isset($data['name']) || !isset($data['capacity'])) {
                sendResponse(['error' => 'Room name and capacity are required'], 400);
            }
            
            $name = trim($data['name']);
            $capacity = (int)$data['capacity'];
            
            if ($capacity < 1) {
                sendResponse(['error' => 'Capacity must be at least 1'], 400);
            }
            
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE name = :name AND status = 'available'");
                $stmt->execute([':name' => $name]);
                
                if ($stmt->fetchColumn() > 0) {
                    sendResponse(['error' => 'A room with that name already exists'], 409);
                }
                
                $stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, status) VALUES (:name, :capacity, 'available')");
                $stmt->execute([':name' => $name, ':capacity' => $capacity]);
                
                sendResponse([
                    'success' => true,
                    'message' => 'Room added successfully',
                    'id' => $pdo->lastInsertId(),
                    'name' => $name,
                    'capacity' => $capacity
                ], 201);
            } catch (PDOException $e) {
                sendResponse(['error' => 'Failed to add room: ' . $e->getMessage()], 500);
            }
            break;
        
        case 'DELETE':
            $data = getRequestBody();
            
            if (!isset($data['id'])) {
                sendResponse(['error' => 'Room ID is required'], 400);
            }
            
            $id = (int)$data['id'];
            
            try {
                // Check if room exists
                $stmt = $pdo->prepare("SELECT id, status FROM rooms WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $room = $stmt->fetch();
                
                if (!$room) {
                    sendResponse(['error' => 'Room not found'], 404);
                }
                
                // If occupied, delete it (booking removal)
                if ($room['status'] === 'occupied') {
                    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                    sendResponse(['success' => true, 'message' => 'Booking removed and room deleted']);
                } else {
                    // Delete available room
                    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id AND status = 'available'");
                    $stmt->execute([':id' => $id]);
                    
                    if ($stmt->rowCount() > 0) {
                        sendResponse(['success' => true, 'message' => 'Room deleted successfully']);
                    } else {
                        sendResponse(['error' => 'Cannot delete room'], 400);
                    }
                }
            } catch (PDOException $e) {
                sendResponse(['error' => 'Failed to delete room: ' . $e->getMessage()], 500);
            }
            break;
        
        default:
            sendResponse(['error' => 'Method not allowed'], 405);
    }
}

// ==================== INSTRUCTORS HANDLER ====================
function handleInstructors($method) {
    global $pdo;
    
    if ($method === 'GET') {
        try {
            $stmt = $pdo->query("SELECT id, fullname, email, username FROM instructors ORDER BY fullname");
            sendResponse(['success' => true, 'instructors' => $stmt->fetchAll()]);
        } catch (PDOException $e) {
            sendResponse(['error' => 'Failed to fetch instructors: ' . $e->getMessage()], 500);
        }
    } else {
        sendResponse(['error' => 'Method not allowed'], 405);
    }
}

// ==================== BOOKINGS HANDLER ====================
function handleBookings($method) {
    global $pdo;
    
    switch ($method) {
        case 'GET':
            try {
                $stmt = $pdo->query("
                    SELECT id, name, capacity, status, booking_date, start_time, end_time, notes, created_at, updated_at
                    FROM rooms
                    WHERE status = 'occupied'
                    ORDER BY booking_date, start_time
                ");
                $bookings = $stmt->fetchAll();
                
                foreach ($bookings as &$booking) {
                    $instructorInfo = [
                        'instructor_id' => null,
                        'instructor_fullname' => null,
                        'instructor_email' => null
                    ];
                    
                    if ($booking['notes'] && preg_match('/\[INSTRUCTOR:(.*?)\|(.*?)\|(.*?)\]/', $booking['notes'], $matches)) {
                        $instructorInfo = [
                            'instructor_id' => $matches[3],
                            'instructor_fullname' => $matches[1],
                            'instructor_email' => $matches[2]
                        ];
                        $booking['notes'] = trim(preg_replace('/\[INSTRUCTOR:.*?\]/', '', $booking['notes']));
                    }
                    
                    $booking = array_merge($booking, $instructorInfo);
                }
                
                sendResponse(['success' => true, 'bookings' => $bookings]);
            } catch (PDOException $e) {
                sendResponse(['error' => 'Failed to fetch bookings: ' . $e->getMessage()], 500);
            }
            break;
        
        case 'POST':
            $data = getRequestBody();
            
            if (!isset($data['name']) || !isset($data['booking_date']) || !isset($data['start_time']) || !isset($data['end_time'])) {
                sendResponse(['error' => 'Room, date, start time, and end time are required'], 400);
            }
            
            $name = trim($data['name']);
            $booking_date = $data['booking_date'];
            $start_time = $data['start_time'];
            $end_time = $data['end_time'];
            $instructor_id = isset($data['instructor_id']) ? (int)$data['instructor_id'] : null;
            
            if (strtotime($end_time) <= strtotime($start_time)) {
                sendResponse(['error' => 'End time must be after start time'], 400);
            }
            
            try {
                // Get instructor info
                $instructor_fullname = '';
                $instructor_email = '';
                if ($instructor_id) {
                    $stmt = $pdo->prepare("SELECT fullname, email FROM instructors WHERE id = :id");
                    $stmt->execute([':id' => $instructor_id]);
                    $instructor = $stmt->fetch();
                    if ($instructor) {
                        $instructor_fullname = $instructor['fullname'];
                        $instructor_email = $instructor['email'];
                    }
                }
                
                // Build notes
                $userNotes = isset($data['notes']) ? trim($data['notes']) : '';
                $notes = '';
                if ($instructor_id) {
                    $notes = "[INSTRUCTOR:{$instructor_fullname}|{$instructor_email}|{$instructor_id}] ";
                }
                $notes .= $userNotes;
                
                // Get original room capacity
                $stmt = $pdo->prepare("SELECT capacity FROM rooms WHERE name = :name AND status = 'available' LIMIT 1");
                $stmt->execute([':name' => $name]);
                $originalRoom = $stmt->fetch();
                
                if (!$originalRoom) {
                    sendResponse(['error' => 'Room not available'], 400);
                }
                
                // Check conflicts
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM rooms 
                    WHERE name = :name AND booking_date = :booking_date AND status = 'occupied'
                    AND ((start_time < :end_time AND end_time > :start_time))
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':booking_date' => $booking_date,
                    ':start_time' => $start_time,
                    ':end_time' => $end_time
                ]);
                
                if ($stmt->fetchColumn() > 0) {
                    sendResponse(['error' => 'Room is already booked for this time slot'], 409);
                }
                
                // Duplicate room as booking
                $stmt = $pdo->prepare("
                    INSERT INTO rooms (name, capacity, status, booking_date, start_time, end_time, notes) 
                    VALUES (:name, :capacity, 'occupied', :booking_date, :start_time, :end_time, :notes)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':capacity' => $originalRoom['capacity'],
                    ':booking_date' => $booking_date,
                    ':start_time' => $start_time,
                    ':end_time' => $end_time,
                    ':notes' => $notes ?: null
                ]);
                
                sendResponse([
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'id' => $pdo->lastInsertId()
                ], 201);
            } catch (PDOException $e) {
                sendResponse(['error' => 'Failed to create booking: ' . $e->getMessage()], 500);
            }
            break;
        
        case 'PUT':
            $data = getRequestBody();
            
            if (!isset($data['id'])) {
                sendResponse(['error' => 'Booking ID is required'], 400);
            }
            
            $id = (int)$data['id'];
            
            try {
                // Check if booking exists
                $stmt = $pdo->prepare("SELECT id, notes FROM rooms WHERE id = :id AND status = 'occupied'");
                $stmt->execute([':id' => $id]);
                $existing = $stmt->fetch();
                
                if (!$existing) {
                    sendResponse(['error' => 'Booking not found'], 404);
                }
                
                $fields = [];
                $params = [':id' => $id];
                
                if (isset($data['booking_date'])) {
                    $fields[] = "booking_date = :booking_date";
                    $params[':booking_date'] = $data['booking_date'];
                }
                if (isset($data['start_time'])) {
                    $fields[] = "start_time = :start_time";
                    $params[':start_time'] = $data['start_time'];
                }
                if (isset($data['end_time'])) {
                    $fields[] = "end_time = :end_time";
                    $params[':end_time'] = $data['end_time'];
                }
                
                // Handle notes and instructor
                if (isset($data['notes']) || isset($data['instructor_id'])) {
                    $existingNotes = $existing['notes'];
                    $existingNotes = preg_replace('/\[INSTRUCTOR:.*?\]/', '', $existingNotes);
                    
                    if (isset($data['instructor_id']) && $data['instructor_id']) {
                        $stmt = $pdo->prepare("SELECT fullname, email FROM instructors WHERE id = :instructor_id");
                        $stmt->execute([':instructor_id' => (int)$data['instructor_id']]);
                        $instructor = $stmt->fetch();
                        
                        if ($instructor) {
                            $instructorTag = "[INSTRUCTOR:{$instructor['fullname']}|{$instructor['email']}|{$data['instructor_id']}] ";
                            $existingNotes = $instructorTag . $existingNotes;
                        }
                    }
                    
                    if (isset($data['notes'])) {
                        $existingNotes = trim($existingNotes . ' ' . $data['notes']);
                    }
                    
                    $fields[] = "notes = :notes";
                    $params[':notes'] = $existingNotes ?: null;
                }
                
                if (empty($fields)) {
                    sendResponse(['error' => 'No fields to update'], 400);
                }
                
                $sql = "UPDATE rooms SET " . implode(', ', $fields) . " WHERE id = :id AND status = 'occupied'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                sendResponse(['success' => true, 'message' => 'Booking updated successfully']);
            } catch (PDOException $e) {
                sendResponse(['error' => 'Failed to update booking: ' . $e->getMessage()], 500);
            }
            break;
        
        case 'DELETE':
            $data = getRequestBody();
            
            if (!isset($data['id'])) {
                sendResponse(['error' => 'Booking ID is required'], 400);
            }
            
            $id = (int)$data['id'];
            
            try {
                // Check if booking exists
                $stmt = $pdo->prepare("SELECT id FROM rooms WHERE id = :id AND status = 'occupied'");
                $stmt->execute([':id' => $id]);
                
                if (!$stmt->fetch()) {
                    sendResponse(['error' => 'Booking not found'], 404);
                }
                
                // Delete the booking
                $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id AND status = 'occupied'");
                $stmt->execute([':id' => $id]);
                
                if ($stmt->rowCount() > 0) {
                    sendResponse(['success' => true, 'message' => 'Booking removed successfully']);
                } else {
                    sendResponse(['error' => 'Failed to remove booking'], 500);
                }
            } catch (PDOException $e) {
                sendResponse(['error' => 'Failed to remove booking: ' . $e->getMessage()], 500);
            }
            break;
        
        default:
            sendResponse(['error' => 'Method not allowed'], 405);
    }
}
?>