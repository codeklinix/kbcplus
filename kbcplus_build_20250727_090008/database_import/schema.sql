-- Streaming Website Database Schema
-- Run this script in phpMyAdmin or MySQL to create the database structure

CREATE DATABASE IF NOT EXISTS streaming_website;
USE streaming_website;

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

-- Users Table (for admin functionality)
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

-- Insert sample data
INSERT INTO radio_stations (name, description, stream_url, category) VALUES
('Classic Rock FM', 'The best classic rock hits', 'http://stream.example.com/classicrock', 'Rock'),
('Jazz Lounge', 'Smooth jazz 24/7', 'http://stream.example.com/jazz', 'Jazz'),
('News Radio 24', 'Latest news and current affairs', 'http://stream.example.com/news', 'News');

INSERT INTO tv_streams (channel_name, description, stream_url, category) VALUES
('News Channel 1', 'Breaking news and live coverage', 'http://stream.example.com/news1', 'News'),
('Sports TV', 'Live sports and highlights', 'http://stream.example.com/sports', 'Sports'),
('Music Videos', '24/7 music video channel', 'http://stream.example.com/music', 'Entertainment');

INSERT INTO podcasts (title, description, host_name, category) VALUES
('Tech Talk Daily', 'Latest technology news and reviews', 'John Smith', 'Technology'),
('Health & Wellness', 'Tips for a healthier lifestyle', 'Dr. Jane Doe', 'Health'),
('Business Insights', 'Market analysis and business strategies', 'Mike Johnson', 'Business');
