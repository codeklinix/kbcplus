<?php
// Quick test to verify APIs are working
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>KBC Plus - Quick Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🧪 KBC Plus - Quick API Test</h1>
    
    <?php
    // Change to API directory context
    $oldDir = getcwd();
    chdir(__DIR__ . '/backend/api');
    
    $apis = [
        'Radio Stations' => 'radio.php',
        'TV Streams' => 'tv.php',
        'Podcasts' => 'podcasts.php',
        'News Articles' => 'news.php'
    ];
    
    foreach ($apis as $name => $file) {
        echo "<h2>📡 Testing $name ($file)</h2>";
        
        if (file_exists($file)) {
            ob_start();
            try {
                include $file;
                $output = ob_get_clean();
                
                $data = json_decode($output, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "<p class='success'>✅ SUCCESS: Found " . count($data) . " records</p>";
                    if (count($data) > 0) {
                        echo "<details><summary>First record:</summary>";
                        echo "<pre>" . htmlspecialchars(json_encode($data[0], JSON_PRETTY_PRINT)) . "</pre>";
                        echo "</details>";
                    }
                } else {
                    echo "<p class='warning'>⚠️ JSON Error: " . json_last_error_msg() . "</p>";
                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                }
            } catch (Exception $e) {
                ob_end_clean();
                echo "<p class='error'>❌ ERROR: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='error'>❌ File not found: $file</p>";
        }
        echo "<hr>";
    }
    
    chdir($oldDir);
    ?>
    
    <h2>🎯 Summary</h2>
    <ul>
        <li>✅ Database configured for local XAMPP</li>
        <li>📊 Sample KBC data loaded</li>
        <li>👤 Admin user: admin / admin123</li>
        <li>🌐 Website URL: <a href="http://localhost/kbcplus/">http://localhost/kbcplus/</a></li>
    </ul>
    
    <h2>🚀 Next Steps</h2>
    <ol>
        <li>Visit your website: <a href="index.html" target="_blank">Open Homepage</a></li>
        <li>Test streaming functionality</li>
        <li>Check admin panel if available</li>
    </ol>
</body>
</html>
