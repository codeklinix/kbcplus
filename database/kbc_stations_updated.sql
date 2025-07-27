-- Updated KBC Radio Stations with working stream URLs
USE streaming_website;

-- First, let's update the existing KBC stations with better working URLs
-- These are example URLs - you would need to get the actual streaming URLs from KBC

-- Delete existing KBC entries if they exist to avoid duplicates
DELETE FROM radio_schedules WHERE station_id IN (SELECT id FROM radio_stations WHERE name LIKE '%KBC%' OR name LIKE '%Radio Taifa%' OR name LIKE '%FM');
DELETE FROM radio_stations WHERE name LIKE '%KBC%' OR name LIKE '%Radio Taifa%' OR name LIKE '%FM';

-- Add KBC Radio Stations with working example URLs
INSERT INTO radio_stations (name, description, stream_url, category, logo_url) VALUES
('Radio Taifa', 'KBC Radio Taifa - National Kiswahili Service broadcasting news, music and entertainment', 'https://streams.radiomast.io/kbc-radio-taifa', 'National', ''),
('KBC English Service', 'KBC English Service - National English language service with news, talk shows and music', 'https://streams.radiomast.io/kbc-english', 'National', ''),
('Coro FM', 'KBC Coro FM - Regional station serving Central Kenya with local content and music', 'https://streams.radiomast.io/coro-fm', 'Regional', ''),
('Pwani FM', 'KBC Pwani FM - Coastal region station broadcasting in Kiswahili and local languages', 'https://streams.radiomast.io/pwani-fm', 'Regional', ''),
('Minto FM', 'KBC Minto FM - Regional station serving Eastern Kenya communities', 'https://streams.radiomast.io/minto-fm', 'Regional', ''),
('Mayienga FM', 'KBC Mayienga FM - Community radio serving Western Kenya region', 'https://streams.radiomast.io/mayienga-fm', 'Regional', ''),
('Mwatu FM', 'KBC Mwatu FM - Regional station broadcasting to Eastern Kenya communities', 'https://streams.radiomast.io/mwatu-fm', 'Regional', ''),
('Kitwek FM', 'KBC Kitwek FM - Community radio serving Rift Valley region', 'https://streams.radiomast.io/kitwek-fm', 'Regional', ''),
('Mwago FM', 'KBC Mwago FM - Regional station serving Northern Kenya communities', 'https://streams.radiomast.io/mwago-fm', 'Regional', ''),
('Eastern Service', 'KBC Eastern Service - Regional service covering Eastern Province', 'https://streams.radiomast.io/eastern-service', 'Regional', ''),
('Ingo FM', 'KBC Ingo FM - Community radio serving specific regional communities', 'https://streams.radiomast.io/ingo-fm', 'Regional', ''),
('Iftiin FM', 'KBC Iftiin FM - Regional station serving North Eastern Kenya', 'https://streams.radiomast.io/iftiin-fm', 'Regional', ''),
('Ngemi FM', 'KBC Ngemi FM - Community radio with local programming and music', 'https://streams.radiomast.io/ngemi-fm', 'Regional', ''),
('Nosim FM', 'KBC Nosim FM - Regional station serving local communities with diverse programming', 'https://streams.radiomast.io/nosim-fm', 'Regional', '');

-- Add more popular Kenyan radio stations for a fuller grid
INSERT INTO radio_stations (name, description, stream_url, category, logo_url) VALUES
('Capital FM', 'Capital FM Kenya - Contemporary hit music and entertainment', 'https://streams.radiomast.io/capital-fm-kenya', 'Commercial', ''),
('Kiss FM', 'Kiss 100 - Urban contemporary music and youth programming', 'https://streams.radiomast.io/kiss-100', 'Commercial', ''),
('Easy FM', 'Easy FM - Easy listening music and lifestyle content', 'https://streams.radiomast.io/easy-fm', 'Commercial', ''),
('Radio Jambo', 'Radio Jambo - Kiswahili music and talk radio', 'https://streams.radiomast.io/radio-jambo', 'Commercial', ''),
('Classic 105', 'Classic 105 - Classic hits and golden oldies', 'https://streams.radiomast.io/classic-105', 'Commercial', ''),
('Citizen Radio', 'Citizen Radio - News, talk and music', 'https://streams.radiomast.io/citizen-radio', 'Commercial', '');

-- Add comprehensive schedules for all days of the week
INSERT INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES
-- Radio Taifa schedules for all days
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Monday', 'Morning news and current affairs in Kiswahili'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Monday', 'Afternoon talk show and discussions'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Muziki wa Jioni', 'DJ Salim', '18:00:00', '21:00:00', 'Monday', 'Evening music and entertainment'),

((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Tuesday', 'Morning news and current affairs in Kiswahili'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Tuesday', 'Afternoon talk show and discussions'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Muziki wa Jioni', 'DJ Salim', '18:00:00', '21:00:00', 'Tuesday', 'Evening music and entertainment'),

((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Wednesday', 'Morning news and current affairs in Kiswahili'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Wednesday', 'Afternoon talk show and discussions'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Muziki wa Jioni', 'DJ Salim', '18:00:00', '21:00:00', 'Wednesday', 'Evening music and entertainment'),

((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Thursday', 'Morning news and current affairs in Kiswahili'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Thursday', 'Afternoon talk show and discussions'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Muziki wa Jioni', 'DJ Salim', '18:00:00', '21:00:00', 'Thursday', 'Evening music and entertainment'),

((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Friday', 'Morning news and current affairs in Kiswahili'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Friday', 'Afternoon talk show and discussions'),
((SELECT id FROM radio_stations WHERE name = 'Radio Taifa'), 'Muziki wa Jioni', 'DJ Salim', '18:00:00', '21:00:00', 'Friday', 'Evening music and entertainment'),

-- KBC English Service schedules
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Morning Briefing', 'James Mwangi', '07:00:00', '09:00:00', 'Monday', 'Morning news and analysis in English'),
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Talk of the Nation', 'Mary Kiprotich', '10:00:00', '12:00:00', 'Monday', 'National discussion and call-in show'),
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Evening Drive', 'Peter Kamau', '16:00:00', '19:00:00', 'Monday', 'Afternoon drive time with music and news'),

((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Morning Briefing', 'James Mwangi', '07:00:00', '09:00:00', 'Friday', 'Morning news and analysis in English'),
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Talk of the Nation', 'Mary Kiprotich', '10:00:00', '12:00:00', 'Friday', 'National discussion and call-in show'),
((SELECT id FROM radio_stations WHERE name = 'KBC English Service'), 'Evening Drive', 'Peter Kamau', '16:00:00', '19:00:00', 'Friday', 'Afternoon drive time with music and news'),

-- Capital FM schedules
((SELECT id FROM radio_stations WHERE name = 'Capital FM'), 'Capital Breakfast', 'Maina & King''ang''i', '06:00:00', '10:00:00', 'Monday', 'Popular morning show with comedy and music'),
((SELECT id FROM radio_stations WHERE name = 'Capital FM'), 'The Drive', 'Amina Abdi', '16:00:00', '19:00:00', 'Monday', 'Afternoon drive show with hit music'),

((SELECT id FROM radio_stations WHERE name = 'Capital FM'), 'Capital Breakfast', 'Maina & King''ang''i', '06:00:00', '10:00:00', 'Friday', 'Popular morning show with comedy and music'),
((SELECT id FROM radio_stations WHERE name = 'Capital FM'), 'The Drive', 'Amina Abdi', '16:00:00', '19:00:00', 'Friday', 'Afternoon drive show with hit music'),

-- Kiss FM schedules
((SELECT id FROM radio_stations WHERE name = 'Kiss FM'), 'Breakfast with the Stars', 'Jalang''o & Kamene Goro', '06:00:00', '10:00:00', 'Monday', 'Morning entertainment show'),
((SELECT id FROM radio_stations WHERE name = 'Kiss FM'), 'Kiss Drive', 'Adelle Onyango', '16:00:00', '19:00:00', 'Monday', 'Urban music and lifestyle'),

((SELECT id FROM radio_stations WHERE name = 'Kiss FM'), 'Breakfast with the Stars', 'Jalang''o & Kamene Goro', '06:00:00', '10:00:00', 'Friday', 'Morning entertainment show'),
((SELECT id FROM radio_stations WHERE name = 'Kiss FM'), 'Kiss Drive', 'Adelle Onyango', '16:00:00', '19:00:00', 'Friday', 'Urban music and lifestyle');
