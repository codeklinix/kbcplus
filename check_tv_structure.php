<?php
require_once 'backend/config.php';

echo "=== Checking TV Streams Table Structure ===\n\n";

try {
    // Check if tv_streams table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tv_streams'");
    if ($stmt->rowCount() == 0) {
        echo "❌ tv_streams table doesn't exist!\n";
        echo "Let me check what tables exist:\n\n";
        
        $stmt = $pdo->query("SHOW TABLES");
        echo "Available tables:\n";
        while ($row = $stmt->fetch()) {
            echo "- " . array_values($row)[0] . "\n";
        }
    } else {
        echo "✅ tv_streams table exists\n\n";
        
        // Show table structure
        echo "Table structure:\n";
        $stmt = $pdo->query("DESCRIBE tv_streams");
        while ($row = $stmt->fetch()) {
            echo "Column: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Default: {$row['Default']}\n";
        }
        
        echo "\n--- Current Records ---\n";
        $stmt = $pdo->query("SELECT * FROM tv_streams LIMIT 5");
        $columns = [];
        while ($row = $stmt->fetch()) {
            if (empty($columns)) {
                $columns = array_keys($row);
                echo "Columns: " . implode(" | ", $columns) . "\n\n";
            }
            foreach ($row as $key => $value) {
                echo "{$key}: {$value}\n";
            }
            echo "---\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
