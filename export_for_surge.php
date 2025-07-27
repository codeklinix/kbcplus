<?php
require_once 'backend/config.php';

echo "=== Exporting KBC Plus Data for Static Deployment ===\n\n";

try {
    // Create static data directory
    $staticDir = 'static_data';
    if (!is_dir($staticDir)) {
        mkdir($staticDir, 0755, true);
        echo "✅ Created static_data directory\n";
    }

    // Export Radio Stations
    echo "📻 Exporting radio stations...\n";
    $stmt = $pdo->query('SELECT * FROM radio_stations ORDER BY id');
    $radioStations = $stmt->fetchAll();
    file_put_contents($staticDir . '/radio.json', json_encode($radioStations, JSON_PRETTY_PRINT));
    echo "✅ Exported " . count($radioStations) . " radio stations\n";

    // Export TV Streams
    echo "📺 Exporting TV streams...\n";
    $stmt = $pdo->query('SELECT * FROM tv_streams WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
    $tvStreams = $stmt->fetchAll();
    
    // Format TV streams for frontend
    foreach ($tvStreams as &$stream) {
        $stream['is_live'] = (bool)$stream['is_live'];
        $stream['is_active'] = (bool)$stream['is_active'];
        
        // Add YouTube data if applicable
        if (strpos($stream['stream_url'], 'youtube.com/embed/') !== false) {
            preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $stream['stream_url'], $matches);
            if (isset($matches[1])) {
                $stream['youtube_id'] = $matches[1];
                $stream['youtube_url'] = 'https://www.youtube.com/watch?v=' . $matches[1];
                $stream['thumbnail_url'] = 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
                $stream['source_type'] = 'YouTube';
            }
        }
        
        if (empty($stream['logo_url'])) {
            $stream['logo_url'] = 'assets/images/kbc-logo.png';
        }
    }
    
    file_put_contents($staticDir . '/tv_streams.json', json_encode($tvStreams, JSON_PRETTY_PRINT));
    echo "✅ Exported " . count($tvStreams) . " TV streams\n";

    // Export Podcasts
    echo "🎧 Exporting podcasts...\n";
    $stmt = $pdo->query('SELECT * FROM podcasts ORDER BY id');
    $podcasts = $stmt->fetchAll();
    file_put_contents($staticDir . '/podcasts.json', json_encode($podcasts, JSON_PRETTY_PRINT));
    echo "✅ Exported " . count($podcasts) . " podcasts\n";

    // Export Podcast Episodes
    echo "📝 Exporting podcast episodes...\n";
    $stmt = $pdo->query('SELECT * FROM podcast_episodes ORDER BY podcast_id, episode_number');
    $episodes = $stmt->fetchAll();
    
    // Group episodes by podcast
    $episodesByPodcast = [];
    foreach ($episodes as $episode) {
        $episodesByPodcast[$episode['podcast_id']][] = $episode;
    }
    
    file_put_contents($staticDir . '/podcast_episodes.json', json_encode($episodesByPodcast, JSON_PRETTY_PRINT));
    echo "✅ Exported " . count($episodes) . " podcast episodes\n";

    // Export News Articles
    echo "📰 Exporting news articles...\n";
    $stmt = $pdo->query('SELECT * FROM news_articles ORDER BY created_at DESC');
    $news = $stmt->fetchAll();
    file_put_contents($staticDir . '/news.json', json_encode($news, JSON_PRETTY_PRINT));
    echo "✅ Exported " . count($news) . " news articles\n";

    // Export Radio Schedule (if exists)
    try {
        $stmt = $pdo->query('SELECT * FROM radio_schedules ORDER BY start_time');
        $schedules = $stmt->fetchAll();
        file_put_contents($staticDir . '/schedule.json', json_encode($schedules, JSON_PRETTY_PRINT));
        echo "✅ Exported " . count($schedules) . " schedule items\n";
    } catch (Exception $e) {
        echo "ℹ️  No radio schedules table found\n";
        file_put_contents($staticDir . '/schedule.json', json_encode([], JSON_PRETTY_PRINT));
    }

    echo "\n=== Export Complete! ===\n";
    echo "📁 All data exported to: {$staticDir}/\n";
    echo "📋 Files created:\n";
    echo "  - radio.json\n";
    echo "  - tv_streams.json\n";
    echo "  - podcasts.json\n";
    echo "  - podcast_episodes.json\n";
    echo "  - news.json\n";
    echo "  - schedule.json\n";
    
    echo "\n🚀 Next steps for Surge deployment:\n";
    echo "1. Install Surge: npm install -g surge\n";
    echo "2. Run the static conversion script\n";
    echo "3. Deploy with: surge ./surge_build\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
