<?php
// Database Setup Script for KBC Streaming
// Run this file in your browser to populate your database

// Include your database configuration
require_once 'backend/config.php';

echo "<h2>KBC Streaming Database Setup</h2>";
echo "<p>Setting up your database tables and sample data...</p>";

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

    // Create radio_schedules table
    $pdo->exec("CREATE TABLE IF NOT EXISTS radio_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        station_id INT NOT NULL,
        show_name VARCHAR(255) NOT NULL,
        host_name VARCHAR(255),
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (station_id) REFERENCES radio_stations(id) ON DELETE CASCADE
    )");
    echo "✅ Radio schedules table created<br>";

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

    // Create podcast_episodes table
    $pdo->exec("CREATE TABLE IF NOT EXISTS podcast_episodes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        podcast_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        audio_url VARCHAR(500) NOT NULL,
        duration VARCHAR(20),
        episode_number INT,
        season_number INT DEFAULT 1,
        published_date DATE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (podcast_id) REFERENCES podcasts(id) ON DELETE CASCADE
    )");
    echo "✅ Podcast episodes table created<br>";

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
        ('Classic Rock FM', 'The best classic rock hits', 'https://streams.radiomast.io/7e0a6780-8c47-4c8c-b10f-2b9ee30763b6', 'Rock'),
        ('Jazz Lounge', 'Smooth jazz 24/7', 'https://streams.radiomast.io/smooth-jazz-stream', 'Jazz'),
        ('News Radio 24', 'Latest news and current affairs', 'https://streams.radiomast.io/news-radio-stream', 'News')");
    echo "✅ Sample radio stations added<br>";

    $pdo->exec("INSERT IGNORE INTO tv_streams (channel_name, description, stream_url, category) VALUES
        ('News Channel 1', 'Breaking news and live coverage', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'News'),
        ('Sports TV', 'Live sports and highlights', 'https://sample-videos.com/zip/10/mp4/SampleVideo_640x360_1mb.mp4', 'Sports'),
        ('Music Videos', '24/7 music video channel', 'https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_1mb.mp4', 'Entertainment')");
    echo "✅ Sample TV streams added<br>";

    $pdo->exec("INSERT IGNORE INTO podcasts (title, description, host_name, category) VALUES
        ('Tech Talk Daily', 'Latest technology news and reviews', 'John Smith', 'Technology'),
        ('Health & Wellness', 'Tips for a healthier lifestyle', 'Dr. Jane Doe', 'Health'),
        ('Business Insights', 'Market analysis and business strategies', 'Mike Johnson', 'Business')");
    echo "✅ Sample podcasts added<br>";

    $pdo->exec("INSERT IGNORE INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES
        (1, 'Morning Rock', 'DJ Mike', '06:00:00', '10:00:00', 'Monday', 'Start your Monday with the best rock hits'),
        (1, 'Afternoon Drive', 'Sarah Johnson', '14:00:00', '18:00:00', 'Monday', 'Rock music for your drive home'),
        (2, 'Jazz Morning', 'David Williams', '07:00:00', '11:00:00', 'Monday', 'Smooth jazz to start your day'),
        (2, 'Evening Jazz', 'Maria Garcia', '19:00:00', '23:00:00', 'Monday', 'Relaxing jazz for your evening'),
        (3, 'News Update', 'John Smith', '08:00:00', '09:00:00', 'Monday', 'Latest news and current affairs'),
        (3, 'Talk Show', 'Lisa Brown', '10:00:00', '12:00:00', 'Monday', 'Interactive talk show with callers')");
    echo "✅ Sample schedules added<br>";

    $pdo->exec("INSERT IGNORE INTO news_articles (title, content, summary, author, category, is_published, published_at) VALUES
        ('Tech Giants Report Strong Q4 Earnings', 'Major technology companies have reported stronger than expected earnings for the fourth quarter, driven by cloud computing and AI services growth.', 'Technology companies exceed Q4 earnings expectations.', 'Sarah Thompson', 'Technology', 1, '2025-01-25 10:00:00'),
        ('New Health Guidelines Released', 'Health authorities have released updated guidelines for preventive care, emphasizing the importance of regular check-ups and lifestyle modifications.', 'Updated health guidelines stress preventive care.', 'Dr. Lisa Wang', 'Health', 1, '2025-01-24 14:20:00')");
    echo "✅ Sample news articles added<br>";

    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>Your KBC Streaming website is now ready!</strong></p>";
    echo "<p><a href='index.html'>Go to your website</a> | <a href='admin.html'>Admin Panel</a></p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Database Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Please check your database configuration in backend/config.php</strong></p>";
}
?>
