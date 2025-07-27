<?php
// Check existing database structure
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kbcplus', 'root', '');
    echo "✅ Database connection successful\n\n";
    
    // Show all tables
    $stmt = $pdo->query('SHOW TABLES');
    echo "📋 Existing tables:\n";
    while($row = $stmt->fetch()) {
        echo "- " . $row[0] . "\n";
    }
    
    echo "\n👤 Users table structure:\n";
    $stmt = $pdo->query('DESCRIBE users');
    while($row = $stmt->fetch()) {
        echo "- " . $row['Field'] . " | " . $row['Type'] . "\n";
    }
    
    echo "\n📊 Sample data count:\n";
    $tables = ['radio_stations', 'tv_streams', 'podcasts', 'news_articles', 'users'];
    foreach($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "- $table: $count records\n";
        } catch(Exception $e) {
            echo "- $table: Error - " . $e->getMessage() . "\n";
        }
    }
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
