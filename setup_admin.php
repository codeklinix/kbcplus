<?php
require_once 'backend/config.php';

echo "<h2>KBC+ Admin Setup</h2>";

try {
    // Check if database connection works
    echo "<p>✓ Database connection successful</p>";
    
    // Run schema updates
    echo "<h3>Updating Database Schema...</h3>";
    
    // Add display_order column to radio_stations if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE radio_stations ADD COLUMN display_order INT DEFAULT 0 AFTER is_active");
        echo "<p>✓ Added display_order column to radio_stations</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>- display_order column already exists in radio_stations</p>";
        } else {
            echo "<p>⚠ Error updating radio_stations: " . $e->getMessage() . "</p>";
        }
    }
    
    // Add display_order column to tv_streams if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE tv_streams ADD COLUMN display_order INT DEFAULT 0 AFTER is_active");
        echo "<p>✓ Added display_order column to tv_streams</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>- display_order column already exists in tv_streams</p>";
        } else {
            echo "<p>⚠ Error updating tv_streams: " . $e->getMessage() . "</p>";
        }
    }
    
    // Update TV streams table to have consistent column names
    try {
        $pdo->exec("ALTER TABLE tv_streams CHANGE COLUMN channel_name name VARCHAR(255) NOT NULL");
        echo "<p>✓ Updated tv_streams column name</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Unknown column 'channel_name'") !== false) {
            echo "<p>- tv_streams column name already updated</p>";
        } else {
            echo "<p>⚠ Error updating tv_streams column: " . $e->getMessage() . "</p>";
        }
    }
    
    // Create default admin user
    echo "<h3>Creating Admin User...</h3>";
    
    $adminUsername = 'admin';
    $adminEmail = 'admin@kbc.co.ke';
    $adminPassword = 'admin123'; // Change this to a secure password
    
    // Check if admin user already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->execute([$adminUsername, $adminEmail]);
    
    if ($checkStmt->fetch()) {
        echo "<p>- Admin user already exists</p>";
    } else {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active, created_at) 
            VALUES (?, ?, ?, 'admin', 1, NOW())
        ");
        
        if ($insertStmt->execute([$adminUsername, $adminEmail, $passwordHash])) {
            echo "<p>✓ Admin user created successfully</p>";
            echo "<p><strong>Login Details:</strong></p>";
            echo "<p>Username: $adminUsername</p>";
            echo "<p>Email: $adminEmail</p>";
            echo "<p>Password: $adminPassword</p>";
            echo "<p style='color: red;'><strong>⚠ IMPORTANT: Change the admin password after first login!</strong></p>";
        } else {
            echo "<p>❌ Failed to create admin user</p>";
        }
    }
    
    // Test radio stations
    echo "<h3>Sample Data...</h3>";
    
    $sampleStations = [
        ['KBC English Service', 'https://stream.zeno.fm/0cbdcdmvge5tv', 'news'],
        ['KBC Kiswahili Service', 'https://stream.zeno.fm/kvcmgvr8a1zuv', 'news'],
        ['KBC Central FM', 'https://stream.zeno.fm/x4vnazww29zuv', 'music']
    ];
    
    foreach ($sampleStations as $station) {
        $checkStation = $pdo->prepare("SELECT id FROM radio_stations WHERE name = ?");
        $checkStation->execute([$station[0]]);
        
        if (!$checkStation->fetch()) {
            $insertStation = $pdo->prepare("
                INSERT INTO radio_stations (name, stream_url, category, description, is_active, display_order, created_at) 
                VALUES (?, ?, ?, ?, 1, 0, NOW())
            ");
            $insertStation->execute([
                $station[0], 
                $station[1], 
                $station[2], 
                'Sample KBC radio station'
            ]);
            echo "<p>✓ Added sample station: {$station[0]}</p>";
        } else {
            echo "<p>- Sample station already exists: {$station[0]}</p>";
        }
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li><a href='login.html'>Login to Admin Panel</a></li>";
    echo "<li><a href='admin.html'>Go to Admin Dashboard</a> (after login)</li>";
    echo "<li><a href='index.html'>Visit Main Site</a></li>";
    echo "</ul>";
    
    echo "<p style='color: orange;'><strong>Security Note:</strong> Delete this setup file (setup_admin.php) after setup is complete for security.</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
