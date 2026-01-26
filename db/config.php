<?php
// Database Configuration
// Use Docker environment variables if they exist, otherwise fallback to XAMPP defaults
$host = getenv('DB_HOST') ?: "localhost";
$port = getenv('DB_PORT') ?: "3306";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "";
$dbname = getenv('DB_NAME') ?: "todo_db";

// Enable error reporting for debugging (remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname, (int) $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("<h3>Database Connection Error</h3><p>Could not connect to the database.</p><p><b>Debug Info:</b> Host: $host | Port: $port | User: $user</p><p>Error: " . $e->getMessage() . "</p>");
}
?>