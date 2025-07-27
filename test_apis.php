<?php
// Simple API test script to diagnose homepage grid issues
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test-section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .warning { color: #ff9800; }
        .api-data { background: #f9f9f9; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        button { background: #2196F3; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #1976D2; }
    </style>
</head>
<body>
    <h1>🔧 StreamHub API Debug Test</h1>
    
    <div class="test-section">
        <h2>📍 Current Directory & File Check</h2>
        <?php
        echo "<p><strong>Current directory:</strong> " . getcwd() . "</p>";
        echo "<p><strong>Expected API directory:</strong> " . getcwd() . "/backend/api/</p>";
        
        $apiDir = getcwd() . "/backend/api/";
        if (is_dir($apiDir)) {
            echo "<p class='success'>✅ API directory exists</p>";
            $apiFiles = ['radio.php', 'tv.php', 'podcasts.php', 'news.php', 'schedule.php'];
            foreach ($apiFiles as $file) {
                $filePath = $apiDir . $file;
                if (file_exists($filePath)) {
                    echo "<p class='success'>✅ $file exists</p>";
                } else {
                    echo "<p class='error'>❌ $file missing</p>";
                }
            }
        } else {
            echo "<p class='error'>❌ API directory does not exist</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>🔌 Database Connection Test</h2>
        <?php
        try {
            include_once 'backend/config/config.php';
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='success'>✅ Database connection successful</p>";
            
            // Test if tables exist
            $tables = ['radio_stations', 'tv_channels', 'podcasts', 'news_articles', 'radio_schedule'];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                    $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                    echo "<p class='success'>✅ Table '$table' exists with $count records</p>";
                } else {
                    echo "<p class='error'>❌ Table '$table' does not exist</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>🧪 API Endpoint Tests</h2>
        <button onclick="testAPI('radio')">Test Radio API</button>
        <button onclick="testAPI('tv')">Test TV API</button>
        <button onclick="testAPI('podcasts')">Test Podcasts API</button>
        <button onclick="testAPI('news')">Test News API</button>
        <button onclick="testAPI('schedule')">Test Schedule API</button>
        <button onclick="testAllAPIs()">Test All APIs</button>
        
        <div id="api-results"></div>
    </div>

    <div class="test-section">
        <h2>🏠 Homepage Grid Element Test</h2>
        <button onclick="testHomepageElements()">Test Homepage Elements</button>
        <div id="homepage-results"></div>
    </div>

    <div class="test-section">
        <h2>🌐 JavaScript Console Test</h2>
        <p>Check your browser's console (F12) for JavaScript errors when loading the homepage.</p>
        <button onclick="simulateHomepageLoad()">Simulate Homepage Load</button>
        <div id="js-results"></div>
    </div>

    <script>
        async function testAPI(endpoint) {
            const resultsDiv = document.getElementById('api-results');
            resultsDiv.innerHTML += `<h3>Testing ${endpoint}.php...</h3>`;
            
            try {
                const response = await fetch(`backend/api/${endpoint}.php`);
                const responseText = await response.text();
                
                if (response.ok) {
                    try {
                        const data = JSON.parse(responseText);
                        resultsDiv.innerHTML += `
                            <div class="api-data">
                                <p class="success">✅ ${endpoint}.php - Status: ${response.status}</p>
                                <p><strong>Records found:</strong> ${Array.isArray(data) ? data.length : 'Not an array'}</p>
                                <details>
                                    <summary>View Response Data</summary>
                                    <pre>${JSON.stringify(data, null, 2)}</pre>
                                </details>
                            </div>
                        `;
                    } catch (e) {
                        resultsDiv.innerHTML += `
                            <div class="api-data">
                                <p class="warning">⚠️ ${endpoint}.php - Response not valid JSON</p>
                                <details>
                                    <summary>View Raw Response</summary>
                                    <pre>${responseText}</pre>
                                </details>
                            </div>
                        `;
                    }
                } else {
                    resultsDiv.innerHTML += `
                        <div class="api-data">
                            <p class="error">❌ ${endpoint}.php - Status: ${response.status}</p>
                            <pre>${responseText}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultsDiv.innerHTML += `
                    <div class="api-data">
                        <p class="error">❌ ${endpoint}.php - Network Error: ${error.message}</p>
                    </div>
                `;
            }
        }

        async function testAllAPIs() {
            document.getElementById('api-results').innerHTML = '<h3>🚀 Testing All APIs...</h3>';
            const endpoints = ['radio', 'tv', 'podcasts', 'news', 'schedule'];
            
            for (const endpoint of endpoints) {
                await testAPI(endpoint);
                await new Promise(resolve => setTimeout(resolve, 500)); // Small delay
            }
        }

        function testHomepageElements() {
            const resultsDiv = document.getElementById('homepage-results');
            resultsDiv.innerHTML = '<h3>Testing Homepage Grid Elements...</h3>';
            
            // Create a temporary iframe to load the homepage and test elements
            const iframe = document.createElement('iframe');
            iframe.src = 'index.html';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            iframe.onload = function() {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                const elements = [
                    'home-radio-stations',
                    'home-tv-channels', 
                    'home-podcasts',
                    'home-news'
                ];
                
                elements.forEach(elementId => {
                    const element = doc.getElementById(elementId);
                    if (element) {
                        resultsDiv.innerHTML += `<p class="success">✅ Element '${elementId}' exists</p>`;
                        resultsDiv.innerHTML += `<p>Content: ${element.innerHTML.length > 0 ? 'Has content (' + element.innerHTML.length + ' chars)' : 'Empty'}</p>`;
                    } else {
                        resultsDiv.innerHTML += `<p class="error">❌ Element '${elementId}' not found</p>`;
                    }
                });
                
                // Check if main.js loaded
                const scripts = doc.getElementsByTagName('script');
                let mainJsFound = false;
                for (let script of scripts) {
                    if (script.src && script.src.includes('main.js')) {
                        mainJsFound = true;
                        break;
                    }
                }
                resultsDiv.innerHTML += mainJsFound ? 
                    "<p class='success'>✅ main.js script tag found</p>" : 
                    "<p class='error'>❌ main.js script tag not found</p>";
                
                document.body.removeChild(iframe);
            };
        }

        async function simulateHomepageLoad() {
            const resultsDiv = document.getElementById('js-results');
            resultsDiv.innerHTML = '<h3>🔄 Simulating Homepage Content Load...</h3>';
            
            try {
                // Simulate the loadHomeContent function
                console.log('🔄 Testing home page content loading...');
                
                // Test radio API
                console.log('📻 Testing radio stations...');
                const radioResponse = await fetch('backend/api/radio.php');
                console.log('Radio response status:', radioResponse.status);
                
                if (radioResponse.ok) {
                    const radioStations = await radioResponse.json();
                    console.log('Radio stations loaded:', radioStations.length);
                    resultsDiv.innerHTML += `<p class="success">✅ Radio API working - ${radioStations.length} stations</p>`;
                } else {
                    throw new Error(`Radio API error: ${radioResponse.status}`);
                }
                
                // Test TV API
                const tvResponse = await fetch('backend/api/tv.php');
                if (tvResponse.ok) {
                    const tvChannels = await tvResponse.json();
                    resultsDiv.innerHTML += `<p class="success">✅ TV API working - ${tvChannels.length} channels</p>`;
                }
                
                // Test Podcasts API  
                const podcastsResponse = await fetch('backend/api/podcasts.php');
                if (podcastsResponse.ok) {
                    const podcasts = await podcastsResponse.json();
                    resultsDiv.innerHTML += `<p class="success">✅ Podcasts API working - ${podcasts.length} podcasts</p>`;
                }
                
                // Test News API
                const newsResponse = await fetch('backend/api/news.php');
                if (newsResponse.ok) {
                    const newsArticles = await newsResponse.json();
                    resultsDiv.innerHTML += `<p class="success">✅ News API working - ${newsArticles.length} articles</p>`;
                }
                
                resultsDiv.innerHTML += '<p class="success">✅ All API simulations completed! Check browser console for detailed logs.</p>';
                
            } catch (error) {
                console.error('❌ Error in simulation:', error);
                resultsDiv.innerHTML += `<p class="error">❌ Error: ${error.message}</p>`;
            }
        }

        // Auto-run basic tests on page load
        window.onload = function() {
            console.log('🔧 API Debug Test page loaded');
            console.log('Ready to test StreamHub APIs and homepage elements');
        };
    </script>
</body>
</html>
