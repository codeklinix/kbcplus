-- Sample data for testing the streaming website
USE streaming_website;

-- Insert sample radio schedules
INSERT INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES
(1, 'Morning Rock', 'DJ Mike', '06:00:00', '10:00:00', 'Monday', 'Start your Monday with the best rock hits'),
(1, 'Afternoon Drive', 'Sarah Johnson', '14:00:00', '18:00:00', 'Monday', 'Rock music for your drive home'),
(2, 'Jazz Morning', 'David Williams', '07:00:00', '11:00:00', 'Monday', 'Smooth jazz to start your day'),
(2, 'Evening Jazz', 'Maria Garcia', '19:00:00', '23:00:00', 'Monday', 'Relaxing jazz for your evening'),
(3, 'News Update', 'John Smith', '08:00:00', '09:00:00', 'Monday', 'Latest news and current affairs'),
(3, 'Talk Show', 'Lisa Brown', '10:00:00', '12:00:00', 'Monday', 'Interactive talk show with callers');

-- Insert sample podcast episodes
INSERT INTO podcast_episodes (podcast_id, title, description, audio_url, duration, episode_number, published_date) VALUES
(1, 'Latest iPhone Review', 'Comprehensive review of the latest iPhone model', 'http://example.com/audio/tech-talk-001.mp3', '45:30', 1, '2025-01-20'),
(1, 'AI in 2025', 'Discussion about artificial intelligence trends', 'http://example.com/audio/tech-talk-002.mp3', '52:15', 2, '2025-01-22'),
(2, 'Morning Meditation', 'Start your day with a peaceful meditation', 'http://example.com/audio/health-001.mp3', '20:00', 1, '2025-01-21'),
(2, 'Healthy Eating Tips', 'Simple tips for maintaining a healthy diet', 'http://example.com/audio/health-002.mp3', '35:45', 2, '2025-01-23'),
(3, 'Market Analysis', 'Weekly market trends and analysis', 'http://example.com/audio/business-001.mp3', '40:20', 1, '2025-01-19'),
(3, 'Startup Success Stories', 'Inspiring stories from successful entrepreneurs', 'http://example.com/audio/business-002.mp3', '55:10', 2, '2025-01-24');

-- Insert sample news articles
INSERT INTO news_articles (title, content, summary, author, category, is_published, published_at) VALUES
('Tech Giants Report Strong Q4 Earnings', 
'Major technology companies have reported stronger than expected earnings for the fourth quarter, driven by cloud computing and AI services growth. The results exceeded analyst expectations across the board.',
'Technology companies exceed Q4 earnings expectations with strong cloud and AI growth.',
'Sarah Thompson', 'Technology', 1, '2025-01-25 10:00:00'),

('New Breakthrough in Renewable Energy Storage', 
'Scientists have developed a new battery technology that could revolutionize renewable energy storage, making solar and wind power more reliable and cost-effective.',
'Revolutionary battery technology promises to improve renewable energy storage efficiency.',
'Dr. Michael Chen', 'Science', 1, '2025-01-25 08:30:00'),

('Global Economic Outlook for 2025', 
'Economic experts predict moderate growth for the global economy in 2025, with emerging markets showing particular strength despite ongoing geopolitical tensions.',
'Economists forecast moderate global growth with emerging markets leading the way.',
'Jennifer Martinez', 'Business', 1, '2025-01-24 16:45:00'),

('Major Sports Championship Results', 
'The championship finals concluded with an exciting match that went into overtime. Fans witnessed one of the most thrilling games in recent history.',
'Championship finals deliver thrilling overtime victory in historic match.',
'Tom Rodriguez', 'Sports', 1, '2025-01-24 22:15:00'),

('New Health Guidelines Released', 
'Health authorities have released updated guidelines for preventive care, emphasizing the importance of regular check-ups and lifestyle modifications.',
'Updated health guidelines stress preventive care and lifestyle changes.',
'Dr. Lisa Wang', 'Health', 1, '2025-01-24 14:20:00');

-- Add more sample schedules for different days
INSERT INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES
-- Tuesday schedules
(1, 'Tuesday Rock Block', 'DJ Alex', '06:00:00', '10:00:00', 'Tuesday', 'Rock music to power your Tuesday'),
(2, 'Jazz Cafe', 'Emma Davis', '07:00:00', '11:00:00', 'Tuesday', 'Cafe-style jazz for a relaxing morning'),
(3, 'Tuesday News Brief', 'Robert Johnson', '08:00:00', '09:00:00', 'Tuesday', 'Quick news updates for busy people'),

-- Wednesday schedules
(1, 'Midweek Rock', 'DJ Sam', '06:00:00', '10:00:00', 'Wednesday', 'Get through Wednesday with great rock'),
(2, 'Wednesday Wind Down', 'Carlos Rodriguez', '07:00:00', '11:00:00', 'Wednesday', 'Smooth jazz for midweek relaxation'),
(3, 'Current Affairs', 'Nancy Wilson', '08:00:00', '09:00:00', 'Wednesday', 'In-depth analysis of current events');

-- Update some URLs to use working example streams (Note: these are example URLs)
UPDATE radio_stations SET stream_url = 'https://streams.radiomast.io/7e0a6780-8c47-4c8c-b10f-2b9ee30763b6' WHERE id = 1;
UPDATE radio_stations SET stream_url = 'https://streams.radiomast.io/smooth-jazz-stream' WHERE id = 2;
UPDATE radio_stations SET stream_url = 'https://streams.radiomast.io/news-radio-stream' WHERE id = 3;

UPDATE tv_streams SET stream_url = 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4' WHERE id = 1;
UPDATE tv_streams SET stream_url = 'https://sample-videos.com/zip/10/mp4/SampleVideo_640x360_1mb.mp4' WHERE id = 2;
UPDATE tv_streams SET stream_url = 'https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_1mb.mp4' WHERE id = 3;
