<?php
// Simple Database Setup - Copy this entire code to create setup_database.php on your server
require_once 'backend/config.php';

echo "<h2>KBC+ Database Setup</h2>";

try {
    // Create radio_stations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS radio_stations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        stream_url VARCHAR(500) NOT NULL,
        logo_url VARCHAR(500),
        category VARCHAR(100),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Radio stations table created<br>";

    // Create tv_streams table
    $pdo->exec("CREATE TABLE IF NOT EXISTS tv_streams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        channel_name VARCHAR(255) NOT NULL,
        description TEXT,
        stream_url VARCHAR(500) NOT NULL,
        logo_url VARCHAR(500),
        category VARCHAR(100),
        is_live BOOLEAN DEFAULT TRUE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ TV streams table created<br>";

    // Create podcasts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS podcasts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        host_name VARCHAR(255),
        cover_image VARCHAR(500),
        category VARCHAR(100),
        rss_feed VARCHAR(500),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Podcasts table created<br>";

    // Create news_articles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS news_articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        summary TEXT,
        author VARCHAR(255),
        featured_image VARCHAR(500),
        category VARCHAR(100),
        tags TEXT,
        is_published BOOLEAN DEFAULT FALSE,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ News articles table created<br>";

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'editor', 'user') DEFAULT 'user',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Users table created<br>";

    // Insert sample data
    $pdo->exec("INSERT IGNORE INTO radio_stations (name, description, stream_url, category) VALUES
        ('KBC English Service', 'Kenya Broadcasting Corporation English Service', 'https://kbcradio.co.ke:8000/english', 'News'),
        ('KBC Radio Taifa', 'Kenya Broadcasting Corporation Swahili Service', 'https://kbcradio.co.ke:8000/taifa', 'News'),
        ('KBC Central FM', 'KBC Central Kenya Service', 'https://kbcradio.co.ke:8000/central', 'Regional')");
    echo "✅ Sample radio stations added<br>";

    $pdo->exec("INSERT IGNORE INTO tv_streams (channel_name, description, stream_url, category) VALUES
        ('KBC Channel 1', 'Kenya Broadcasting Corporation TV', 'https://kbctv.co.ke/live', 'General'),
        ('KBC News', 'KBC 24/7 News Channel', 'https://kbctv.co.ke/news', 'News'),
        ('KBC Sports', 'KBC Sports Channel', 'https://kbctv.co.ke/sports', 'Sports')");
    echo "✅ Sample TV streams added<br>";

    $pdo->exec("INSERT IGNORE INTO podcasts (title, description, host_name, category) VALUES
        ('KBC Morning Show', 'Start your day with KBC Morning Show', 'Morning Team', 'Talk Show'),
        ('KBC Business Hour', 'Business news and analysis', 'Business Team', 'Business'),
        ('KBC Sports Talk', 'Latest sports news and discussions', 'Sports Team', 'Sports')");
    echo "✅ Sample podcasts added<br>";

    $pdo->exec("INSERT IGNORE INTO news_articles (title, content, summary, author, category, is_published, published_at) VALUES
        ('KBC+ Streaming Platform Launched', 'Kenya Broadcasting Corporation has launched its new streaming platform KBC+ offering live radio, TV and on-demand content.', 'KBC launches new streaming platform', 'KBC News', 'Technology', 1, NOW()),
        ('New Programming Schedule', 'KBC announces updated programming schedule with more local content and international shows.', 'Updated programming schedule announced', 'KBC Programming', 'General', 1, NOW())");
    echo "✅ Sample news articles added<br>";

    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>Your KBC+ Streaming website is now ready!</strong></p>";
    echo "<p><a href='index.html'>Go to your website</a></p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Database Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
