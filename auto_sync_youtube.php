<?php
/**
 * Automated YouTube Video Sync Script
 * 
 * This script can be run manually or scheduled via cron/task scheduler
 * to automatically fetch the latest videos from your YouTube channel
 */

require_once 'backend/config.php';
require_once 'youtube_config.php';

// Set execution time limit
set_time_limit(300); // 5 minutes

// Function to log messages
function logMessage($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] [$type] $message\n";
    
    // Also log to file if desired
    $logFile = __DIR__ . '/logs/youtube_sync.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, "[$timestamp] [$type] $message\n", FILE_APPEND);
}

try {
    logMessage("Starting YouTube sync process...");
    
    // Check if we should run the sync (avoid too frequent syncing)
    $stmt = $pdo->prepare("SELECT sync_time FROM sync_log WHERE sync_type = 'youtube_videos'");
    $stmt->execute();
    $lastSync = $stmt->fetchColumn();
    
    $minInterval = 3600; // 1 hour minimum between syncs
    if ($lastSync && (time() - strtotime($lastSync)) < $minInterval) {
        logMessage("Sync skipped - last sync was less than 1 hour ago");
        exit(0);
    }
    
    // Validate API configuration
    if (empty(YOUTUBE_API_KEY) || YOUTUBE_API_KEY === 'YOUR_YOUTUBE_API_KEY_HERE') {
        throw new Exception('YouTube API key not configured');
    }

    if (empty(YOUTUBE_CHANNEL_ID)) {
        throw new Exception('YouTube Channel ID not configured');
    }

    logMessage("API configuration validated");

    // Create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            youtube_id VARCHAR(50) NOT NULL UNIQUE,
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
            is_active BOOLEAN DEFAULT TRUE,
            is_live BOOLEAN DEFAULT FALSE,
            youtube_published_at DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sync_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sync_type VARCHAR(50) NOT NULL,
            sync_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            results JSON,
            UNIQUE KEY unique_sync_type (sync_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    logMessage("Database tables verified");

    // Fetch videos from YouTube channel
    $maxResults = 25; // Get latest 25 videos
    $channelVideos = getChannelVideos(YOUTUBE_CHANNEL_ID, YOUTUBE_API_KEY, $maxResults);
    
    if (empty($channelVideos)) {
        throw new Exception('No videos found or API request failed');
    }

    logMessage("Fetched " . count($channelVideos) . " videos from YouTube API");

    $syncResults = [
        'total_fetched' => 0,
        'new_videos' => 0,
        'updated_videos' => 0,
        'errors' => [],
        'videos' => []
    ];

    foreach ($channelVideos as $item) {
        try {
            // Skip if not a video
            if ($item['id']['kind'] !== 'youtube#video') {
                continue;
            }

            $videoId = $item['id']['videoId'];
            $snippet = $item['snippet'];
            
            logMessage("Processing video: $videoId - " . substr($snippet['title'], 0, 50) . "...");
            
            // Get additional video details
            $videoDetails = getYouTubeVideoDetails($videoId, YOUTUBE_API_KEY);
            
            $duration = '';
            $viewCount = 0;
            $isLive = false;
            
            if ($videoDetails) {
                // Parse duration from ISO 8601 format (PT15M33S) to readable format
                if (isset($videoDetails['contentDetails']['duration'])) {
                    $interval = new DateInterval($videoDetails['contentDetails']['duration']);
                    $hours = $interval->h;
                    $minutes = $interval->i;
                    $seconds = $interval->s;
                    
                    if ($hours > 0) {
                        $duration = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
                    } else {
                        $duration = sprintf('%d:%02d', $minutes, $seconds);
                    }
                }
                
                if (isset($videoDetails['statistics']['viewCount'])) {
                    $viewCount = intval($videoDetails['statistics']['viewCount']);
                }
                
                // Check if it's a live stream
                if (isset($videoDetails['snippet']['liveBroadcastContent'])) {
                    $isLive = in_array($videoDetails['snippet']['liveBroadcastContent'], ['live', 'upcoming']);
                }
            }

            $title = $snippet['title'];
            $description = $snippet['description'];
            $publishedAt = date('Y-m-d H:i:s', strtotime($snippet['publishedAt']));
            $thumbnailUrl = $snippet['thumbnails']['maxres']['url'] ?? 
                           $snippet['thumbnails']['high']['url'] ?? 
                           $snippet['thumbnails']['medium']['url'] ?? 
                           $snippet['thumbnails']['default']['url'];
            
            $youtubeUrl = 'https://www.youtube.com/watch?v=' . $videoId;
            
            // Determine category based on title/description keywords
            $category = categorizeVideo($title, $description);
            
            // Generate tags from title and description
            $tags = generateTags($title, $description);

            // Check if video already exists
            $checkStmt = $pdo->prepare("SELECT id, views_count FROM videos WHERE youtube_id = ?");
            $checkStmt->execute([$videoId]);
            $existingVideo = $checkStmt->fetch();

            if ($existingVideo) {
                // Update existing video
                $updateStmt = $pdo->prepare("
                    UPDATE videos SET 
                        title = ?, 
                        description = ?, 
                        thumbnail_url = ?, 
                        duration = ?,
                        views_count = GREATEST(views_count, ?),
                        is_live = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE youtube_id = ?
                ");
                
                $updateStmt->execute([
                    $title,
                    $description,
                    $thumbnailUrl,
                    $duration,
                    $viewCount,
                    $isLive,
                    $videoId
                ]);
                
                $syncResults['updated_videos']++;
                logMessage("Updated existing video: $videoId");
            } else {
                // Insert new video
                $insertStmt = $pdo->prepare("
                    INSERT INTO videos (
                        title, description, youtube_id, youtube_url, thumbnail_url, 
                        channel_name, category, tags, duration, views_count, 
                        is_live, youtube_published_at, published_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $insertStmt->execute([
                    $title,
                    $description,
                    $videoId,
                    $youtubeUrl,
                    $thumbnailUrl,
                    $snippet['channelTitle'] ?? 'KBC Channel',
                    $category,
                    $tags,
                    $duration,
                    $viewCount,
                    $isLive,
                    $publishedAt,
                    $publishedAt
                ]);
                
                $syncResults['new_videos']++;
                logMessage("Added new video: $videoId");
            }

            $syncResults['videos'][] = [
                'youtube_id' => $videoId,
                'title' => $title,
                'duration' => $duration,
                'views' => $viewCount,
                'is_live' => $isLive,
                'published_at' => $publishedAt
            ];

            $syncResults['total_fetched']++;

        } catch (Exception $e) {
            $error = [
                'video_id' => $videoId ?? 'unknown',
                'error' => $e->getMessage()
            ];
            $syncResults['errors'][] = $error;
            logMessage("Error processing video " . ($videoId ?? 'unknown') . ": " . $e->getMessage(), 'ERROR');
        }
    }

    // Update sync log
    $pdo->exec("
        INSERT INTO sync_log (sync_type, sync_time, results) 
        VALUES ('youtube_videos', NOW(), '" . json_encode($syncResults) . "')
        ON DUPLICATE KEY UPDATE 
        sync_time = NOW(), 
        results = '" . json_encode($syncResults) . "'
    ");

    logMessage("Sync completed successfully!");
    logMessage("New videos: " . $syncResults['new_videos']);
    logMessage("Updated videos: " . $syncResults['updated_videos']);
    logMessage("Total processed: " . $syncResults['total_fetched']);
    logMessage("Errors: " . count($syncResults['errors']));

    if (!empty($syncResults['errors'])) {
        logMessage("Errors encountered:", 'WARNING');
        foreach ($syncResults['errors'] as $error) {
            logMessage("  - " . $error['video_id'] . ": " . $error['error'], 'WARNING');
        }
    }

} catch (Exception $e) {
    logMessage("Sync failed: " . $e->getMessage(), 'ERROR');
    exit(1);
}

/**
 * Categorize video based on title and description keywords
 */
function categorizeVideo($title, $description) {
    $text = strtolower($title . ' ' . $description);
    
    $categories = [
        'News' => ['news', 'breaking', 'bulletin', 'report', 'update', 'current affairs'],
        'Sports' => ['sports', 'football', 'soccer', 'basketball', 'athletics', 'match', 'game', 'tournament'],
        'Entertainment' => ['music', 'entertainment', 'show', 'performance', 'concert', 'comedy'],
        'Documentary' => ['documentary', 'history', 'culture', 'heritage', 'nature', 'wildlife'],
        'Technology' => ['technology', 'tech', 'innovation', 'digital', 'computer', 'internet'],
        'Lifestyle' => ['cooking', 'food', 'health', 'fashion', 'travel', 'lifestyle'],
        'Education' => ['education', 'learning', 'tutorial', 'lesson', 'training', 'academic'],
        'Talk Show' => ['talk', 'interview', 'discussion', 'debate', 'conversation']
    ];
    
    foreach ($categories as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return $category;
            }
        }
    }
    
    return 'General';
}

/**
 * Generate tags from title and description
 */
function generateTags($title, $description) {
    $text = strtolower($title . ' ' . $description);
    $commonWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'a', 'an'];
    
    // Extract meaningful words
    $words = preg_split('/[^a-zA-Z0-9]+/', $text);
    $tags = [];
    
    foreach ($words as $word) {
        if (strlen($word) > 3 && !in_array($word, $commonWords)) {
            $tags[] = $word;
        }
    }
    
    return implode(',', array_unique(array_slice($tags, 0, 10)));
}
?>
