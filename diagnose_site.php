<?php
/**
 * KBC Plus - Site Diagnostic Tool
 * This will help identify what's wrong with your deployed site
 * Upload this to your site root and visit it to see what's happening
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>KBC Plus - Site Diagnostic</h1>";
echo "<p><strong>Site:</strong> https://kbcplus.page.gd/</p>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Test 1: Basic PHP Environment
echo "<h2>1. PHP Environment</h2>";
echo "<p>✓ PHP Version: " . phpversion() . "</p>";
echo "<p>✓ Server: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</p>";
echo "<p>✓ Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>";
echo "<p>✓ Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "</p>";

// Test 2: File Structure
echo "<h2>2. File Structure Check</h2>";
$required_files = [
    'index.html',
    'admin.html', 
    'login.html',
    'admin.js',
    'backend/config.php',
    'assets/css/style.css',
    'assets/js/main.js'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ Found: $file</p>";
    } else {
        echo "<p style='color: red;'>✗ Missing: $file</p>";
    }
}

// Test 3: Check if backend directory exists and is accessible
echo "<h2>3. Backend Directory Check</h2>";
if (is_dir('backend')) {
    echo "<p style='color: green;'>✓ Backend directory exists</p>";
    
    $backend_files = glob('backend/*');
    echo "<p>Backend files found:</p><ul>";
    foreach ($backend_files as $file) {
        echo "<li>" . basename($file) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Backend directory missing</p>";
}

// Test 4: Database Configuration Check
echo "<h2>4. Database Configuration Check</h2>";
if (file_exists('backend/config.php')) {
    echo "<p style='color: green;'>✓ Config file exists</p>";
    
    // Try to include and test
    try {
        ob_start();
        include 'backend/config.php';
        $output = ob_get_clean();
        
        if (isset($pdo)) {
            echo "<p style='color: green;'>✓ Database connection object created</p>";
            
            // Test the connection
            try {
                $stmt = $pdo->query("SELECT 1 as test");
                $result = $stmt->fetch();
                if ($result && $result['test'] == 1) {
                    echo "<p style='color: green;'>✓ Database connection successful</p>";
                } else {
                    echo "<p style='color: red;'>✗ Database query failed</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Database connection test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Database connection object not created</p>";
        }
        
        if (!empty($output)) {
            echo "<p style='color: orange;'>⚠ Config output (might indicate errors):</p>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Config file error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Config file missing</p>";
}

// Test 5: API Endpoints
echo "<h2>5. API Endpoints Test</h2>";
$api_files = [
    'backend/api/radio.php',
    'backend/api/tv.php', 
    'backend/api/podcasts.php',
    'backend/api/news.php'
];

foreach ($api_files as $api_file) {
    if (file_exists($api_file)) {
        echo "<p style='color: green;'>✓ Found: $api_file</p>";
        
        // Try to test the API
        try {
            $url = 'https://kbcplus.page.gd/' . $api_file;
            echo "<p>Testing: <a href='$url' target='_blank'>$url</a></p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ Could not test $api_file</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Missing: $api_file</p>";
    }
}

// Test 6: .htaccess Check
echo "<h2>6. .htaccess Check</h2>";
if (file_exists('.htaccess')) {
    echo "<p style='color: green;'>✓ .htaccess file exists</p>";
    $htaccess_size = filesize('.htaccess');
    echo "<p>File size: $htaccess_size bytes</p>";
} else {
    echo "<p style='color: red;'>✗ .htaccess file missing</p>";
}

// Test 7: Permissions Check
echo "<h2>7. Permissions Check</h2>";
$permission_checks = [
    '.' => 'Directory (root)',
    'backend' => 'Backend directory',
    'assets' => 'Assets directory',
    'logs' => 'Logs directory'
];

foreach ($permission_checks as $path => $description) {
    if (file_exists($path)) {
        $perms = fileperms($path);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "<p>$description: $perms_octal</p>";
    }
}

// Test 8: Error Log Check
echo "<h2>8. Error Log Check</h2>";
if (file_exists('logs/error.log')) {
    $error_log_content = file_get_contents('logs/error.log');
    if (!empty($error_log_content)) {
        echo "<p style='color: red;'>Recent errors found:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: scroll;'>";
        echo htmlspecialchars(substr($error_log_content, -1000)); // Last 1000 characters
        echo "</pre>";
    } else {
        echo "<p style='color: green;'>✓ No errors in log file</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Error log file not found</p>";
}

// Test 9: Homepage Content Test
echo "<h2>9. Homepage Test</h2>";
if (file_exists('index.html')) {
    $index_content = file_get_contents('index.html');
    if (strpos($index_content, 'KBC Plus') !== false) {
        echo "<p style='color: green;'>✓ Homepage contains expected content</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Homepage might not have expected content</p>";
    }
    echo "<p>Homepage size: " . strlen($index_content) . " bytes</p>";
}

echo "<hr>";
echo "<h2>🔧 Quick Fixes</h2>";
echo "<p><strong>If you see issues above:</strong></p>";
echo "<ol>";
echo "<li>Missing files → Re-upload the deployment package</li>";
echo "<li>Database errors → Check credentials in backend/config.php</li>";
echo "<li>Permission errors → Set files to 644, directories to 755</li>";
echo "<li>API errors → Test individual API endpoints</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>Delete this diagnostic file after reviewing the results!</em></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 800px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
p { margin: 8px 0; }
pre { background: #f8f8f8; padding: 10px; border-radius: 4px; }
ul li { margin: 4px 0; }
</style>
