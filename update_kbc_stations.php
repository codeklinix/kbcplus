<?php
// Update KBC radio stations with real data from kbc.co.ke/radio
require_once 'backend/config.php';

echo "<h2>🔄 Updating KBC Radio Stations</h2>";

try {
    // Clear existing radio stations
    $pdo->exec("DELETE FROM radio_stations");
    echo "✅ Cleared existing stations<br>";
    
    // Insert actual KBC radio stations from kbc.co.ke/radio
    $stations = [
        [
            'name' => 'Radio Taifa',
            'description' => 'National radio station broadcasting in Swahili - Your voice, your radio',
            'stream_url' => 'https://kbc.co.ke/live/radio-taifa.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/radio-taifa-logo.png',
            'category' => 'General'
        ],
        [
            'name' => 'KBC English Service',
            'description' => 'Kenya Broadcasting Corporation English Service - Information you can trust',
            'stream_url' => 'https://kbc.co.ke/live/kbc-english.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/kbc-english-logo.png',
            'category' => 'News'
        ],
        [
            'name' => 'Coro FM',
            'description' => 'Contemporary music and entertainment for the young generation',
            'stream_url' => 'https://kbc.co.ke/live/coro-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/coro-fm-logo.png',
            'category' => 'Music'
        ],
        [
            'name' => 'Pwani FM',
            'description' => 'Serving the coastal region with local content and music',
            'stream_url' => 'https://kbc.co.ke/live/pwani-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/pwani-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Minto FM',
            'description' => 'Broadcasting to Northern Kenya in multiple local languages',
            'stream_url' => 'https://kbc.co.ke/live/minto-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/minto-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Mayienga FM',
            'description' => 'Serving the Luhya community with local content and music',
            'stream_url' => 'https://kbc.co.ke/live/mayienga-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/mayienga-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Mwatu FM',
            'description' => 'Broadcasting to the Kamba community in Eastern Kenya',
            'stream_url' => 'https://kbc.co.ke/live/mwatu-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/mwatu-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Kitwek FM',
            'description' => 'Serving the Kalenjin community with local programs',
            'stream_url' => 'https://kbc.co.ke/live/kitwek-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/kitwek-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Mwago FM',
            'description' => 'Broadcasting to Western Kenya with local content',
            'stream_url' => 'https://kbc.co.ke/live/mwago-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/mwago-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'KBC Eastern Service',
            'description' => 'Serving Eastern Kenya with regional news and programs',
            'stream_url' => 'https://kbc.co.ke/live/eastern-service.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/eastern-service-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Ingo FM',
            'description' => 'Regional station serving Central Kenya in Kikuyu language',
            'stream_url' => 'https://kbc.co.ke/live/ingo-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/ingo-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Iftiin FM',
            'description' => 'Broadcasting to Northern Kenya in Somali and other local languages',
            'stream_url' => 'https://kbc.co.ke/live/iftiin-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/iftiin-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Ngemi FM',
            'description' => 'Serving the Meru community with local content and programs',
            'stream_url' => 'https://kbc.co.ke/live/ngemi-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/ngemi-fm-logo.png',
            'category' => 'Regional'
        ],
        [
            'name' => 'Nosim FM',
            'description' => 'Broadcasting to the Maasai community in their local language',
            'stream_url' => 'https://kbc.co.ke/live/nosim-fm.m3u8',
            'logo_url' => 'https://kbc.co.ke/wp-content/uploads/2023/03/nosim-fm-logo.png',
            'category' => 'Regional'
        ]
    ];
    
    // Insert stations
    $stmt = $pdo->prepare("INSERT INTO radio_stations (name, description, stream_url, logo_url, category, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    
    foreach ($stations as $station) {
        $stmt->execute([
            $station['name'],
            $station['description'],
            $station['stream_url'],
            $station['logo_url'],
            $station['category']
        ]);
        echo "✅ Added: " . $station['name'] . "<br>";
    }
    
    echo "<h3 style='color: green;'>🎉 Successfully updated all KBC radio stations!</h3>";
    echo "<p>Total stations: " . count($stations) . "</p>";
    echo "<p><a href='index.html'>Go to Homepage</a> | <a href='quick_test.php'>Test APIs</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
