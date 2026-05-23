<?php
// ===== api/rooms.php =====
// Backend API for school_system database

$host = 'localhost';
$db   = 'school_system';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB Connection Failed"]);
    exit;
}

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

header("Content-Type: application/json");

switch ($action) {
    case 'list':
        $res = $conn->query("SELECT * FROM rooms ORDER BY id DESC");
        echo json_encode(["success" => true, "data" => ["rooms" => $res->fetch_all(MYSQLI_ASSOC)]]);
        break;

    case 'create':
        // Prepare Statement for exact schema columns
        $stmt = $conn->prepare("INSERT INTO rooms (name, capacity, status, booking_date, start_time, end_time, notes, created_at, updated_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        // Map to your schema
        $stmt->bind_param("sisssss", 
            $data['name'], 
            $data['capacity'], 
            $data['status'], 
            $data['booking_date'], 
            $data['start_time'], 
            $data['end_time'], 
            $data['notes']
        );
        
        $success = $stmt->execute();
        echo json_encode(["success" => $success, "message" => $success ? "Room saved!" : $conn->error]);
        break;

    case 'update':
        $id = $_GET['id'];
        $stmt = $conn->prepare("UPDATE rooms SET name=?, capacity=?, status=?, booking_date=?, start_time=?, end_time=?, notes=?, updated_at=NOW() WHERE id=?");
        
        $stmt->bind_param("sisssssi", 
            $data['name'], 
            $data['capacity'], 
            $data['status'], 
            $data['booking_date'], 
            $data['start_time'], 
            $data['end_time'], 
            $data['notes'],
            $id
        );
        
        $success = $stmt->execute();
        echo json_encode(["success" => $success, "message" => $success ? "Updated!" : $conn->error]);
        break;

    case 'delete':
        $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $success = $stmt->execute();
        echo json_encode(["success" => $success]);
        break;

    case 'get':
        $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $room = $stmt->get_result()->fetch_assoc();
        echo json_encode(["success" => true, "data" => ["room" => $room]]);
        break;
}