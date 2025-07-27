<?php
/**
 * KBC Plus - MariaDB Configuration for InfinityFree
 * Optimized for MariaDB compatibility and shared hosting
 */

// === PRODUCTION DATABASE CONFIGURATION ===
// Update these values with your InfinityFree database details
define('DB_HOST', 'sql107.infinityfree.com'); // Usually localhost for InfinityFree
define('DB_USERNAME', 'if0_39560816'); // Your database username
define('DB_PASSWORD', 'LTVzPbhJW0'); // Your database password
define('DB_NAME', 'if0_39560816_mariadb'); // Your database name
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// === SITE CONFIGURATION ===
// Update with your actual domain
define('BASE_URL', 'https://kbcplus.page.gd/'); // Your actual domain
define('ASSETS_URL', BASE_URL . 'assets/');
define('SITE_NAME', 'KBC Plus');

// === PRODUCTION SETTINGS ===
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);

// Error handling for production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// === MARIADB CONNECTION ===
try {
    // MariaDB-specific PDO options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_TIMEOUT => 30,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci",
        // MariaDB specific optimizations
        PDO::MYSQL_ATTR_COMPRESS => true,
        PDO::ATTR_PERSISTENT => false, // Disable persistent connections for shared hosting
    ];

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
    
    // Set MariaDB specific settings for better compatibility
    $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    $pdo->exec("SET SESSION time_zone = '+03:00'"); // East Africa Time
    
} catch (PDOException $e) {
    // Log error without exposing sensitive information
    error_log("Database connection failed: " . $e->getMessage());
    
    // Show generic error to users
    die("Service temporarily unavailable. Please try again later.");
}

// === CORS CONFIGURATION ===
$allowed_origins = [
    'https://kbcplus.page.gd',
    'http://kbcplus.page.gd' // For development/testing
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
} else {
    header("Access-Control-Allow-Origin: " . $allowed_origins[0]);
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// === SECURITY HEADERS ===
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// === TIMEZONE CONFIGURATION ===
date_default_timezone_set('Africa/Nairobi');

// === SESSION CONFIGURATION ===
// Secure session settings for production
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable only if using HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// === INFINITYFREE LIMITS ===
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');
ini_set('max_execution_time', 30);
ini_set('memory_limit', '64M');

// === HELPER FUNCTIONS ===

/**
 * Execute a query with error handling
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query error: " . $e->getMessage() . " SQL: " . $sql);
        return false;
    }
}

/**
 * Get base URL dynamically
 */
function getBaseUrl() {
    return BASE_URL;
}

/**
 * Check if we're in production
 */
function isProduction() {
    return ENVIRONMENT === 'production';
}

/**
 * Log errors in production-safe way
 */
function logError($message, $context = []) {
    if (isProduction()) {
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
        if (!empty($context)) {
            $logMessage .= ' - Context: ' . json_encode($context);
        }
        error_log($logMessage);
    }
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Sanitize input for MariaDB
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// === CONSTANTS FOR API RESPONSES ===
define('API_SUCCESS', 'success');
define('API_ERROR', 'error');
define('API_UNAUTHORIZED', 'unauthorized');

// === DATABASE HEALTH CHECK ===
function checkDatabaseHealth() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
    } catch (PDOException $e) {
        logError("Database health check failed: " . $e->getMessage());
        return false;
    }
}

?>
