<?php
require_once 'backend/config.php';

try {
    // $pdo is already created in config.php
    echo "✅ Database connection successful\n\n";
    
    // Examine podcasts table structure
    echo "📊 PODCASTS TABLE STRUCTURE:\n";
    $stmt = $pdo->query("DESCRIBE podcasts");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
    
    // Show sample podcasts data
    echo "\n📋 SAMPLE PODCASTS DATA:\n";
    $stmt = $pdo->query("SELECT * FROM podcasts LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Title: " . substr($row['title'], 0, 50) . "...\n";
        echo "  Description: " . substr($row['description'], 0, 80) . "...\n";
        echo "  RSS URL: {$row['rss_url']}\n";
        echo "  Status: {$row['status']}\n";
        echo "  Created: {$row['created_at']}\n";
        echo "  ---\n";
    }
    
    echo "\n📊 PODCAST_EPISODES TABLE STRUCTURE:\n";
    $stmt = $pdo->query("DESCRIBE podcast_episodes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
    
    // Show sample episodes data
    echo "\n📋 SAMPLE PODCAST_EPISODES DATA:\n";
    $stmt = $pdo->query("SELECT * FROM podcast_episodes LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Podcast ID: {$row['podcast_id']}\n";
        echo "  Title: " . substr($row['title'], 0, 50) . "...\n";
        echo "  Audio URL: {$row['audio_url']}\n";
        echo "  Duration: {$row['duration']}\n";
        echo "  Published: {$row['publish_date']}\n";
        echo "  Status: {$row['status']}\n";
        echo "  ---\n";
    }
    
    // Count records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM podcasts");
    $podcastCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM podcast_episodes");
    $episodeCount = $stmt->fetch()['count'];
    
    echo "\n📈 RECORD COUNTS:\n";
    echo "- Podcasts: {$podcastCount}\n";
    echo "- Episodes: {$episodeCount}\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
