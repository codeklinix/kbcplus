<?php
require_once 'backend/config.php';

echo "<h2>KBC+ Database Structure Fix</h2>\n";

try {
    // Check and fix radio_stations table
    echo "<h3>📻 Fixing Radio Stations Table</h3>\n";
    
    $checkRadio = $pdo->query("SHOW TABLES LIKE 'radio_stations'");
    if ($checkRadio->rowCount() == 0) {
        // Create radio_stations table
        $pdo->exec("
            CREATE TABLE radio_stations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                stream_url VARCHAR(500) NOT NULL,
                logo_url VARCHAR(500),
                category VARCHAR(100) DEFAULT 'General',
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                display_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "<p>✅ Created radio_stations table</p>\n";
    } else {
        // Add missing columns to existing table
        $columns = [];
        $stmt = $pdo->query("DESCRIBE radio_stations");
        while ($row = $stmt->fetch()) {
            $columns[] = $row['Field'];
        }
        
        if (!in_array('display_order', $columns)) {
            $pdo->exec("ALTER TABLE radio_stations ADD COLUMN display_order INT DEFAULT 0");
            echo "<p>✅ Added display_order column to radio_stations</p>\n";
        }
        if (!in_array('is_active', $columns)) {
            $pdo->exec("ALTER TABLE radio_stations ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
            echo "<p>✅ Added is_active column to radio_stations</p>\n";
        }
        echo "<p>✅ Radio stations table structure updated</p>\n";
    }
    
    // Check and fix tv_streams table  
    echo "<h3>📺 Fixing TV Streams Table</h3>\n";
    
    $checkTV = $pdo->query("SHOW TABLES LIKE 'tv_streams'");
    if ($checkTV->rowCount() == 0) {
        // Create tv_streams table
        $pdo->exec("
            CREATE TABLE tv_streams (
                id INT AUTO_INCREMENT PRIMARY KEY,
                channel_name VARCHAR(255) NOT NULL,
                stream_url VARCHAR(500) NOT NULL,
                logo_url VARCHAR(500),
                category VARCHAR(100) DEFAULT 'General',
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                is_live BOOLEAN DEFAULT TRUE,
                display_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "<p>✅ Created tv_streams table</p>\n";
    } else {
        // Add missing columns to existing table
        $columns = [];
        $stmt = $pdo->query("DESCRIBE tv_streams");
        while ($row = $stmt->fetch()) {
            $columns[] = $row['Field'];
        }
        
        if (!in_array('display_order', $columns)) {
            $pdo->exec("ALTER TABLE tv_streams ADD COLUMN display_order INT DEFAULT 0");
            echo "<p>✅ Added display_order column to tv_streams</p>\n";
        }
        if (!in_array('is_active', $columns)) {
            $pdo->exec("ALTER TABLE tv_streams ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
            echo "<p>✅ Added is_active column to tv_streams</p>\n";
        }
        if (!in_array('is_live', $columns)) {
            $pdo->exec("ALTER TABLE tv_streams ADD COLUMN is_live BOOLEAN DEFAULT TRUE");
            echo "<p>✅ Added is_live column to tv_streams</p>\n";
        }
        echo "<p>✅ TV streams table structure updated</p>\n";
    }
    
    // Update videos table to work better with TV integration
    echo "<h3>🎥 Updating Videos Table</h3>\n";
    
    $checkVideos = $pdo->query("SHOW TABLES LIKE 'videos'");
    if ($checkVideos->rowCount() > 0) {
        $columns = [];
        $stmt = $pdo->query("DESCRIBE videos");
        while ($row = $stmt->fetch()) {
            $columns[] = $row['Field'];
        }
        
        if (!in_array('display_order', $columns)) {
            $pdo->exec("ALTER TABLE videos ADD COLUMN display_order INT DEFAULT 0");
            echo "<p>✅ Added display_order column to videos</p>\n";
        }
        if (!in_array('is_live', $columns)) {
            $pdo->exec("ALTER TABLE videos ADD COLUMN is_live BOOLEAN DEFAULT FALSE");
            echo "<p>✅ Added is_live column to videos</p>\n";
        }
        echo "<p>✅ Videos table structure updated</p>\n";
    }
    
    echo "<hr>";
    echo "<h3>🎯 YouTube Integration Setup</h3>";
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>To display YouTube videos on Live TV:</h4>";
    echo "<ol>";
    echo "<li><strong>Update YouTube Video IDs:</strong> Go to <code>backend/api/tv.php</code> and <code>backend/api/videos.php</code></li>";
    echo "<li><strong>Replace sample YouTube IDs</strong> with your actual channel videos</li>";
    echo "<li><strong>YouTube API Key:</strong> Add your YouTube Data API key to fetch videos automatically</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h4>Current Video Sources in TV:</h4>";
    $videos = $pdo->query("SELECT title, youtube_id, channel_name FROM videos ORDER BY id LIMIT 5")->fetchAll();
    echo "<table border='1' cellpadding='8' cellspacing='0' style='margin: 10px 0; background: white;'>";
    echo "<tr style='background: #667eea; color: white;'><th>Title</th><th>YouTube ID</th><th>Channel</th></tr>";
    foreach ($videos as $video) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($video['title']) . "</td>";
        echo "<td>" . htmlspecialchars($video['youtube_id']) . "</td>";
        echo "<td>" . htmlspecialchars($video['channel_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>✅ Database Structure Fixed!</h3>";
    echo "<p>The admin panel should now work without column errors.</p>";
    echo "<p><a href='admin.html'>Go to Admin Panel</a> | <a href='login.html'>Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3, h4 { color: #333; }
p, li { margin: 8px 0; }
table { border-collapse: collapse; width: 100%; }
th, td { padding: 8px; text-align: left; }
code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
a { color: #667eea; text-decoration: none; }
a:hover { text-decoration: underline; }
hr { margin: 20px 0; border: none; border-top: 2px solid #ddd; }
</style>
