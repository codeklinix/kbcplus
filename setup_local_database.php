<?php
// Local Database Setup Script for KBC Plus
// This script creates the database and all necessary tables for local XAMPP development

echo "<h2>KBC Plus - Local Database Setup</h2>";
echo "<p>Setting up database for local XAMPP environment...</p>";

// First, connect without specifying a database to create it
try {
    $pdo_init = new PDO(
        "mysql:host=localhost;port=3306;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Create the database
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS kbcplus");
    echo "✅ Database 'kbcplus' created/verified<br>";
    
    // Use the database
    $pdo_init->exec("USE kbcplus");
    echo "✅ Using database 'kbcplus'<br>";
    
} catch (PDOException $e) {
    die("<h3 style='color: red;'>❌ Database Creation Error:</h3><p>" . $e->getMessage() . "</p>");
}

try {
    // Create radio_stations table
    $pdo_init->exec("CREATE TABLE IF NOT EXISTS radio_stations (
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
    $pdo_init->exec("CREATE TABLE IF NOT EXISTS radio_schedules (
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
    $pdo_init->exec("CREATE TABLE IF NOT EXISTS tv_streams (
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
    $pdo_init->exec("CREATE TABLE IF NOT EXISTS podcasts (
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
    $pdo_init->exec("CREATE TABLE IF NOT EXISTS podcast_episodes (
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
    $pdo_init->exec("CREATE TABLE IF NOT EXISTS news_articles (
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
    $pdo_init->exec("CREATE TABLE IF NOT EXISTS users (
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

    // Insert sample KBC radio stations
    $pdo_init->exec("INSERT IGNORE INTO radio_stations (name, description, stream_url, category) VALUES
        ('KBC English Service', 'Kenya Broadcasting Corporation English Service', 'https://kbc.co.ke/live/kbc-english.m3u8', 'News'),
        ('KBC Swahili Service', 'Kenya Broadcasting Corporation Kiswahili Service', 'https://kbc.co.ke/live/kbc-swahili.m3u8', 'News'),
        ('KBC Radio Taifa', 'National radio station broadcasting in Swahili', 'https://kbc.co.ke/live/radio-taifa.m3u8', 'General'),
        ('KBC Coro FM', 'Contemporary music and entertainment', 'https://kbc.co.ke/live/coro-fm.m3u8', 'Music'),
        ('KBC Ingo FM', 'Regional station serving Central Kenya', 'https://kbc.co.ke/live/ingo-fm.m3u8', 'Regional'),
        ('KBC Mayienga FM', 'Serving the Luhya community', 'https://kbc.co.ke/live/mayienga-fm.m3u8', 'Regional')");
    echo "✅ Sample KBC radio stations added<br>";

    // Insert sample TV streams
    $pdo_init->exec("INSERT IGNORE INTO tv_streams (channel_name, description, stream_url, category) VALUES
        ('KBC Channel 1', 'Kenya Broadcasting Corporation TV Channel 1', 'https://kbc.co.ke/live/kbc-tv1.m3u8', 'General'),
        ('KBC Channel 2', 'Kenya Broadcasting Corporation TV Channel 2', 'https://kbc.co.ke/live/kbc-tv2.m3u8', 'Entertainment'),
        ('KBC News', 'Kenya Broadcasting Corporation News Channel', 'https://kbc.co.ke/live/kbc-news.m3u8', 'News')");
    echo "✅ Sample KBC TV streams added<br>";

    // Insert sample podcasts
    $pdo_init->exec("INSERT IGNORE INTO podcasts (title, description, host_name, category) VALUES
        ('KBC Morning Talk', 'Daily morning talk show covering current affairs', 'John Kiprotich', 'News'),
        ('Mazungumzo', 'Weekly discussions on social and political issues', 'Grace Wanjiku', 'Politics'),
        ('Tech Kenya', 'Technology trends and innovations in Kenya', 'David Mwangi', 'Technology'),
        ('Kenya Today', 'Daily news and analysis', 'Sarah Nyambura', 'News')");
    echo "✅ Sample podcasts added<br>";

    // Insert sample radio schedules
    $pdo_init->exec("INSERT IGNORE INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES
        (1, 'Morning Briefing', 'Peter Kimani', '06:00:00', '09:00:00', 'Monday', 'Start your day with the latest news'),
        (1, 'Afternoon Drive', 'Mary Wanjiku', '15:00:00', '18:00:00', 'Monday', 'Drive time news and music'),
        (2, 'Habari za Asubuhi', 'Joseph Mwangi', '06:30:00', '09:30:00', 'Monday', 'Morning news in Swahili'),
        (3, 'Mazungumzo ya Mchana', 'Grace Njeri', '12:00:00', '14:00:00', 'Monday', 'Midday discussions'),
        (4, 'Hit Parade', 'DJ Mike', '10:00:00', '12:00:00', 'Monday', 'Latest hits and music'),
        (5, 'Ingo News', 'Samuel Kariuki', '07:00:00', '08:00:00', 'Monday', 'Regional news for Central Kenya')");
    echo "✅ Sample radio schedules added<br>";

    // Insert sample news articles
    $pdo_init->exec("INSERT IGNORE INTO news_articles (title, content, summary, author, category, is_published, published_at) VALUES
        ('Kenya Economy Shows Strong Growth', 'Kenya\'s economy has demonstrated resilient growth in the fourth quarter, driven by strong performance in agriculture, manufacturing, and services sectors. The Kenya National Bureau of Statistics reported...', 'Kenya\'s economy records strong Q4 growth across multiple sectors.', 'Economic Desk', 'Business', 1, '2025-01-25 08:00:00'),
        ('New Broadcasting Regulations Announced', 'The Communications Authority of Kenya has announced new regulations for digital broadcasting, aimed at improving content quality and expanding reach to underserved areas...', 'CAK announces new broadcasting regulations for improved coverage.', 'Media Desk', 'Technology', 1, '2025-01-24 16:30:00'),
        ('KBC Launches Digital Platform', 'Kenya Broadcasting Corporation has launched its new digital streaming platform, making it easier for Kenyans worldwide to access local content...', 'KBC introduces new digital streaming service.', 'Tech Reporter', 'Technology', 1, '2025-01-23 11:15:00')");
    echo "✅ Sample news articles added<br>";

    // Create default admin user (password: admin123)
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo_init->exec("INSERT IGNORE INTO users (username, email, password_hash, role) VALUES
        ('admin', 'admin@kbcplus.local', '$admin_password', 'admin')");
    echo "✅ Default admin user created (username: admin, password: admin123)<br>";

    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>Your KBC Plus streaming website is now ready!</strong></p>";
    echo "<p><strong>Database:</strong> kbcplus</p>";
    echo "<p><strong>Admin Login:</strong> username=admin, password=admin123</p>";
    echo "<p><a href='index.html' style='color: blue; text-decoration: none; margin-right: 20px;'>📺 Go to Website</a>";
    echo "<a href='admin.html' style='color: green; text-decoration: none;'>⚙️ Admin Panel</a></p>";
    echo "<hr>";
    echo "<h4>Next Steps:</h4>";
    echo "<ul>";
    echo "<li>✅ Database is configured and ready</li>";
    echo "<li>📺 Visit your website at: <code>http://localhost/kbcplus/</code></li>";
    echo "<li>⚙️ Access admin panel to manage content</li>";
    echo "<li>🔧 Test the APIs and streaming functionality</li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Database Setup Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Common solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL service is running</li>";
    echo "<li>Check that MySQL is accessible on port 3306</li>";
    echo "<li>Ensure you have proper MySQL permissions</li>";
    echo "</ul>";
}
?>
