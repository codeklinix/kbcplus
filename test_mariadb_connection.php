<?php
/**
 * KBC Plus - MariaDB Connection Test
 * Use this file to test your database connection on InfinityFree
 * DELETE THIS FILE after successful testing!
 */

// Enable error display for testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>KBC Plus - MariaDB Connection Test</h2>";
echo "<p><strong>⚠️ DELETE THIS FILE AFTER TESTING!</strong></p>";

// Database configuration - UPDATE THESE VALUES
$db_host = 'localhost';
$db_username = 'YOUR_INFINITYFREE_DB_USER'; // Update this
$db_password = 'YOUR_INFINITYFREE_DB_PASSWORD'; // Update this
$db_name = 'YOUR_INFINITYFREE_DB_NAME'; // Update this
$db_port = '3306';
$db_charset = 'utf8mb4';

echo "<h3>Testing Environment</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";

echo "<h3>Database Connection Test</h3>";

try {
    // Test basic connection first
    echo "<p>Testing basic connection...</p>";
    
    $dsn = "mysql:host=$db_host;port=$db_port;charset=$db_charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_TIMEOUT => 30,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $db_charset COLLATE utf8mb4_unicode_ci",
    ];
    
    $pdo = new PDO($dsn, $db_username, $db_password, $options);
    echo "<p style='color: green;'>✓ Basic connection successful!</p>";
    
    // Test database selection
    echo "<p>Testing database selection...</p>";
    $pdo->exec("USE `$db_name`");
    echo "<p style='color: green;'>✓ Database '$db_name' selected successfully!</p>";
    
    // Test MariaDB version
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "<p>Database Version: " . $version['version'] . "</p>";
    
    // Test table creation (basic test)
    echo "<p>Testing table operations...</p>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT AUTO_INCREMENT PRIMARY KEY, test_field VARCHAR(255)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Test table created successfully!</p>";
    
    // Test insert
    $stmt = $pdo->prepare("INSERT INTO test_table (test_field) VALUES (?)");
    $stmt->execute(['Test data from MariaDB connection test']);
    echo "<p style='color: green;'>✓ Data insertion successful!</p>";
    
    // Test select
    $stmt = $pdo->query("SELECT * FROM test_table ORDER BY id DESC LIMIT 1");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✓ Data retrieval successful! Last entry: " . htmlspecialchars($result['test_field']) . "</p>";
    
    // Clean up test table
    $pdo->exec("DROP TABLE test_table");
    echo "<p style='color: green;'>✓ Test table cleaned up!</p>";
    
    echo "<h3 style='color: green;'>🎉 All Tests Passed!</h3>";
    echo "<p><strong>Your MariaDB connection is working properly.</strong></p>";
    echo "<p>Next steps:</p>";
    echo "<ol>";
    echo "<li>Import your database schema using phpMyAdmin</li>";
    echo "<li>Update your config.php file with these database credentials</li>";
    echo "<li><strong>DELETE THIS FILE for security!</strong></li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Connection Failed!</h3>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Common solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Verify your database credentials are correct</li>";
    echo "<li>Make sure the database exists in your InfinityFree control panel</li>";
    echo "<li>Check that the database user has proper permissions</li>";
    echo "<li>Ensure you're using the correct database name format</li>";
    echo "</ul>";
    
    // Show PDO error info if available
    $errorInfo = $e->errorInfo ?? null;
    if ($errorInfo) {
        echo "<p><strong>Error Details:</strong></p>";
        echo "<pre>" . print_r($errorInfo, true) . "</pre>";
    }
}

echo "<hr>";
echo "<p><em>This test file is for debugging purposes only. Please delete it after successful testing!</em></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
h3 { color: #666; }
p { margin: 10px 0; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>
