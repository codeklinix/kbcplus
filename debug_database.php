<?php
// Debug script to check database connection and data
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>KBC+ Database Debug</h2>";

try {
    // Try to include config
    require_once 'backend/config.php';
    echo "✅ Config file loaded successfully<br>";
    
    // Check if PDO connection exists
    if (isset($pdo)) {
        echo "✅ PDO connection established<br>";
        
        // Test connection with a simple query
        $result = $pdo->query("SELECT 1 as test");
        if ($result) {
            echo "✅ Database connection working<br>";
        } else {
            echo "❌ Database query failed<br>";
        }
        
        // Check if tables exist
        $tables = ['radio_stations', 'tv_streams', 'podcasts', 'news_articles', 'users'];
        
        foreach ($tables as $table) {
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "✅ Table '$table': $count records<br>";
            } catch (Exception $e) {
                echo "❌ Table '$table': " . $e->getMessage() . "<br>";
            }
        }
        
        // Show database info
        $dbInfo = $pdo->query("SELECT VERSION() as version")->fetch();
        echo "<br><strong>Database Version:</strong> " . $dbInfo['version'] . "<br>";
        
        // Test API endpoints
        echo "<br><h3>Testing API Endpoints:</h3>";
        
        // Test radio API
        $radioTest = file_get_contents('https://kbcplus.page.gd/backend/api/radio.php');
        if ($radioTest) {
            $radioData = json_decode($radioTest, true);
            if (is_array($radioData)) {
                echo "✅ Radio API: " . count($radioData) . " stations found<br>";
            } else {
                echo "❌ Radio API: Invalid JSON response<br>";
                echo "Response: " . substr($radioTest, 0, 200) . "...<br>";
            }
        } else {
            echo "❌ Radio API: No response<br>";
        }
        
    } else {
        echo "❌ No PDO connection found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "❌ File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<br><h3>Configuration Check:</h3>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
echo "DB_USERNAME: " . (defined('DB_USERNAME') ? DB_USERNAME : 'Not defined') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'Not defined') . "<br>";

echo "<br><h3>Server Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

echo "<br><p><a href='index.html'>Back to website</a></p>";
?>
