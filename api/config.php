<?php
// Database configuration - Local XAMPP setup

// Local XAMPP MySQL configuration:
define('DB_HOST', 'localhost'); // XAMPP MySQL host
define('DB_USERNAME', 'root'); // Default XAMPP MySQL username
define('DB_PASSWORD', ''); // Default XAMPP MySQL password (empty)
define('DB_NAME', 'kbcplus'); // Local database name
define('DB_PORT', '3306');

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Base URL configuration - Local XAMPP setup
define('BASE_URL', 'http://localhost/kbcplus/'); // Local development URL
define('ASSETS_URL', BASE_URL . 'assets/');

// Enable CORS for API requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
?>
