<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Check if videos table exists, if not create it
    $checkTable = $pdo->query("SHOW TABLES LIKE 'videos'");
    if ($checkTable->rowCount() == 0) {
        // Create videos table first
        $pdo->exec("
            CREATE TABLE videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                youtube_id VARCHAR(50) NOT NULL,
                youtube_url VARCHAR(255) NOT NULL,
                thumbnail_url VARCHAR(500),
                channel_name VARCHAR(100) DEFAULT 'KBC Channel',
                category VARCHAR(50) DEFAULT 'General',
                tags TEXT,
                duration VARCHAR(20),
                views_count INT DEFAULT 0,
                published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Load YouTube videos from configuration
        $youtubeVideos = include '../youtube_config.php';
        $sampleVideos = $youtubeVideos;
        
        $stmt = $pdo->prepare("
            INSERT INTO videos (title, description, youtube_id, youtube_url, thumbnail_url, channel_name, category, tags) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleVideos as $video) {
            $youtube_url = 'https://www.youtube.com/watch?v=' . $video['youtube_id'];
            $thumbnail_url = 'https://img.youtube.com/vi/' . $video['youtube_id'] . '/maxresdefault.jpg';
            
            $stmt->execute([
                $video['title'],
                $video['description'], 
                $video['youtube_id'],
                $youtube_url,
                $thumbnail_url,
                $video['channel_name'],
                $video['category'],
                $video['tags']
            ]);
        }
    }
    
    // Get videos formatted as TV channels (prioritize live content)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title as channel_name,
            description,
            youtube_id,
            youtube_url as stream_url,
            thumbnail_url as logo_url,
            channel_name as broadcaster,
            category,
            'YouTube' as source_type,
            is_live,
            TRUE as is_active,
            duration,
            views_count,
            youtube_published_at,
            CASE WHEN is_live = 1 THEN 1 ELSE 0 END as live_priority
        FROM videos 
        WHERE is_active = TRUE 
        ORDER BY live_priority DESC, youtube_published_at DESC, views_count DESC
        LIMIT 50
    ");
    $stmt->execute();
    $channels = $stmt->fetchAll();
    
    // Add embed URLs for better video playback
    foreach ($channels as &$channel) {
        $channel['embed_url'] = 'https://www.youtube.com/embed/' . $channel['youtube_id'];
        $channel['is_live'] = (bool)$channel['is_live'];
        $channel['formatted_views'] = formatViews($channel['views_count']);
    }
    
    echo json_encode($channels);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Format view count for display
 */
function formatViews($views) {
    if ($views >= 1000000) {
        return round($views / 1000000, 1) . 'M views';
    } elseif ($views >= 1000) {
        return round($views / 1000, 1) . 'K views';
    } else {
        return $views . ' views';
    }
}
?>
