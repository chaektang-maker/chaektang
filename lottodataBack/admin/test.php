<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing PHP...<br>";

// Test database connection
echo "Testing database connection...<br>";
require_once '../config/database.php';

try {
    $conn = getDBConnection();
    echo "Database connection: OK<br>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as total FROM lotto_data");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Total records: " . $row['total'] . "<br>";
    } else {
        echo "Query error: " . $conn->error . "<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "Test completed!";
?>
