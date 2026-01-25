<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'luckyd');

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        // ถ้าเป็น AJAX request ให้ส่ง JSON error
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database connection error: ' . $e->getMessage()]);
            exit;
        }
        // ถ้าไม่ใช่ AJAX ให้แสดง error ปกติ
        die("Database connection error: " . $e->getMessage());
    }
}

// Test connection
function testConnection() {
    $conn = getDBConnection();
    if ($conn) {
        echo "Database connection successful!<br>";
        $conn->close();
        return true;
    }
    return false;
}
?>
