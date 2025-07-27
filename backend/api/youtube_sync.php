<?php
require_once '../config.php';
require_once '../../youtube_config.php';

header('Content-Type: application/json');

/**
 * YouTube Channel Video Synchronization API
 * Automatically fetches the latest videos from your YouTube channel
 */

try {
    // Validate API configuration
    if (empty(YOUTUBE_API_KEY) || YOUTUBE_API_KEY === 'YOUR_YOUTUBE_API_KEY_HERE') {
        throw new Exception('YouTube API key not configured');
    }

    if (empty(YOUTUBE_CHANNEL_ID)) {
        throw new Exception('YouTube Channel ID not configured');
    }

    // Create videos table if it doesn't exist
    $checkTable = $pdo->query("SHOW TABLES LIKE 'videos'");
    if ($checkTable->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE videos (
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
        
        // Add index for better performance
        $pdo->exec("CREATE INDEX idx_youtube_id ON videos (youtube_id)");
        $pdo->exec("CREATE INDEX idx_published_at ON videos (youtube_published_at)");
    }

    // Get the number of videos to fetch (default: 20, max: 50)
    $maxResults = isset($_GET['max_results']) ? min(intval($_GET['max_results']), 50) : 20;
    
    // Get videos from YouTube channel
    $channelVideos = getChannelVideos(YOUTUBE_CHANNEL_ID, YOUTUBE_API_KEY, $maxResults);
    
    if (empty($channelVideos)) {
        throw new Exception('No videos found or API request failed');
    }

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
            
            // Get additional video details
            $videoDetails = getYouTubeVideoDetails($videoId, YOUTUBE_API_KEY);
            
            $duration = '';
            $viewCount = 0;
            $isLive = false;
            
            if ($videoDetails) {
                // Parse duration from ISO 8601 format (PT15M33S) to readable format
                if (isset($videoDetails['contentDetails']['duration'])) {
                    $duration = parseYouTubeDuration($videoDetails['contentDetails']['duration']);
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
            $syncResults['errors'][] = [
                'video_id' => $videoId ?? 'unknown',
                'error' => $e->getMessage()
            ];
        }
    }

    // Update last sync timestamp
    $pdo->exec("
        INSERT INTO sync_log (sync_type, sync_time, results) 
        VALUES ('youtube_videos', NOW(), '" . json_encode($syncResults) . "')
        ON DUPLICATE KEY UPDATE 
        sync_time = NOW(), 
        results = '" . json_encode($syncResults) . "'
    ");

    echo json_encode([
        'success' => true,
        'message' => 'YouTube videos synchronized successfully',
        'sync_results' => $syncResults
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Parse YouTube duration from ISO 8601 format to readable format
 */
function parseYouTubeDuration($duration) {
    $interval = new DateInterval($duration);
    $hours = $interval->h;
    $minutes = $interval->i;
    $seconds = $interval->s;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%d:%02d', $minutes, $seconds);
    }
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
