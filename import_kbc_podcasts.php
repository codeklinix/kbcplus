<?php
require_once 'backend/config.php';

echo "🚀 Starting KBC Podcast Import...\n";

try {
    // Step 1: Clear existing data
    echo "\n🗑️ Clearing existing podcast data...\n";
    $pdo->exec("DELETE FROM podcast_episodes");
    $pdo->exec("DELETE FROM podcasts");
    $pdo->exec("ALTER TABLE podcasts AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE podcast_episodes AUTO_INCREMENT = 1");
    echo "✅ Existing data cleared\n";

    // Step 2: Fetch KBC playlist data
    echo "\n📡 Fetching KBC podcast episodes...\n";
    $playlist_url = "https://podcast.kbc.co.ke/?load=playlist.json&title=&albums=7881";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $json_data = file_get_contents($playlist_url, false, $context);
    if ($json_data === false) {
        throw new Exception("Failed to fetch playlist data");
    }
    
    $playlist = json_decode($json_data, true);
    if (!$playlist || !isset($playlist['tracks'])) {
        throw new Exception("Invalid playlist format");
    }
    
    echo "✅ Found " . count($playlist['tracks']) . " episodes\n";

    // Step 3: Create podcast categories based on KBC content
    $podcasts = [
        [
            'title' => 'KBC English Podcasts',
            'description' => 'English language podcasts from Kenya Broadcasting Corporation covering news, politics, and social issues.',
            'host_name' => 'KBC Radio',
            'cover_image' => 'https://podcast.kbc.co.ke/app/themes/vodi/assets/img/logo.png',
            'category' => 'News & Politics',
            'rss_feed' => 'https://podcast.kbc.co.ke/feed/podcast'
        ],
        [
            'title' => 'KBC Vernacular Podcasts',
            'description' => 'Local language podcasts including Ekegusii, Luo, Somali and other Kenyan languages covering community issues, agriculture, and health.',
            'host_name' => 'KBC Radio',
            'cover_image' => 'https://podcast.kbc.co.ke/app/themes/vodi/assets/img/logo.png',
            'category' => 'Society & Culture',
            'rss_feed' => 'https://podcast.kbc.co.ke/feed/podcast'
        ],
        [
            'title' => 'KBC Agriculture & Health',
            'description' => 'Educational podcasts focusing on agricultural practices, health awareness, and community development.',
            'host_name' => 'KBC Radio',
            'cover_image' => 'https://podcast.kbc.co.ke/app/themes/vodi/assets/img/logo.png',
            'category' => 'Education',
            'rss_feed' => 'https://podcast.kbc.co.ke/feed/podcast'
        ]
    ];

    // Step 4: Insert podcasts
    echo "\n📻 Creating podcast categories...\n";
    $podcast_ids = [];
    
    $stmt = $pdo->prepare("
        INSERT INTO podcasts (title, description, host_name, cover_image, category, rss_feed, is_active, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
    ");
    
    foreach ($podcasts as $podcast) {
        $stmt->execute([
            $podcast['title'],
            $podcast['description'],
            $podcast['host_name'],
            $podcast['cover_image'],
            $podcast['category'],
            $podcast['rss_feed']
        ]);
        $podcast_ids[] = $pdo->lastInsertId();
        echo "✅ Created: " . $podcast['title'] . "\n";
    }

    // Step 5: Process and categorize episodes
    echo "\n📝 Processing episodes...\n";
    
    $episode_stmt = $pdo->prepare("
        INSERT INTO podcast_episodes (podcast_id, title, description, audio_url, duration, episode_number, published_date, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    
    $episode_count = 0;
    
    foreach ($playlist['tracks'] as $index => $episode) {
        // Determine podcast category based on title content
        $title = $episode['track_title'] ?? 'Episode ' . ($index + 1);
        $description = $episode['track_description'] ?? 'KBC Podcast Episode';
        $audio_url = $episode['mp3'] ?? '';
        $duration = $episode['duration'] ?? '00:00';
        $published_date = isset($episode['date']) ? date('Y-m-d', strtotime($episode['date'])) : date('Y-m-d');
        
        // Categorize based on title content
        $podcast_id = $podcast_ids[0]; // Default to English
        
        // Check for vernacular language indicators
        $vernacular_keywords = ['ekegusii', 'luo', 'somali', 'tinyia', 'minto', 'endege'];
        foreach ($vernacular_keywords as $keyword) {
            if (stripos($title, $keyword) !== false || stripos($description, $keyword) !== false) {
                $podcast_id = $podcast_ids[1]; // Vernacular
                break;
            }
        }
        
        // Check for agriculture/health content
        $agri_health_keywords = ['agriculture', 'farming', 'health', 'crops', 'livestock', 'medical', 'clinic'];
        foreach ($agri_health_keywords as $keyword) {
            if (stripos($title, $keyword) !== false || stripos($description, $keyword) !== false) {
                $podcast_id = $podcast_ids[2]; // Agriculture & Health
                break;
            }
        }
        
        // Insert episode
        try {
            $episode_stmt->execute([
                $podcast_id,
                $title,
                $description,
                $audio_url,
                $duration,
                $index + 1,
                $published_date
            ]);
            $episode_count++;
            
            if ($episode_count % 10 == 0) {
                echo "📝 Processed $episode_count episodes...\n";
            }
        } catch (Exception $e) {
            echo "⚠️ Error inserting episode: " . $e->getMessage() . "\n";
        }
    }

    // Step 6: Summary
    echo "\n📊 Import Summary:\n";
    echo "✅ Podcasts created: " . count($podcasts) . "\n";
    echo "✅ Episodes imported: $episode_count\n";
    
    // Verify data
    $podcast_count = $pdo->query("SELECT COUNT(*) FROM podcasts")->fetchColumn();
    $episode_total = $pdo->query("SELECT COUNT(*) FROM podcast_episodes")->fetchColumn();
    
    echo "\n🔍 Database Verification:\n";
    echo "📻 Total podcasts in DB: $podcast_count\n";
    echo "📝 Total episodes in DB: $episode_total\n";
    
    // Show sample episodes per podcast
    echo "\n📋 Episodes per podcast:\n";
    $result = $pdo->query("
        SELECT p.title, COUNT(pe.id) as episode_count 
        FROM podcasts p 
        LEFT JOIN podcast_episodes pe ON p.id = pe.podcast_id 
        GROUP BY p.id, p.title
    ");
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  • " . $row['title'] . ": " . $row['episode_count'] . " episodes\n";
    }
    
    echo "\n🎉 KBC Podcast import completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Import failed!\n";
}
?>
