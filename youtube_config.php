<?php
/**
 * YouTube Configuration for KBC+
 * 
 * Update this file with your YouTube channel information and API keys
 * to display your actual videos on the Live TV page
 */

// YouTube Data API Configuration
define('YOUTUBE_API_KEY', 'AIzaSyCGH9opfDlT92R3BDEEPQsGKdvMyq-4LHs');
define('YOUTUBE_CHANNEL_ID', 'UCypNjM5hP1qcUqQZe57jNfg');

// Your YouTube Videos Configuration
// Replace these with your actual YouTube video IDs
$youtubeVideos = [
    [
        'title' => 'KBC News Live - Breaking News Coverage',
        'description' => 'Live coverage of breaking news and current events from KBC News team.',
'youtube_id' => 'ACTUAL_VIDEO_ID_1', // Replace with your video ID
        'channel_name' => 'KBC News',
        'category' => 'News',
        'tags' => 'live,news,breaking,current events',
        'duration' => '0:00', // Will be fetched automatically if API key is provided
        'is_live' => true
    ],
    [
        'title' => 'KBC Sports Live - Football Highlights',
        'description' => 'Live sports coverage and highlights from local and international matches.',
'youtube_id' => 'ACTUAL_VIDEO_ID_2', // Replace with your video ID
        'channel_name' => 'KBC Sports',
        'category' => 'Sports',
        'tags' => 'live,sports,football,highlights',
        'duration' => '0:00',
        'is_live' => true
    ],
    [
        'title' => 'KBC Entertainment - Music Shows',
        'description' => 'Live music performances and entertainment shows featuring local artists.',
'youtube_id' => 'ACTUAL_VIDEO_ID_3', // Replace with your video ID
        'channel_name' => 'KBC Entertainment',
        'category' => 'Entertainment',
        'tags' => 'live,music,entertainment,shows',
        'duration' => '0:00',
        'is_live' => false
    ],
    [
        'title' => 'KBC Documentary - Wildlife Kenya',
        'description' => 'Explore Kenya\'s amazing wildlife and natural heritage.',
'youtube_id' => 'ACTUAL_VIDEO_ID_4', // Replace with your video ID
        'channel_name' => 'KBC Nature',
        'category' => 'Documentary',
        'tags' => 'wildlife,nature,kenya,documentary',
        'duration' => '0:00',
        'is_live' => false
    ],
    [
        'title' => 'KBC Talk Show - Current Affairs',
        'description' => 'Discussion of current political and social issues affecting Kenya.',
'youtube_id' => 'ACTUAL_VIDEO_ID_5', // Replace with your video ID
        'channel_name' => 'KBC Talk',
        'category' => 'Talk Show',
        'tags' => 'talk show,politics,current affairs',
        'duration' => '0:00',
        'is_live' => false
    ],
    [
        'title' => 'KBC Tech Today - Innovation Hub',
        'description' => 'Latest technology trends and innovations in Kenya and beyond.',
'youtube_id' => 'ACTUAL_VIDEO_ID_6', // Replace with your video ID
        'channel_name' => 'KBC Tech',
        'category' => 'Technology',
        'tags' => 'technology,innovation,tech news',
        'duration' => '0:00',
        'is_live' => false
    ]
];

/**
 * Function to fetch video details from YouTube API
 */
function getYouTubeVideoDetails($videoId, $apiKey) {
    if (empty($apiKey) || $apiKey === 'YOUR_YOUTUBE_API_KEY_HERE') {
        return null;
    }
    
    $url = "https://www.googleapis.com/youtube/v3/videos?id={$videoId}&key={$apiKey}&part=snippet,contentDetails,statistics";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['items'][0])) {
            return $data['items'][0];
        }
    }
    
    return null;
}

/**
 * Function to get videos from your YouTube channel
 */
function getChannelVideos($channelId, $apiKey, $maxResults = 10) {
    if (empty($apiKey) || $apiKey === 'YOUR_YOUTUBE_API_KEY_HERE') {
        return [];
    }
    
    $url = "https://www.googleapis.com/youtube/v3/search?key={$apiKey}&channelId={$channelId}&part=snippet,id&order=date&maxResults={$maxResults}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['items'])) {
            return $data['items'];
        }
    }
    
    return [];
}

/**
 * How to get your YouTube API key:
 * 
 * 1. Go to the Google Developers Console: https://console.developers.google.com/
 * 2. Create a new project or select an existing one
 * 3. Enable the YouTube Data API v3
 * 4. Create credentials (API key)
 * 5. Copy the API key and paste it above
 * 
 * How to get your YouTube Channel ID:
 * 
 * 1. Go to your YouTube channel
 * 2. Look at the URL - it will be something like:
 *    - youtube.com/channel/UC1234567890 (UC1234567890 is your channel ID)
 *    - OR youtube.com/c/YourChannelName (you'll need to look at page source for the channel ID)
 * 3. You can also use online tools to find your channel ID
 */

return $youtubeVideos;
?>
