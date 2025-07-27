<?php
echo "=== Creating Static Build for Surge.sh ===\n\n";

$sourceDir = 'kbcplus';
$buildDir = 'surge_build';

// Create build directory
if (is_dir($buildDir)) {
    // Remove existing build directory
    function removeDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object)) {
                        removeDirectory($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    removeDirectory($buildDir);
}

mkdir($buildDir, 0755, true);
echo "✅ Created build directory: {$buildDir}\n";

// Copy static files
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    mkdir($dst, 0755, true);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                // Skip backend directory and other PHP-specific directories
                if (!in_array($file, ['backend', 'static_data'])) {
                    copyDirectory($src . '/' . $file, $dst . '/' . $file);
                }
            } else {
                // Only copy non-PHP files
                if (!preg_match('/\.(php)$/i', $file)) {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
    }
    closedir($dir);
}

// Copy all static files (HTML, CSS, JS, images)
copyDirectory($sourceDir, $buildDir);
echo "✅ Copied static files\n";

// Copy static data
if (is_dir($sourceDir . '/static_data')) {
    copyDirectory($sourceDir . '/static_data', $buildDir . '/api');
    echo "✅ Copied static data to /api directory\n";
}

// Create modified JavaScript file for static API calls
$mainJsPath = $buildDir . '/assets/js/main.js';
if (file_exists($mainJsPath)) {
    $mainJs = file_get_contents($mainJsPath);
    
    // Replace API calls with static JSON file calls
    $replacements = [
        "'backend/api/radio.php'" => "'api/radio.json'",
        "'backend/api/tv_streams.php'" => "'api/tv_streams.json'",
        "'backend/api/podcasts.php'" => "'api/podcasts.json'",
        "'backend/api/news.php'" => "'api/news.json'",
        "'backend/api/schedule.php'" => "'api/schedule.json'",
        "`backend/api/episodes.php?podcast_id=\${podcast.id}`" => "getEpisodesForPodcast(podcast.id)"
    ];
    
    foreach ($replacements as $search => $replace) {
        $mainJs = str_replace($search, $replace, $mainJs);
    }
    
    // Add helper function for podcast episodes
    $episodeHelper = "
// Helper function to get episodes for a podcast from static data
async function getEpisodesForPodcast(podcastId) {
    try {
        const response = await fetch('api/podcast_episodes.json');
        const allEpisodes = await response.json();
        return allEpisodes[podcastId] || [];
    } catch (error) {
        console.error('Error loading episodes:', error);
        return [];
    }
}

";
    
    $mainJs = $episodeHelper . $mainJs;
    
    file_put_contents($mainJsPath, $mainJs);
    echo "✅ Updated JavaScript for static API calls\n";
}

// Create a basic 200.html for SPA routing (if needed)
$html200 = file_get_contents($buildDir . '/index.html');
file_put_contents($buildDir . '/200.html', $html200);
echo "✅ Created 200.html for SPA routing\n";

// Create CNAME file (optional, for custom domain)
// file_put_contents($buildDir . '/CNAME', 'your-domain.com');

echo "\n=== Build Complete! ===\n";
echo "📁 Static build created in: {$buildDir}/\n";
echo "📋 Build includes:\n";
echo "  - HTML, CSS, JavaScript files\n";
echo "  - Static JSON API data\n";
echo "  - Assets and images\n";
echo "  - SPA routing support\n";

echo "\n🚀 Deploy to Surge:\n";
echo "1. Install Surge: npm install -g surge\n";
echo "2. Deploy: surge {$buildDir}\n";
echo "3. Follow prompts to set domain\n";

?>
