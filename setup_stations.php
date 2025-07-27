<?php
// Quick setup script to add KBC stations and test the homepage
require_once 'backend/config.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>StreamHub Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>StreamHub Database Setup</h1>
    
    <?php
    try {
        echo "<div class='info'>🔄 Setting up database and adding stations...</div>\n";
        
        // First, let's check if the database exists
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<div class='error'>❌ No tables found. Please import database/schema.sql first!</div>\n";
            echo "<p><strong>Instructions:</strong></p>";
            echo "<ol>";
            echo "<li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
            echo "<li>Click 'Import' tab</li>";
            echo "<li>Choose file: <code>database/schema.sql</code></li>";
            echo "<li>Click 'Go' to import</li>";
            echo "<li>Then run this script again</li>";
            echo "</ol>";
        } else {
            echo "<div class='success'>✅ Database tables exist: " . implode(', ', $tables) . "</div>\n";
            
            // Check current stations
            $stmt = $pdo->query("SELECT COUNT(*) FROM radio_stations");
            $stationCount = $stmt->fetchColumn();
            echo "<div class='info'>📻 Current stations in database: {$stationCount}</div>\n";
            
            // Add KBC stations if not many exist
            if ($stationCount < 10) {
                echo "<div class='info'>🔄 Adding KBC stations...</div>\n";
                
                // Clear existing stations to avoid duplicates
                $pdo->exec("DELETE FROM radio_schedules");
                $pdo->exec("DELETE FROM radio_stations");
                
                // Add KBC and popular Kenyan stations
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
                
                foreach ($stations as $station) {
                    $stmt = $pdo->prepare("INSERT INTO radio_stations (name, description, stream_url, category) VALUES (?, ?, ?, ?)");
                    $stmt->execute($station);
                }
                
                echo "<div class='success'>✅ Added " . count($stations) . " radio stations</div>\n";
                
                // Add some sample schedules using actual station IDs
                // Get the station IDs that were just inserted
                $stationIds = [];
                $stmt = $pdo->query("SELECT id, name FROM radio_stations ORDER BY id");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $stationIds[$row['name']] = $row['id'];
                }
                
                // Insert schedules using the actual station IDs
                $schedules = [
                    ['Radio Taifa', 'Habari za Asubuhi', 'Mwalimu Hassan', '06:00:00', '09:00:00', 'Friday', 'Morning news in Kiswahili'],
                    ['Radio Taifa', 'Mazungumzo ya Mchana', 'Bi. Amina', '12:00:00', '14:00:00', 'Friday', 'Afternoon discussions'],
                    ['KBC English Service', 'Morning Briefing', 'James Mwangi', '07:00:00', '09:00:00', 'Friday', 'English morning news'],
                    ['Capital FM', 'Capital Breakfast', 'Maina & Kingangi', '06:00:00', '10:00:00', 'Friday', 'Popular morning show'],
                    ['Kiss FM', 'Kiss Breakfast', 'Jalango & Kamene', '06:00:00', '10:00:00', 'Friday', 'Urban morning show']
                ];
                
                $scheduleStmt = $pdo->prepare("INSERT INTO radio_schedules (station_id, show_name, host_name, start_time, end_time, day_of_week, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($schedules as $schedule) {
                    $stationName = $schedule[0];
                    if (isset($stationIds[$stationName])) {
                        $scheduleStmt->execute([
                            $stationIds[$stationName],
                            $schedule[1], // show_name
                            $schedule[2], // host_name
                            $schedule[3], // start_time
                            $schedule[4], // end_time
                            $schedule[5], // day_of_week
                            $schedule[6]  // description
                        ]);
                    }
                }
                
                echo "<div class='success'>✅ Added sample schedules</div>\n";
            }
            
            // Test the API endpoints
            echo "<h2>🧪 Testing API Endpoints</h2>\n";
            
            $apiTests = [
                'radio.php' => 'Radio Stations',
                'tv.php' => 'TV Channels', 
                'podcasts.php' => 'Podcasts',
                'news.php' => 'News Articles'
            ];
            
            foreach ($apiTests as $endpoint => $name) {
                $url = "http://localhost/streaming/backend/api/$endpoint";
                echo "<div class='info'>Testing $name API...</div>\n";
                
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5
                    ]
                ]);
                
                $result = @file_get_contents($url, false, $context);
                if ($result !== false) {
                    $data = json_decode($result, true);
                    if (is_array($data)) {
                        echo "<div class='success'>✅ $name: " . count($data) . " items found</div>\n";
                    } else {
                        echo "<div class='error'>❌ $name: Invalid JSON response</div>\n";
                    }
                } else {
                    echo "<div class='error'>❌ $name: Failed to fetch data</div>\n";
                }
            }
            
            // Final check
            $stmt = $pdo->query("SELECT COUNT(*) FROM radio_stations");
            $finalCount = $stmt->fetchColumn();
            
            echo "<h2>🎉 Setup Complete!</h2>\n";
            echo "<div class='success'>✅ Total stations in database: {$finalCount}</div>\n";
            echo "<p><a href='index.html' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Go to StreamHub</a></p>\n";
            
            if ($finalCount > 0) {
                echo "<div class='info'>💡 The homepage should now show radio stations in the grid!</div>\n";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>\n";
        echo "<div class='info'>💡 Make sure XAMPP MySQL is running and the database exists.</div>\n";
    }
    ?>
    
    <h2>📋 Next Steps</h2>
    <ol>
        <li>Make sure XAMPP Apache and MySQL are running</li>
        <li>If you see errors above, import <code>database/schema.sql</code> in phpMyAdmin first</li>
        <li>Run this script again to add stations</li>
        <li>Visit <a href="index.html">your StreamHub homepage</a></li>
    </ol>
    
</body>
</html>
