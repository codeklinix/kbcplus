<?php
require_once 'youtube_config.php';

echo "<h2>YouTube API Test</h2>";

// Test API configuration
echo "<h3>Configuration</h3>";
echo "<p>API Key: " . substr(YOUTUBE_API_KEY, 0, 10) . "..." . substr(YOUTUBE_API_KEY, -5) . "</p>";
echo "<p>Channel ID: " . YOUTUBE_CHANNEL_ID . "</p>";

// Test basic API call
echo "<h3>Testing Channel Videos API Call</h3>";

$url = "https://www.googleapis.com/youtube/v3/search?key=" . YOUTUBE_API_KEY . "&channelId=" . YOUTUBE_CHANNEL_ID . "&part=snippet,id&order=date&maxResults=5";

echo "<p>URL: " . substr($url, 0, 80) . "...</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>API Response</h3>";
echo "<p>HTTP Code: $httpCode</p>";

if ($error) {
    echo "<p style='color: red;'>cURL Error: $error</p>";
}

if ($response) {
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        echo "<p style='color: red;'>API Error: " . $data['error']['message'] . "</p>";
        echo "<p>Error Code: " . $data['error']['code'] . "</p>";
    } else {
        echo "<p style='color: green;'>API call successful!</p>";
        echo "<p>Items found: " . (isset($data['items']) ? count($data['items']) : 0) . "</p>";
        
        if (isset($data['items']) && !empty($data['items'])) {
            echo "<h3>Sample Videos</h3>";
            foreach (array_slice($data['items'], 0, 3) as $item) {
                if ($item['id']['kind'] === 'youtube#video') {
                    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                    echo "<strong>" . htmlspecialchars($item['snippet']['title']) . "</strong><br>";
                    echo "Video ID: " . $item['id']['videoId'] . "<br>";
                    echo "Published: " . $item['snippet']['publishedAt'] . "<br>";
                    echo "Description: " . substr(htmlspecialchars($item['snippet']['description']), 0, 100) . "...<br>";
                    echo "</div>";
                }
            }
        }
    }
    
    echo "<h3>Raw Response (First 500 chars)</h3>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
} else {
    echo "<p style='color: red;'>No response received</p>";
}

// Test individual video details
echo "<h3>Testing Video Details API</h3>";
$testVideoId = "dQw4w9WgXcQ"; // Rick Roll as test
$videoDetails = getYouTubeVideoDetails($testVideoId, YOUTUBE_API_KEY);

if ($videoDetails) {
    echo "<p style='color: green;'>Video details API working!</p>";
    echo "<p>Test video title: " . $videoDetails['snippet']['title'] . "</p>";
} else {
    echo "<p style='color: red;'>Video details API failed</p>";
}
?>
