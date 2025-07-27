<?php
require_once 'backend/config.php';

echo "=== Adding Sample Episodes for New Podcasts ===\n\n";

try {
    // Check if podcast_episodes table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'podcast_episodes'");
    if ($stmt->rowCount() == 0) {
        echo "Creating podcast_episodes table...\n";
        $pdo->exec("
            CREATE TABLE podcast_episodes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                podcast_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                audio_url VARCHAR(500),
                duration VARCHAR(20),
                episode_number INT,
                published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (podcast_id) REFERENCES podcasts(id) ON DELETE CASCADE
            )
        ");
        echo "✅ Created podcast_episodes table\n\n";
    }
    
    // Get the new podcasts (IDs 4-9)
    $stmt = $pdo->query('SELECT id, title FROM podcasts WHERE id >= 4 ORDER BY id');
    $podcasts = $stmt->fetchAll();
    
    // Sample episodes for each podcast
    $sampleEpisodes = [
        4 => [ // Morning Conversations
            [
                'title' => 'Breaking Down Kenya\'s Economic Outlook for 2025',
                'description' => 'Expert analysis on inflation, job market trends, and what families can expect in the coming year.',
                'duration' => '42:15',
                'episode_number' => 1
            ],
            [
                'title' => 'Community Heroes: Spotlight on Local Changemakers',
                'description' => 'Inspiring conversations with ordinary Kenyans doing extraordinary things in their communities.',
                'duration' => '38:30',
                'episode_number' => 2
            ]
        ],
        5 => [ // Kenya Heritage Stories
            [
                'title' => 'The Origins of Kikuyu Traditional Music',
                'description' => 'Exploring the rich musical heritage of the Kikuyu community with traditional musicians.',
                'duration' => '28:45',
                'episode_number' => 1
            ],
            [
                'title' => 'Sacred Sites: Mount Kenya\'s Cultural Significance',
                'description' => 'Understanding the spiritual and cultural importance of Mount Kenya to local communities.',
                'duration' => '32:20',
                'episode_number' => 2
            ]
        ],
        6 => [ // Business Today Kenya
            [
                'title' => 'Startup Success Stories: From Idea to IPO',
                'description' => 'Meet young entrepreneurs who transformed simple ideas into thriving businesses.',
                'duration' => '48:10',
                'episode_number' => 1
            ],
            [
                'title' => 'Digital Banking Revolution in Kenya',
                'description' => 'How mobile money and fintech are reshaping Kenya\'s financial landscape.',
                'duration' => '52:35',
                'episode_number' => 2
            ]
        ],
        7 => [ // Youth Voices Kenya
            [
                'title' => 'Gen Z and Climate Action: Young Voices for Change',
                'description' => 'Young Kenyans leading environmental conservation efforts in their communities.',
                'duration' => '35:50',
                'episode_number' => 1
            ],
            [
                'title' => 'Tech Innovation: Young Coders Changing Kenya',
                'description' => 'Meet the young programmers and app developers making waves in Kenya\'s tech scene.',
                'duration' => '40:15',
                'episode_number' => 2
            ]
        ],
        8 => [ // Health & Wellness Hour
            [
                'title' => 'Mental Health Awareness: Breaking the Stigma',
                'description' => 'Addressing mental health challenges and available support systems in Kenya.',
                'duration' => '43:25',
                'episode_number' => 1
            ],
            [
                'title' => 'Nutrition on a Budget: Healthy Eating for Kenyan Families',
                'description' => 'Practical tips for maintaining good nutrition without breaking the bank.',
                'duration' => '37:40',
                'episode_number' => 2
            ]
        ],
        9 => [ // Sports Corner Kenya
            [
                'title' => 'Kenya\'s Marathon Legacy: Training the Champions',
                'description' => 'Inside look at Kenya\'s world-renowned marathon training programs and athletes.',
                'duration' => '55:20',
                'episode_number' => 1
            ],
            [
                'title' => 'Football Fever: Analyzing the Premier League Season',
                'description' => 'Comprehensive review of the Kenyan Premier League with expert commentary.',
                'duration' => '62:15',
                'episode_number' => 2
            ]
        ]
    ];
    
    $totalAdded = 0;
    
    foreach ($sampleEpisodes as $podcastId => $episodes) {
        $podcastTitle = '';
        foreach ($podcasts as $podcast) {
            if ($podcast['id'] == $podcastId) {
                $podcastTitle = $podcast['title'];
                break;
            }
        }
        
        echo "Adding episodes for: {$podcastTitle}\n";
        
        foreach ($episodes as $episode) {
            // Check if episode already exists
            $checkStmt = $pdo->prepare('SELECT id FROM podcast_episodes WHERE podcast_id = ? AND title = ?');
            $checkStmt->execute([$podcastId, $episode['title']]);
            
            if ($checkStmt->fetch()) {
                echo "  ⚠️  Episode '{$episode['title']}' already exists, skipping...\n";
                continue;
            }
            
            // Add episode
            $stmt = $pdo->prepare('
                INSERT INTO podcast_episodes (podcast_id, title, description, duration, episode_number, audio_url) 
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            
            // Generate a sample audio URL (you can replace with real audio files)
            $audioUrl = "assets/audio/podcast_{$podcastId}_ep{$episode['episode_number']}.mp3";
            
            $stmt->execute([
                $podcastId,
                $episode['title'],
                $episode['description'],
                $episode['duration'],
                $episode['episode_number'],
                $audioUrl
            ]);
            
            echo "  ✅ Added episode: {$episode['title']} ({$episode['duration']})\n";
            $totalAdded++;
        }
        echo "\n";
    }
    
    echo "=== Summary ===\n";
    echo "✅ Successfully added {$totalAdded} podcast episodes!\n\n";
    
    // Show updated episode count per podcast
    echo "=== Episodes per Podcast ===\n";
    $stmt = $pdo->query('
        SELECT p.title, COUNT(pe.id) as episode_count 
        FROM podcasts p 
        LEFT JOIN podcast_episodes pe ON p.id = pe.podcast_id 
        GROUP BY p.id, p.title 
        ORDER BY p.id
    ');
    
    while ($row = $stmt->fetch()) {
        echo "{$row['title']}: {$row['episode_count']} episodes\n";
    }
    
    echo "\n🎧 Your podcasts now have engaging episodes ready for listeners!\n";
    echo "🌐 Visit: http://localhost/kbc/kbcplus/kbcplus/ to explore the full podcast experience.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
