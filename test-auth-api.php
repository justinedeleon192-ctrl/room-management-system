<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

try {
    $db = new Database();

    $env = [
        'DB_HOST' => getenv('DB_HOST') ?: null,
        'DB_NAME' => getenv('DB_NAME') ?: null,
        'DB_USER' => getenv('DB_USER') ?: null,
        'DB_PASS_set' => (getenv('DB_PASS') !== false && getenv('DB_PASS') !== '') ? true : false,
        'DB_CHARSET' => getenv('DB_CHARSET') ?: null,
    ];

    $tables = [];
    try {
        $tables = $db->fetchAll("SHOW TABLES");
    } catch (Exception $e) {
        $tables = ['error' => $e->getMessage()];
    }

    $studentCount = null;
    $instructorCount = null;
    try {
        $row = $db->fetch("SELECT COUNT(*) as c FROM students");
        $studentCount = $row ? (int)$row['c'] : 0;
    } catch (Exception $e) {
        $studentCount = 'error: ' . $e->getMessage();
    }

    try {
        $row = $db->fetch("SELECT COUNT(*) as c FROM instructors");
        $instructorCount = $row ? (int)$row['c'] : 0;
    } catch (Exception $e) {
        $instructorCount = 'error: ' . $e->getMessage();
    }

    echo json_encode([
        'ok' => $db->isConnected(),
        'env' => $env,
        'students_count' => $studentCount,
        'instructors_count' => $instructorCount,
        'tables_preview' => array_slice($tables, 0, 10),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

