<?php
/**
 * KBC Plus - Production Configuration for InfinityFree
 * Replace backend/config.php with this file content when deploying
 */

// === PRODUCTION DATABASE CONFIGURATION ===
// Update these values with your InfinityFree database details
define('DB_HOST', 'localhost'); // Usually localhost for InfinityFree
define('DB_USERNAME', 'YOUR_INFINITYFREE_DB_USER'); // Your database username (format: epiz_XXXXXXX_dbname)
define('DB_PASSWORD', 'YOUR_INFINITYFREE_DB_PASSWORD'); // Your database password
define('DB_NAME', 'YOUR_INFINITYFREE_DB_NAME'); // Your database name (format: epiz_XXXXXXX_dbname)
define('DB_PORT', '3306');

// === SITE CONFIGURATION ===
// Update with your actual domain
define('BASE_URL', 'https://yourdomain.infinityfreeapp.com/'); // Your InfinityFree subdomain
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

// === DATABASE CONNECTION ===
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            // InfinityFree specific optimizations
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_TIMEOUT => 30, // Connection timeout
        ]
    );
} catch (PDOException $e) {
    // Log error without exposing sensitive information
    error_log("Database connection failed: " . $e->getMessage());
    
    // Show generic error to users
    die("Service temporarily unavailable. Please try again later.");
}

// === CORS CONFIGURATION ===
// Configure CORS for your domain only
$allowed_origins = [
    'https://yourdomain.infinityfreeapp.com',
    'https://www.yourdomain.infinityfreeapp.com'
    // Add your custom domain if you have one
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
} else {
    header("Access-Control-Allow-Origin: " . $allowed_origins[0]);
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
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

// === UPLOAD CONFIGURATION ===
// InfinityFree has strict limits
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');
ini_set('max_execution_time', 30); // InfinityFree limit
ini_set('memory_limit', '64M'); // InfinityFree limit

// === HELPER FUNCTIONS ===

/**
 * Get base URL dynamically
 */
function getBaseUrl() {
    return BASE_URL;
}

/**
 * Sanitize file paths for InfinityFree
 */
function sanitizeFilePath($path) {
    // Remove any potentially dangerous characters
    $path = preg_replace('/[^a-zA-Z0-9\/\-_\.]/', '', $path);
    return $path;
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

// === CONSTANTS FOR API RESPONSES ===
define('API_SUCCESS', 'success');
define('API_ERROR', 'error');
define('API_UNAUTHORIZED', 'unauthorized');

// === YOUTUBE API CONFIGURATION (if used) ===
// Add your YouTube API key here if you're using YouTube features
// define('YOUTUBE_API_KEY', 'your_youtube_api_key_here');

?>
