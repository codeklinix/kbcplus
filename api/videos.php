<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Check if videos table exists, if not create it
    $checkTable = $pdo->query("SHOW TABLES LIKE 'videos'");
    if ($checkTable->rowCount() == 0) {
        // Create videos table
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
        
        // Insert sample YouTube videos from KBC or general content
        $sampleVideos = [
            [
                'title' => 'KBC News Update - Morning Bulletin',
                'description' => 'Stay updated with the latest news from KBC. Our morning bulletin covers local and international news.',
                'youtube_id' => 'dQw4w9WgXcQ', // Sample YouTube ID
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'thumbnail_url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
                'channel_name' => 'KBC Channel',
                'category' => 'News',
                'tags' => 'news,kenya,kbc,morning',
                'duration' => '15:32'
            ],
            [
                'title' => 'Cultural Heritage of Kenya - Documentary',
                'description' => 'Explore the rich cultural heritage of Kenya through this comprehensive documentary featuring traditional music and dance.',
                'youtube_id' => 'ScMzIvxBSi4', // Sample YouTube ID
                'youtube_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
                'thumbnail_url' => 'https://img.youtube.com/vi/ScMzIvxBSi4/maxresdefault.jpg',
                'channel_name' => 'KBC Culture',
                'category' => 'Documentary',
                'tags' => 'culture,kenya,heritage,documentary',
                'duration' => '28:45'
            ],
            [
                'title' => 'Live Music Performance - Kenyan Artists',
                'description' => 'Watch live performances by talented Kenyan musicians in this special KBC music showcase.',
                'youtube_id' => 'kJQP7kiw5Fk', // Sample YouTube ID
                'youtube_url' => 'https://www.youtube.com/watch?v=kJQP7kiw5Fk',
                'thumbnail_url' => 'https://img.youtube.com/vi/kJQP7kiw5Fk/maxresdefault.jpg',
                'channel_name' => 'KBC Entertainment',
                'category' => 'Music',
                'tags' => 'music,live,performance,kenya',
                'duration' => '45:12'
            ],
            [
                'title' => 'Sports Highlights - Premier League Round Up',
                'description' => 'Catch up with the latest sports action and highlights from local and international sports.',
                'youtube_id' => 'LDU_Txk06tM', // Sample YouTube ID
                'youtube_url' => 'https://www.youtube.com/watch?v=LDU_Txk06tM',
                'thumbnail_url' => 'https://img.youtube.com/vi/LDU_Txk06tM/maxresdefault.jpg',
                'channel_name' => 'KBC Sports',
                'category' => 'Sports',
                'tags' => 'sports,football,highlights,kenya',
                'duration' => '22:18'
            ],
            [
                'title' => 'Technology in Kenya - Innovation Hub',
                'description' => 'Discover the latest technological innovations and developments in Kenya\'s growing tech sector.',
                'youtube_id' => 'fJ9rUzIMcZQ', // Sample YouTube ID
                'youtube_url' => 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ',
                'thumbnail_url' => 'https://img.youtube.com/vi/fJ9rUzIMcZQ/maxresdefault.jpg',
                'channel_name' => 'KBC Tech',
                'category' => 'Technology',
                'tags' => 'technology,innovation,kenya,tech',
                'duration' => '18:56'
            ],
            [
                'title' => 'Cooking Show - Traditional Kenyan Cuisine',
                'description' => 'Learn how to prepare traditional Kenyan dishes with our expert chefs in this cooking masterclass.',
                'youtube_id' => 'ZZ5LpwO-An4', // Sample YouTube ID
                'youtube_url' => 'https://www.youtube.com/watch?v=ZZ5LpwO-An4',
                'thumbnail_url' => 'https://img.youtube.com/vi/ZZ5LpwO-An4/maxresdefault.jpg',
                'channel_name' => 'KBC Lifestyle',
                'category' => 'Lifestyle',
                'tags' => 'cooking,food,kenya,traditional,recipe',
                'duration' => '32:24'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO videos (title, description, youtube_id, youtube_url, thumbnail_url, channel_name, category, tags, duration) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleVideos as $video) {
            $stmt->execute([
                $video['title'],
                $video['description'], 
                $video['youtube_id'],
                $video['youtube_url'],
                $video['thumbnail_url'],
                $video['channel_name'],
                $video['category'],
                $video['tags'],
                $video['duration']
            ]);
        }
    }
    
    // Handle different HTTP methods
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get specific video
                $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ? AND is_active = TRUE");
                $stmt->execute([$_GET['id']]);
                $video = $stmt->fetch();
                
                if ($video) {
                    // Increment views count
                    $updateViews = $pdo->prepare("UPDATE videos SET views_count = views_count + 1 WHERE id = ?");
                    $updateViews->execute([$_GET['id']]);
                    $video['views_count']++;
                    
                    echo json_encode($video);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Video not found']);
                }
            } elseif (isset($_GET['category'])) {
                // Get videos by category
                $stmt = $pdo->prepare("SELECT * FROM videos WHERE category = ? AND is_active = TRUE ORDER BY published_at DESC");
                $stmt->execute([$_GET['category']]);
                $videos = $stmt->fetchAll();
                echo json_encode($videos);
            } elseif (isset($_GET['search'])) {
                // Search videos
                $searchTerm = '%' . $_GET['search'] . '%';
                $stmt = $pdo->prepare("
                    SELECT * FROM videos 
                    WHERE (title LIKE ? OR description LIKE ? OR tags LIKE ?) 
                    AND is_active = TRUE 
                    ORDER BY published_at DESC
                ");
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
                $videos = $stmt->fetchAll();
                echo json_encode($videos);
            } else {
                // Get all videos
                $stmt = $pdo->prepare("SELECT * FROM videos WHERE is_active = TRUE ORDER BY published_at DESC");
                $stmt->execute();
                $videos = $stmt->fetchAll();
                echo json_encode($videos);
            }
            break;
            
        case 'POST':
            // Add new video (for admin use)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['title']) || !isset($input['youtube_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Title and YouTube ID are required']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO videos (title, description, youtube_id, youtube_url, thumbnail_url, channel_name, category, tags, duration) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $youtube_url = 'https://www.youtube.com/watch?v=' . $input['youtube_id'];
            $thumbnail_url = 'https://img.youtube.com/vi/' . $input['youtube_id'] . '/maxresdefault.jpg';
            
            $stmt->execute([
                $input['title'],
                $input['description'] ?? '',
                $input['youtube_id'],
                $youtube_url,
                $thumbnail_url,
                $input['channel_name'] ?? 'KBC Channel',
                $input['category'] ?? 'General',
                $input['tags'] ?? '',
                $input['duration'] ?? ''
            ]);
            
            $videoId = $pdo->lastInsertId();
            echo json_encode(['id' => $videoId, 'message' => 'Video added successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
