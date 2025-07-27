-- Update schema to add missing columns for admin functionality

USE streaming_website;

-- Add display_order column to radio_stations if it doesn't exist
ALTER TABLE radio_stations 
ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0 AFTER is_active;

-- Add display_order column to tv_streams if it doesn't exist  
ALTER TABLE tv_streams 
ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0 AFTER is_active;

-- Update TV streams table to have consistent column names
ALTER TABLE tv_streams 
CHANGE COLUMN channel_name name VARCHAR(255) NOT NULL;

-- Add display_order column to podcasts if it doesn't exist
ALTER TABLE podcasts 
ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0 AFTER is_active;

-- Add display_order column to news_articles if it doesn't exist
ALTER TABLE news_articles 
ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0 AFTER is_published;

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@kbc.co.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Xe4sD4NI.5pJ3p/2s8v5tq2eXUa3X9', 'admin');

-- Create settings table for site configuration
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'KBC +'),
('site_description', 'Your premier streaming destination for radio, TV, podcasts, and news.'),
('contact_email', 'feedback@kbc.co.ke'),
('items_per_page', '12'),
('cache_duration', '60');
