<?php
require_once 'backend/config.php';

echo "=== Balancing KBC Plus Content: 5 Live TV, 5 News, 5 Podcasts ===\n\n";

try {
    // Check news table structure first
    echo "Checking news_articles table structure...\n";
    $stmt = $pdo->query('DESCRIBE news_articles');
    $columns = $stmt->fetchAll();
    $columnNames = array_column($columns, 'Field');
    echo "Available columns: " . implode(', ', $columnNames) . "\n\n";
    
    // Check current counts
    echo "Current content counts:\n";
    
    // Live TV
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM tv_streams WHERE is_active = 1');
    $tvCount = $stmt->fetch()['count'];
    echo "- Live TV channels: {$tvCount}\n";
    
    // News
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM news_articles');
    $newsCount = $stmt->fetch()['count'];
    echo "- News articles: {$newsCount}\n";
    
    // Podcasts
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM podcasts');
    $podcastCount = $stmt->fetch()['count'];
    echo "- Podcasts: {$podcastCount}\n\n";
    
    // === Add more News articles if needed ===
    if ($newsCount < 5) {
        echo "Adding more News articles...\n";
        
        $newNewsArticles = [
            [
                'title' => 'Kenya\'s Economic Growth Projected to Rise in 2025',
                'content' => 'Economic analysts predict Kenya\'s GDP growth will accelerate to 5.2% in 2025, driven by improved agricultural output and increased infrastructure investment. The positive outlook comes amid government reforms and strategic partnerships with international development organizations.',
                'summary' => 'Kenya\'s economy expected to grow by 5.2% in 2025 according to economic analysts.',
                'author' => 'James Mwangi',
                'category' => 'Business'
            ],
            [
                'title' => 'New Education Reforms Transform Learning in Rural Schools',
                'content' => 'The Ministry of Education launches comprehensive reforms targeting rural schools across Kenya. The initiative includes digital learning platforms, teacher training programs, and improved infrastructure to bridge the education gap between urban and rural areas.',
                'summary' => 'Education Ministry introduces major reforms to improve rural school learning outcomes.',
                'author' => 'Grace Wanjiku',
                'category' => 'Education'
            ],
            [
                'title' => 'Climate Change: Kenya Leads East Africa in Green Energy',
                'content' => 'Kenya has emerged as East Africa\'s leader in renewable energy adoption, with 90% of electricity now coming from clean sources. The country\'s geothermal, solar, and wind projects serve as a model for sustainable development across the continent.',
                'summary' => 'Kenya achieves 90% renewable energy, leading East Africa in green initiatives.',
                'author' => 'Dr. Peter Kiprotich',
                'category' => 'Environment'
            ],
            [
                'title' => 'Youth Innovation Hub Opens in Nairobi',
                'content' => 'A state-of-the-art innovation hub dedicated to young entrepreneurs officially opens in Nairobi. The facility will provide mentorship, funding opportunities, and technological resources to support Kenya\'s next generation of innovators and startup founders.',
                'summary' => 'New innovation hub in Nairobi supports young entrepreneurs and tech startups.',
                'author' => 'Sarah Njoroge',
                'category' => 'Technology'
            ],
            [
                'title' => 'Healthcare Access Improves with Mobile Clinic Initiative',
                'content' => 'The government launches a mobile clinic program targeting remote communities across Kenya. These fully-equipped medical units will provide essential healthcare services, vaccinations, and health education to underserved populations in rural areas.',
                'summary' => 'Mobile clinics expand healthcare access to remote Kenyan communities.',
                'author' => 'Dr. Mary Wambui',
                'category' => 'Health'
            ]
        ];
        
        // Determine which fields to use based on available columns
        $requiredFields = ['title', 'content', 'author', 'category'];
        $optionalFields = ['summary'];
        
        $useFields = $requiredFields;
        foreach ($optionalFields as $field) {
            if (in_array($field, $columnNames)) {
                $useFields[] = $field;
            }
        }
        
        $placeholders = str_repeat('?,', count($useFields) - 1) . '?';
        $fieldsList = implode(', ', $useFields);
        
        foreach ($newNewsArticles as $article) {
            $stmt = $pdo->prepare('SELECT id FROM news_articles WHERE title = ?');
            $stmt->execute([$article['title']]);
            
            if (!$stmt->fetch()) {
                $insertStmt = $pdo->prepare("INSERT INTO news_articles ({$fieldsList}) VALUES ({$placeholders})");
                
                $values = [];
                foreach ($useFields as $field) {
                    $values[] = isset($article[$field]) ? $article[$field] : null;
                }
                
                $insertStmt->execute($values);
                echo "✅ Added news article: {$article['title']}\n";
            }
        }
    }
    
    // Update final counts
    echo "\n=== Final Content Balance ===\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM tv_streams WHERE is_active = 1');
    $finalTVCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM news_articles');
    $finalNewsCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM podcasts');
    $finalPodcastCount = $stmt->fetch()['count'];
    
    echo "✅ Live TV channels: {$finalTVCount} (showing 5 on homepage)\n";
    echo "✅ News articles: {$finalNewsCount} (showing 5 on homepage)\n";
    echo "✅ Podcasts: {$finalPodcastCount} (showing 6 on homepage)\n";
    
    echo "\n🎯 Your KBC Plus homepage is now perfectly balanced!\n";
    echo "📺 Live TV: Latest channels including YouTube stream\n";
    echo "📰 News: Fresh articles covering various topics\n";  
    echo "🎧 Podcasts: Engaging shows with multiple episodes\n";
    echo "\n🌐 Visit: http://localhost/kbc/kbcplus/kbcplus/ to see the balanced content!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
