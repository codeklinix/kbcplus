<?php
require_once 'backend/config.php';

echo "=== Adding 6 Podcasts to KBC Plus ===\n\n";

try {
    // First, check current podcasts
    echo "Current podcasts:\n";
    $stmt = $pdo->query('SELECT id, title, host_name, category, description FROM podcasts ORDER BY id');
    $currentPodcasts = $stmt->fetchAll();
    
    foreach ($currentPodcasts as $podcast) {
        echo "ID: {$podcast['id']} | Title: {$podcast['title']} | Host: {$podcast['host_name']} | Category: {$podcast['category']}\n";
    }
    
    echo "\n--- Adding New Podcasts ---\n";
    
    // Define 6 engaging KBC podcasts
    $newPodcasts = [
        [
            'title' => 'Morning Conversations',
            'description' => 'Start your day with engaging discussions on current affairs, lifestyle, and community stories that matter to Kenyans.',
            'host_name' => 'Sarah Wanjiku',
            'category' => 'Talk',
            'language' => 'English',
            'duration_avg' => '45 minutes',
            'schedule' => 'Monday to Friday, 7:00 AM',
            'is_active' => 1
        ],
        [
            'title' => 'Kenya Heritage Stories',
            'description' => 'Discover the rich cultural heritage of Kenya through stories, traditions, and conversations with cultural experts.',
            'host_name' => 'Dr. James Mwangi',
            'category' => 'Culture',
            'language' => 'Swahili/English',
            'duration_avg' => '30 minutes',
            'schedule' => 'Wednesdays, 6:00 PM',
            'is_active' => 1
        ],
        [
            'title' => 'Business Today Kenya',
            'description' => 'Weekly insights into Kenya\'s business landscape, entrepreneurship tips, and economic analysis.',
            'host_name' => 'Grace Njeri',
            'category' => 'Business',
            'language' => 'English',
            'duration_avg' => '50 minutes',
            'schedule' => 'Saturdays, 10:00 AM',
            'is_active' => 1
        ],
        [
            'title' => 'Youth Voices Kenya',
            'description' => 'Platform for young Kenyans to share their stories, challenges, and innovations shaping the future.',
            'host_name' => 'Brian Kiprotich',
            'category' => 'Youth',
            'language' => 'English/Sheng',
            'duration_avg' => '35 minutes',
            'schedule' => 'Tuesdays, 8:00 PM',
            'is_active' => 1
        ],
        [
            'title' => 'Health & Wellness Hour',
            'description' => 'Expert advice on health, nutrition, mental wellness, and healthy living for Kenyan families.',
            'host_name' => 'Dr. Mary Wambui',
            'category' => 'Health',
            'language' => 'English',
            'duration_avg' => '40 minutes',
            'schedule' => 'Thursdays, 7:00 PM',
            'is_active' => 1
        ],
        [
            'title' => 'Sports Corner Kenya',
            'description' => 'Complete coverage of Kenyan sports, from football to athletics, featuring interviews with local sports personalities.',
            'host_name' => 'Peter Ochieng',
            'category' => 'Sports',
            'language' => 'English/Swahili',
            'duration_avg' => '60 minutes',
            'schedule' => 'Sundays, 4:00 PM',
            'is_active' => 1
        ]
    ];
    
    // Check table structure first
    $stmt = $pdo->query("DESCRIBE podcasts");
    $columns = $stmt->fetchAll();
    $columnNames = array_column($columns, 'Field');
    
    echo "Available columns: " . implode(', ', $columnNames) . "\n\n";
    
    // Prepare insert statement with only available columns
    $availableFields = ['title', 'description', 'host_name', 'category'];
    $optionalFields = ['language', 'duration_avg', 'schedule', 'is_active'];
    
    // Check which optional fields exist
    foreach ($optionalFields as $field) {
        if (in_array($field, $columnNames)) {
            $availableFields[] = $field;
        }
    }
    
    $placeholders = str_repeat('?,', count($availableFields) - 1) . '?';
    $fieldsList = implode(', ', $availableFields);
    
    $insertSQL = "INSERT INTO podcasts ({$fieldsList}) VALUES ({$placeholders})";
    $stmt = $pdo->prepare($insertSQL);
    
    $addedCount = 0;
    foreach ($newPodcasts as $podcast) {
        // Check if podcast already exists
        $checkStmt = $pdo->prepare('SELECT id FROM podcasts WHERE title = ?');
        $checkStmt->execute([$podcast['title']]);
        
        if ($checkStmt->fetch()) {
            echo "⚠️  Podcast '{$podcast['title']}' already exists, skipping...\n";
            continue;
        }
        
        // Prepare values array based on available fields
        $values = [];
        foreach ($availableFields as $field) {
            $values[] = isset($podcast[$field]) ? $podcast[$field] : null;
        }
        
        $stmt->execute($values);
        $addedCount++;
        
        echo "✅ Added: {$podcast['title']} (Host: {$podcast['host_name']}, Category: {$podcast['category']})\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "✅ Successfully added {$addedCount} new podcasts!\n\n";
    
    // Show updated list
    echo "=== Updated Podcasts List ===\n";
    $stmt = $pdo->query('SELECT id, title, host_name, category FROM podcasts ORDER BY id');
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']} | {$row['title']} | Host: {$row['host_name']} | Category: {$row['category']}\n";
    }
    
    echo "\n🎧 Your KBC Plus homepage now has engaging podcast content!\n";
    echo "🌐 Visit: http://localhost/kbc/kbcplus/kbcplus/ to see the updated podcasts section.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
