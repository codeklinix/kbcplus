<?php
require_once 'backend/config.php';

echo "<h2>YouTube Sync Setup</h2>";

try {
    // Create sync_log table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sync_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sync_type VARCHAR(50) NOT NULL,
            sync_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            results JSON,
            UNIQUE KEY unique_sync_type (sync_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "<p>✓ Created sync_log table</p>";
    
    // Update videos table structure if needed
    $alterQueries = [
        "ALTER TABLE videos ADD COLUMN IF NOT EXISTS is_live BOOLEAN DEFAULT FALSE",
        "ALTER TABLE videos ADD COLUMN IF NOT EXISTS youtube_published_at DATETIME",
        "ALTER TABLE videos ADD UNIQUE KEY IF NOT EXISTS unique_youtube_id (youtube_id)"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "<p>✓ Updated videos table structure</p>";
        } catch (PDOException $e) {
            // Ignore if column already exists
            if (strpos($e->getMessage(), 'Duplicate column name') === false && 
                strpos($e->getMessage(), 'Duplicate key name') === false) {
                throw $e;
            }
        }
    }
    
    // Create indexes for better performance
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_youtube_id ON videos (youtube_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_published_at ON videos (youtube_published_at)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_is_live ON videos (is_live)");
        echo "<p>✓ Created database indexes</p>";
    } catch (PDOException $e) {
        // Ignore if indexes already exist
    }
    
    echo "<p><strong>Setup completed successfully!</strong></p>";
    echo "<p>You can now use the YouTube sync functionality:</p>";
    echo "<ul>";
    echo "<li><a href='backend/api/youtube_sync.php' target='_blank'>Sync videos from YouTube</a></li>";
    echo "<li><a href='backend/api/tv.php' target='_blank'>View TV channels (with synced videos)</a></li>";
    echo "<li><a href='backend/api/videos.php' target='_blank'>View all videos</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
