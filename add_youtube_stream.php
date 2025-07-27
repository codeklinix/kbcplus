<?php
require_once 'backend/config.php';

echo "=== Adding YouTube Live Stream to KBC Plus ===\n\n";

try {
    // First, let's check current TV streams
    echo "Current TV Streams:\n";
    $stmt = $pdo->query('SELECT id, channel_name, stream_url, is_active FROM tv_streams ORDER BY id');
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']} | Name: {$row['channel_name']} | Active: {$row['is_active']}\n";
        echo "URL: {$row['stream_url']}\n\n";
    }
    
    // Extract YouTube video ID from the URL
    $youtube_url = "https://www.youtube.com/watch?v=PuSkU61kncI";
    $video_id = "PuSkU61kncI";
    
    // Convert to embeddable format
    $embed_url = "https://www.youtube.com/embed/{$video_id}?autoplay=1&mute=0&controls=1&rel=0&modestbranding=1";
    
    // Check if this stream already exists
    $check_stmt = $pdo->prepare('SELECT id FROM tv_streams WHERE stream_url LIKE ?');
    $check_stmt->execute(["%{$video_id}%"]);
    
    if ($check_stmt->fetch()) {
        echo "❌ This YouTube stream already exists in the database!\n";
    } else {
        // Add the new YouTube live stream
        $insert_stmt = $pdo->prepare('
            INSERT INTO tv_streams (channel_name, stream_url, logo_url, description, category, is_live, is_active, display_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $insert_stmt->execute([
            'KBC YouTube Live', // Channel name
            $embed_url, // Embeddable YouTube URL
            'assets/images/kbc-logo.png', // Logo (you can change this)
            'KBC Live YouTube Stream - Live broadcasting from YouTube', // Description
            'Live Stream', // Category
            1, // is_live
            1, // is_active
            10 // display_order (higher number = shown later)
        ]);
        
        echo "✅ Successfully added YouTube live stream!\n\n";
        
        // Show the new stream details
        echo "New Stream Details:\n";
        echo "Channel Name: KBC YouTube Live\n";
        echo "Original URL: {$youtube_url}\n";
        echo "Embed URL: {$embed_url}\n";
        echo "Status: Active & Live\n\n";
        
        // Get the new stream ID
        $new_id = $pdo->lastInsertId();
        echo "Assigned ID: {$new_id}\n\n";
    }
    
    // Show updated list
    echo "=== Updated TV Streams List ===\n";
    $stmt = $pdo->query('SELECT id, channel_name, is_active, is_live FROM tv_streams ORDER BY display_order, id');
    while ($row = $stmt->fetch()) {
        $status = ($row['is_active'] && $row['is_live']) ? 'Active & Live' : ($row['is_active'] ? 'Active' : 'Inactive');
        echo "ID: {$row['id']} | Name: {$row['channel_name']} | Status: {$status}\n";
    }
    
    echo "\n✅ Done! The YouTube live stream has been added to your KBC Plus website.\n";
    echo "🌐 Visit: http://localhost/kbc/kbcplus/kbcplus/ to see the new live stream!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
