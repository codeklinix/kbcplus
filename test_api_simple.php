<?php
// Simple API test
header('Content-Type: text/html; charset=utf-8');
echo "<h2>API Test Results</h2>";

// Test radio API
echo "<h3>Radio API Test:</h3>";
try {
    require_once 'backend/config.php';
    
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT * FROM radio_stations WHERE is_active = 1");
        $stations = $stmt->fetchAll();
        
        echo "Found " . count($stations) . " radio stations:<br>";
        foreach ($stations as $station) {
            echo "- " . htmlspecialchars($station['name']) . "<br>";
        }
        
        if (count($stations) == 0) {
            echo "<br><strong>No stations found! Running INSERT statements...</strong><br>";
            
            // Try to insert sample data
            $insertSQL = "INSERT IGNORE INTO radio_stations (name, description, stream_url, category) VALUES
                ('Classic Rock FM', 'The best classic rock hits', 'https://streams.radiomast.io/7e0a6780-8c47-4c8c-b10f-2b9ee30763b6', 'Rock'),
                ('Jazz Lounge', 'Smooth jazz 24/7', 'https://streams.radiomast.io/smooth-jazz-stream', 'Jazz'),
                ('News Radio 24', 'Latest news and current affairs', 'https://streams.radiomast.io/news-radio-stream', 'News')";
            
            if ($pdo->exec($insertSQL)) {
                echo "✅ Sample data inserted!<br>";
                // Check again
                $stmt = $pdo->query("SELECT * FROM radio_stations WHERE is_active = 1");
                $stations = $stmt->fetchAll();
                echo "Now found " . count($stations) . " stations:<br>";
                foreach ($stations as $station) {
                    echo "- " . htmlspecialchars($station['name']) . "<br>";
                }
            } else {
                echo "❌ Failed to insert sample data<br>";
            }
        }
        
    } else {
        echo "❌ No database connection<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Direct API Call Test:</h3>";
$apiUrl = 'https://kbcplus.page.gd/backend/api/radio.php';
$response = @file_get_contents($apiUrl);
if ($response) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        echo "✅ API returned " . count($data) . " items<br>";
        echo "Sample data: <pre>" . htmlspecialchars(substr($response, 0, 300)) . "</pre>";
    } else {
        echo "❌ Invalid JSON response<br>";
        echo "Raw response: <pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "❌ No response from API<br>";
}

echo "<br><a href='index.html'>Back to website</a>";
?>
