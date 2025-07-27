<?php
// Complete fix for homepage grid issues
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>StreamHub Complete Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .step { margin: 15px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .btn { background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔧 StreamHub Complete Fix</h1>
    
    <?php
    $steps = [];
    $allSuccess = true;
    
    // Step 1: Check and create database
    try {
        echo "<div class='step info'><strong>Step 1:</strong> Setting up database...</div>\n";
        
        // Try to connect without database first
        $pdo_temp = new PDO(
            "mysql:host=localhost;charset=utf8mb4",
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create database if it doesn't exist
        $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS streaming_website");
        $pdo_temp->exec("USE streaming_website");
        
        // Now connect to the specific database
        $pdo = new PDO(
            "mysql:host=localhost;dbname=streaming_website;charset=utf8mb4",
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        echo "<div class='step success'>✅ Database connection successful</div>\n";
        
    } catch (Exception $e) {
        echo "<div class='step error'>❌ Database connection failed: " . $e->getMessage() . "</div>\n";
        echo "<div class='step error'>Please make sure XAMPP MySQL is running!</div>\n";
        $allSuccess = false;
        exit;
    }
    
    // Step 2: Create tables
    try {
        echo "<div class='step info'><strong>Step 2:</strong> Creating database tables...</div>\n";
        
        // Drop existing tables to start fresh
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tables = ['radio_schedules', 'podcast_episodes', 'radio_stations', 'tv_streams', 'podcasts', 'news_articles', 'users'];
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS $table");
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Create tables
        $sql = "
        -- Radio Stations Table
        CREATE TABLE radio_stations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            stream_url VARCHAR(500) NOT NULL,
            logo_url VARCHAR(500),
            category VARCHAR(100),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Radio Schedule Table
        CREATE TABLE radio_schedules (
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
        );

        -- TV Streams Table
        CREATE TABLE tv_streams (
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
        );

        -- Podcasts Table
        CREATE TABLE podcasts (
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
        );

        -- Podcast Episodes Table
        CREATE TABLE podcast_episodes (
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
        );

        -- News Articles Table
        CREATE TABLE news_articles (
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
        );

        -- Users Table
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'editor', 'user') DEFAULT 'user',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";
        
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "<div class='step success'>✅ Database tables created successfully</div>\n";
        
    } catch (Exception $e) {
        echo "<div class='step error'>❌ Error creating tables: " . $e->getMessage() . "</div>\n";
        $allSuccess = false;
    }
    
    // Step 3: Insert sample data
    try {
        echo "<div class='step info'><strong>Step 3:</strong> Adding KBC and sample stations...</div>\n";
        
        // Insert KBC and popular radio stations
        $stations = [
            ['Radio Taifa', 'KBC Radio Taifa - National Kiswahili Service', 'https://s3.radio.co/s3e555c6d9/listen', 'National'],
            ['KBC English Service', 'KBC English Service - National English service', 'https://s3.radio.co/s0ea98db2b/listen', 'National'],
            ['Coro FM', 'KBC Coro FM - Central Kenya regional station', 'https://s3.radio.co/s1e33f7f2c/listen', 'Regional'],
            ['Pwani FM', 'KBC Pwani FM - Coastal region station', 'https://s3.radio.co/s2f44a8a3d/listen', 'Regional'],
            ['Minto FM', 'KBC Minto FM - Eastern Kenya communities', 'https://s3.radio.co/s3c55b9b4e/listen', 'Regional'],
            ['Mayienga FM', 'KBC Mayienga FM - Western Kenya region', 'https://s3.radio.co/s4d66cac5f/listen', 'Regional'],
            ['Capital FM', 'Capital FM Kenya - Contemporary hit music', 'https://s3.radio.co/s5e77dbd60/listen', 'Commercial'],
            ['Kiss FM', 'Kiss 100 - Urban contemporary music', 'https://s3.radio.co/s6f88ece71/listen', 'Commercial'],
            ['Easy FM', 'Easy FM - Easy listening music', 'https://s3.radio.co/s7a99fdf82/listen', 'Commercial'],
            ['Radio Jambo', 'Radio Jambo - Kiswahili music and talk', 'https://s3.radio.co/s8baa0e093/listen', 'Commercial'],
            ['Classic 105', 'Classic 105 - Classic hits and oldies', 'https://s3.radio.co/s9cbb1f1a4/listen', 'Commercial'],
            ['Citizen Radio', 'Citizen Radio - News, talk and music', 'https://s3.radio.co/s0dcc202b5/listen', 'Commercial'],
            ['Kitwek FM', 'KBC Kitwek FM - Rift Valley region', 'https://s3.radio.co/s1edd313c6/listen', 'Regional'],
            ['Mwago FM', 'KBC Mwago FM - Northern Kenya', 'https://s3.radio.co/s2fee424d7/listen', 'Regional'],
            ['Iftiin FM', 'KBC Iftiin FM - North Eastern Kenya', 'https://s3.radio.co/s30ff535e8/listen', 'Regional'],
            ['Nosim FM', 'KBC Nosim FM - Local communities', 'https://s3.radio.co/s410a646f9/listen', 'Regional'],
            ['Mwatu FM', 'KBC Mwatu FM - Eastern communities', 'https://s3.radio.co/s521b7570a/listen', 'Regional'],
            ['Ngemi FM', 'KBC Ngemi FM - Community programming', 'https://s3.radio.co/s632c8681b/listen', 'Regional']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO radio_stations (name, description, stream_url, category) VALUES (?, ?, ?, ?)");
        foreach ($stations as $station) {
            $stmt->execute($station);
        }
        
        echo "<div class='step success'>✅ Added " . count($stations) . " radio stations</div>\n";
        
        // Add TV channels
        $tvChannels = [
            ['KBC Channel 1', 'Kenya Broadcasting Corporation main channel', 'https://streams.example.com/kbc1', 'National', 1],
            ['Citizen TV', 'Citizen Television - News and entertainment', 'https://streams.example.com/citizen', 'National', 1],
            ['NTV Kenya', 'Nation Television - News and current affairs', 'https://streams.example.com/ntv', 'National', 1],
            ['KTN News', 'Kenya Television Network - 24/7 news', 'https://streams.example.com/ktn', 'News', 1],
            ['Inooro TV', 'Kikuyu language television', 'https://streams.example.com/inooro', 'Regional', 1],
            ['K24 TV', 'K24 Television - Entertainment and news', 'https://streams.example.com/k24', 'Entertainment', 1]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO tv_streams (channel_name, description, stream_url, category, is_live) VALUES (?, ?, ?, ?, ?)");
        foreach ($tvChannels as $channel) {
            $stmt->execute($channel);
        }
        
        echo "<div class='step success'>✅ Added " . count($tvChannels) . " TV channels</div>\n";
        
        // Add podcasts
        $podcasts = [
            ['Tech Talk Kenya', 'Technology discussions and reviews', 'John Kamau', 'Technology'],
            ['Kenyan Business Weekly', 'Business insights and market analysis', 'Sarah Wanjiku', 'Business'],
            ['Health Matters', 'Health tips and medical advice', 'Dr. Mary Njeri', 'Health'],
            ['Sports Corner', 'Kenyan sports news and analysis', 'Peter Ochieng', 'Sports'],
            ['Culture & Arts', 'Kenyan culture and artistic expressions', 'Grace Akinyi', 'Culture'],
            ['Education Focus', 'Educational content and career guidance', 'Prof. David Mwangi', 'Education']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO podcasts (title, description, host_name, category) VALUES (?, ?, ?, ?)");
        foreach ($podcasts as $podcast) {
            $stmt->execute($podcast);
        }
        
        echo "<div class='step success'>✅ Added " . count($podcasts) . " podcasts</div>\n";
        
        // Add podcast episodes
        $episodes = [
            [1, 'Latest Smartphone Reviews', 'Review of the newest smartphones in the Kenyan market', 'https://example.com/audio/tech1.mp3', '45:30', 1, '2025-01-20'],
            [1, 'AI and Machine Learning in Kenya', 'How AI is transforming businesses in Kenya', 'https://example.com/audio/tech2.mp3', '52:15', 2, '2025-01-22'],
            [2, 'NSE Market Analysis', 'Weekly analysis of Nairobi Securities Exchange', 'https://example.com/audio/business1.mp3', '35:20', 1, '2025-01-21'],
            [3, 'Managing Diabetes', 'Tips for managing diabetes in Kenya', 'https://example.com/audio/health1.mp3', '28:45', 1, '2025-01-19'],
            [4, 'Kenyan Premier League Update', 'Latest from Kenyan football', 'https://example.com/audio/sports1.mp3', '40:15', 1, '2025-01-23'],
            [5, 'Kikuyu Traditional Music', 'Exploring traditional Kikuyu musical heritage', 'https://example.com/audio/culture1.mp3', '55:30', 1, '2025-01-18']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO podcast_episodes (podcast_id, title, description, audio_url, duration, episode_number, published_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($episodes as $episode) {
            $stmt->execute($episode);
        }
        
        echo "<div class='step success'>✅ Added " . count($episodes) . " podcast episodes</div>\n";
        
        // Add news articles
        $newsArticles = [
            ['Kenya Economy Shows Strong Growth', 'Kenya\'s economy has shown resilient growth in the last quarter, driven by strong performance in agriculture and technology sectors.', 'Economic growth driven by agriculture and tech sectors', 'Business Reporter', 'Business', 1, '2025-01-25 10:00:00'],
            ['New Technology Hub Opens in Nairobi', 'A new technology innovation hub has opened in Nairobi, aimed at supporting local startups and fostering innovation.', 'New tech hub supports local startups and innovation', 'Tech Correspondent', 'Technology', 1, '2025-01-25 08:30:00'],
            ['Education Reform Bill Passes Parliament', 'Parliament has passed the new education reform bill that will modernize Kenya\'s education system.', 'Parliament approves major education system reforms', 'Education Reporter', 'Education', 1, '2025-01-24 16:45:00'],
            ['Kenya Wildlife Conservation Success', 'Kenya\'s wildlife conservation efforts show remarkable success with increasing animal populations.', 'Wildlife conservation efforts yield positive results', 'Environment Writer', 'Environment', 1, '2025-01-24 14:20:00'],
            ['Sports: Kenya Dominates East African Games', 'Kenyan athletes continue their dominance at the East African Games with multiple gold medals.', 'Kenyan athletes excel at regional games', 'Sports Correspondent', 'Sports', 1, '2025-01-24 12:15:00'],
            ['Healthcare Improvements Across Counties', 'Significant improvements in healthcare infrastructure have been reported across various Kenyan counties.', 'Healthcare infrastructure sees major improvements', 'Health Reporter', 'Health', 1, '2025-01-23 18:30:00']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO news_articles (title, content, summary, author, category, is_published, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($newsArticles as $article) {
            $stmt->execute($article);
        }
        
        echo "<div class='step success'>✅ Added " . count($newsArticles) . " news articles</div>\n";
        
        // Add radio schedules
        $schedules = [
            [1, 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Friday', 'Morning news in Kiswahili'],
            [1, 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Friday', 'Afternoon discussions'],
            [2, 'Morning Briefing', 'James Mwangi', '07:00:00', '09:00:00', 'Friday', 'English morning news'],
            [7, 'Capital Breakfast', 'Maina & Kingangi', '06:00:00', '10:00:00', 'Friday', 'Popular morning show'],
            [8, 'Kiss Breakfast', 'Jalango & Kamene', '06:00:00', '10:00:00', 'Friday', 'Urban morning show'],
            [9, 'Easy Drive', 'Mike Mondo', '16:00:00', '19:00:00', 'Friday', 'Easy listening drive time'],
            [10, 'Jambo Drive', 'Gidi Gidi', '16:00:00', '20:00:00', 'Friday', 'Kiswahili drive time'),
            [11, 'Classic Hits', 'DJ Classic', '14:00:00', '18:00:00', 'Friday', 'Classic music hits']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($schedules as $schedule) {
            $stmt->execute($schedule);
        }
        
        echo "<div class='step success'>✅ Added " . count($schedules) . " radio schedules</div>\n";
        
    } catch (Exception $e) {
        echo "<div class='step error'>❌ Error adding sample data: " . $e->getMessage() . "</div>\n";
        $allSuccess = false;
    }
    
    // Step 4: Test APIs
    try {
        echo "<div class='step info'><strong>Step 4:</strong> Testing API endpoints...</div>\n";
        
        $apiTests = [
            'radio.php' => 'Radio Stations',
            'tv.php' => 'TV Channels',
            'podcasts.php' => 'Podcasts',
            'news.php' => 'News Articles',
            'schedule.php' => 'Radio Schedule'
        ];
        
        foreach ($apiTests as $endpoint => $name) {
            $url = "http://localhost/streaming/backend/api/$endpoint";
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            $result = @file_get_contents($url, false, $context);
            
            if ($result !== false) {
                $data = json_decode($result, true);
                if (is_array($data)) {
                    echo "<div class='step success'>✅ $name API: " . count($data) . " items found</div>\n";
                } else {
                    echo "<div class='step error'>❌ $name API: Invalid JSON response</div>\n";
                    $allSuccess = false;
                }
            } else {
                echo "<div class='step error'>❌ $name API: Failed to fetch data</div>\n";
                $allSuccess = false;
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='step error'>❌ Error testing APIs: " . $e->getMessage() . "</div>\n";
        $allSuccess = false;
    }
    
    // Final status
    if ($allSuccess) {
        echo "<div class='step success'><strong>🎉 SUCCESS! All systems are working!</strong></div>\n";
        echo "<div class='step info'>";
        echo "<strong>Next steps:</strong><br>";
        echo "1. <a href='index.html' target='_blank'>Visit your StreamHub homepage</a><br>";
        echo "2. You should now see radio stations, TV channels, podcasts, and news in the grids<br>";
        echo "3. Press F12 and check the console for any remaining issues<br>";
        echo "4. <a href='debug.html' target='_blank'>Run the debug tool</a> if you still have issues";
        echo "</div>\n";
    } else {
        echo "<div class='step error'><strong>❌ Some issues were found. Please check the errors above.</strong></div>\n";
    }
    
    // Show final database statistics
    try {
        $radioCount = $pdo->query("SELECT COUNT(*) FROM radio_stations")->fetchColumn();
        $tvCount = $pdo->query("SELECT COUNT(*) FROM tv_streams")->fetchColumn();
        $podcastCount = $pdo->query("SELECT COUNT(*) FROM podcasts")->fetchColumn();
        $newsCount = $pdo->query("SELECT COUNT(*) FROM news_articles")->fetchColumn();
        
        echo "<div class='step info'>";
        echo "<strong>📊 Database Summary:</strong><br>";
        echo "Radio Stations: $radioCount<br>";
        echo "TV Channels: $tvCount<br>";
        echo "Podcasts: $podcastCount<br>";
        echo "News Articles: $newsCount";
        echo "</div>\n";
        
    } catch (Exception $e) {
        echo "<div class='step warning'>Could not get database statistics: " . $e->getMessage() . "</div>\n";
    }
    ?>
    
</body>
</html>
