-- KBC Plus - MariaDB Compatible Schema for InfinityFree
-- This schema is optimized for MariaDB and shared hosting environments

-- Set SQL mode for compatibility
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- Radio Stations Table
CREATE TABLE IF NOT EXISTS radio_stations (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    stream_url VARCHAR(500) NOT NULL,
    logo_url VARCHAR(500),
    category VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Schedule Table
CREATE TABLE IF NOT EXISTS radio_schedules (
    id INT(11) NOT NULL AUTO_INCREMENT,
    station_id INT(11) NOT NULL,
    show_name VARCHAR(255) NOT NULL,
    host_name VARCHAR(255),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (station_id) REFERENCES radio_stations(id) ON DELETE CASCADE,
    INDEX idx_station_day (station_id, day_of_week),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TV Streams Table
CREATE TABLE IF NOT EXISTS tv_streams (
    id INT(11) NOT NULL AUTO_INCREMENT,
    channel_name VARCHAR(255) NOT NULL,
    description TEXT,
    stream_url VARCHAR(500) NOT NULL,
    logo_url VARCHAR(500),
    category VARCHAR(100),
    is_live TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_live (is_live)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Podcasts Table
CREATE TABLE IF NOT EXISTS podcasts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    host_name VARCHAR(255),
    cover_image VARCHAR(500),
    category VARCHAR(100),
    rss_feed VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Podcast Episodes Table
CREATE TABLE IF NOT EXISTS podcast_episodes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    podcast_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    audio_url VARCHAR(500) NOT NULL,
    duration VARCHAR(20),
    episode_number INT(11),
    season_number INT(11) DEFAULT 1,
    published_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (podcast_id) REFERENCES podcasts(id) ON DELETE CASCADE,
    INDEX idx_podcast_episode (podcast_id, episode_number),
    INDEX idx_published (published_date),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News Articles Table
CREATE TABLE IF NOT EXISTS news_articles (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    summary TEXT,
    author VARCHAR(255),
    featured_image VARCHAR(500),
    category VARCHAR(100),
    tags TEXT,
    is_published TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_published (is_published, published_at),
    INDEX idx_category (category),
    INDEX idx_author (author)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Table (for admin functionality)
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_username (username),
    UNIQUE KEY unique_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample radio stations (KBC stations)
INSERT IGNORE INTO radio_stations (name, description, stream_url, category) VALUES
('KBC English Service', 'Kenya Broadcasting Corporation English Service', 'https://webradio.kbc.co.ke/english', 'News'),
('KBC Kiswahili Service', 'Kenya Broadcasting Corporation Kiswahili Service', 'https://webradio.kbc.co.ke/kiswahili', 'News'),
('KBC Central FM', 'Kenya Broadcasting Corporation Central FM', 'https://webradio.kbc.co.ke/central', 'Entertainment'),
('KBC Coast FM', 'Kenya Broadcasting Corporation Coast FM', 'https://webradio.kbc.co.ke/coast', 'Entertainment'),
('KBC Western FM', 'Kenya Broadcasting Corporation Western FM', 'https://webradio.kbc.co.ke/western', 'Entertainment');

-- Insert sample TV streams
INSERT IGNORE INTO tv_streams (channel_name, description, stream_url, category) VALUES
('KBC Channel 1', 'Kenya Broadcasting Corporation Channel 1', 'https://kbcchannel1.kbc.co.ke/live', 'News'),
('KTN News', 'Kenya Television Network News', 'https://ktnkenya.co.ke/live', 'News'),
('Citizen TV', 'Royal Media Services Citizen TV', 'https://citizentv.co.ke/live', 'Entertainment');

-- Insert sample podcasts
INSERT IGNORE INTO podcasts (title, description, host_name, category) VALUES
('KBC Business Talk', 'Weekly business insights and market analysis', 'Business Team', 'Business'),
('Tech Kenya', 'Technology trends and innovations in Kenya', 'Tech Team', 'Technology'),
('Health Matters', 'Health tips and medical advice', 'Dr. Sarah Mwangi', 'Health');

-- Create default admin user (password: admin123 - CHANGE THIS!)
-- Password hash for 'admin123' using PHP password_hash()
INSERT IGNORE INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@kbcplus.co.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Add some sample news articles
INSERT IGNORE INTO news_articles (title, content, summary, author, category, is_published, published_at) VALUES
('KBC Plus Launches', 'KBC Plus streaming platform is now live, offering the best of Kenyan broadcasting content online.', 'New streaming platform launches', 'KBC Team', 'Technology', 1, NOW()),
('Broadcasting Excellence', 'Committed to delivering quality content across all our platforms.', 'Quality content delivery', 'Editorial Team', 'News', 1, NOW());
