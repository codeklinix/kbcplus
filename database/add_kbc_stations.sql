-- Add KBC Radio Stations from kbc.co.ke
USE streaming_website;

-- Insert KBC Radio Stations
INSERT INTO radio_stations (name, description, stream_url, category, logo_url) VALUES
('Radio Taifa', 'KBC Radio Taifa - National Kiswahili Service broadcasting news, music and entertainment', 'https://kbc.co.ke/live/radio-taifa', 'National', ''),
('KBC English Service', 'KBC English Service - National English language service with news, talk shows and music', 'https://kbc.co.ke/live/english-service', 'National', ''),
('Coro FM', 'KBC Coro FM - Regional station serving Central Kenya with local content and music', 'https://kbc.co.ke/live/coro-fm', 'Regional', ''),
('Pwani FM', 'KBC Pwani FM - Coastal region station broadcasting in Kiswahili and local languages', 'https://kbc.co.ke/live/pwani-fm', 'Regional', ''),
('Minto FM', 'KBC Minto FM - Regional station serving Eastern Kenya communities', 'https://kbc.co.ke/live/minto-fm', 'Regional', ''),
('Mayienga FM', 'KBC Mayienga FM - Community radio serving Western Kenya region', 'https://kbc.co.ke/live/mayienga-fm', 'Regional', ''),
('Mwatu FM', 'KBC Mwatu FM - Regional station broadcasting to Eastern Kenya communities', 'https://kbc.co.ke/live/mwatu-fm', 'Regional', ''),
('Kitwek FM', 'KBC Kitwek FM - Community radio serving Rift Valley region', 'https://kbc.co.ke/live/kitwek-fm', 'Regional', ''),
('Mwago FM', 'KBC Mwago FM - Regional station serving Northern Kenya communities', 'https://kbc.co.ke/live/mwago-fm', 'Regional', ''),
('Eastern Service', 'KBC Eastern Service - Regional service covering Eastern Province', 'https://kbc.co.ke/live/eastern-service', 'Regional', ''),
('Ingo FM', 'KBC Ingo FM - Community radio serving specific regional communities', 'https://kbc.co.ke/live/ingo-fm', 'Regional', ''),
('Iftiin FM', 'KBC Iftiin FM - Regional station serving North Eastern Kenya', 'https://kbc.co.ke/live/iftiin-fm', 'Regional', ''),
('Ngemi FM', 'KBC Ngemi FM - Community radio with local programming and music', 'https://kbc.co.ke/live/ngemi-fm', 'Regional', ''),
('Nosim FM', 'KBC Nosim FM - Regional station serving local communities with diverse programming', 'https://kbc.co.ke/live/nosim-fm', 'Regional', '');

-- Add some KBC radio schedules for today (you can modify the day as needed)
INSERT INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES
-- Radio Taifa schedules
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Friday', 'Morning news and current affairs in Kiswahili'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Friday', 'Afternoon talk show and discussions'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Muziki wa Jioni', 'DJ Salim', '18:00:00', '21:00:00', 'Friday', 'Evening music and entertainment'),

-- KBC English Service schedules
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Morning Briefing', 'James Mwangi', '07:00:00', '09:00:00', 'Friday', 'Morning news and analysis in English'),
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Talk of the Nation', 'Mary Kiprotich', '10:00:00', '12:00:00', 'Friday', 'National discussion and call-in show'),
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Evening Drive', 'Peter Kamau', '16:00:00', '19:00:00', 'Friday', 'Afternoon drive time with music and news'),

-- Coro FM schedules
((SELECT id FROM radio_stations WHERE name = 'Coro FM'), 'Coro Asubuhi', 'Samuel Njoroge', '06:30:00', '09:30:00', 'Friday', 'Morning show with local news and music'),
((SELECT id FROM radio_stations WHERE name = 'Coro FM'), 'Mugithi Time', 'Grace Wanjiku', '19:00:00', '22:00:00', 'Friday', 'Traditional Kikuyu music and entertainment'),

-- Pwani FM schedules
((SELECT id FROM radio_stations WHERE name = 'Pwani FM'), 'Bahari ya Asubuhi', 'Ahmed Salim', '06:00:00', '09:00:00', 'Friday', 'Coastal morning show with Taarab and local news'),
((SELECT id FROM radio_stations WHERE name = 'Pwani FM'), 'Kilifi Nights', 'Fatma Omar', '20:00:00', '23:00:00', 'Friday', 'Evening coastal music and talk');

-- Update stream URLs to use placeholder URLs (these would need to be actual streaming URLs)
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/radio-taifa' WHERE name = 'Radio Taifa';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/english-service' WHERE name = 'KBC English Service';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/coro-fm' WHERE name = 'Coro FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/pwani-fm' WHERE name = 'Pwani FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/minto-fm' WHERE name = 'Minto FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/mayienga-fm' WHERE name = 'Mayienga FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/mwatu-fm' WHERE name = 'Mwatu FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/kitwek-fm' WHERE name = 'Kitwek FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/mwago-fm' WHERE name = 'Mwago FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/eastern-service' WHERE name = 'Eastern Service';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/ingo-fm' WHERE name = 'Ingo FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/iftiin-fm' WHERE name = 'Iftiin FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/ngemi-fm' WHERE name = 'Ngemi FM';
UPDATE radio_stations SET stream_url = 'https://streams.kbc.co.ke/nosim-fm' WHERE name = 'Nosim FM';
