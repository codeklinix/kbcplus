<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Get active TV streams from tv_streams table
    $stmt = $pdo->prepare("
        SELECT 
            id,
            channel_name,
            description,
            stream_url,
            logo_url,
            category,
            is_live,
            is_active,
            display_order,
            created_at,
            updated_at
        FROM tv_streams 
        WHERE is_active = 1
        ORDER BY display_order ASC, id ASC
    ");
    
    $stmt->execute();
    $channels = $stmt->fetchAll();
    
    // Format the response for better frontend consumption
    foreach ($channels as &$channel) {
        $channel['is_live'] = (bool)$channel['is_live'];
        $channel['is_active'] = (bool)$channel['is_active'];
        
        // If it's a YouTube embed URL, extract video ID for additional functionality
        if (strpos($channel['stream_url'], 'youtube.com/embed/') !== false) {
            preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $channel['stream_url'], $matches);
            if (isset($matches[1])) {
                $channel['youtube_id'] = $matches[1];
                $channel['youtube_url'] = 'https://www.youtube.com/watch?v=' . $matches[1];
                $channel['thumbnail_url'] = 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
                $channel['source_type'] = 'YouTube';
            }
        } else {
            $channel['source_type'] = 'Stream';
        }
        
        // Ensure logo_url has a default if empty
        if (empty($channel['logo_url'])) {
            $channel['logo_url'] = 'assets/images/kbc-logo.png';
        }
    }
    
    echo json_encode($channels);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
