<?php
require_once 'backend/config.php';

echo "<h2>Database Status Check</h2>";

try {
    // Check if database connection works
    echo "<p>✓ Database connection successful</p>";
    
    // Get current database name
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "<p>Connected to database: <strong>$dbName</strong></p>";
    
    // List all tables in the database
    echo "<h3>Existing Tables:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p>❌ No tables found in the database!</p>";
        echo "<p>It looks like you need to run the schema.sql file first.</p>";
        echo "<p><strong>Please run the following steps:</strong></p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin</li>";
        echo "<li>Import the file: <code>database/schema.sql</code></li>";
        echo "<li>Then run this check again</li>";
        echo "</ol>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Check if users table exists
        if (!in_array('users', $tables)) {
            echo "<h3>Creating Missing Users Table...</h3>";
            
            $createUsersTable = "
                CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(100) UNIQUE NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('admin', 'editor', 'user') DEFAULT 'user',
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ";
            
            $pdo->exec($createUsersTable);
            echo "<p>✓ Users table created successfully!</p>";
            
            // Create default admin user
            $adminUsername = 'admin';
            $adminEmail = 'admin@kbc.co.ke';
            $adminPassword = 'admin123';
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            
            $insertAdmin = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role, is_active) 
                VALUES (?, ?, ?, 'admin', 1)
            ");
            
            if ($insertAdmin->execute([$adminUsername, $adminEmail, $passwordHash])) {
                echo "<p>✓ Default admin user created!</p>";
                echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>Login Credentials:</h4>";
                echo "<p><strong>Username:</strong> $adminUsername</p>";
                echo "<p><strong>Email:</strong> $adminEmail</p>";
                echo "<p><strong>Password:</strong> $adminPassword</p>";
                echo "</div>";
                echo "<p style='color: red;'><strong>⚠ IMPORTANT: Change this password after first login!</strong></p>";
            } else {
                echo "<p>❌ Failed to create admin user</p>";
            }
        } else {
            echo "<p>✓ Users table already exists</p>";
        }
        
        // Add missing columns to radio_stations if needed
        echo "<h3>Checking Radio Stations Table...</h3>";
        $stmt = $pdo->query("DESCRIBE radio_stations");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('display_order', $columns)) {
            try {
                $pdo->exec("ALTER TABLE radio_stations ADD COLUMN display_order INT DEFAULT 0 AFTER is_active");
                echo "<p>✓ Added display_order column to radio_stations</p>";
            } catch (PDOException $e) {
                echo "<p>⚠ Error adding display_order: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>✓ display_order column exists in radio_stations</p>";
        }
    }
    
    // Test connection to login endpoint
    echo "<h3>Testing Setup...</h3>";
    echo "<p>You can now try:</p>";
    echo "<ul>";
    echo "<li><a href='login.html' target='_blank'>Login Page</a></li>";
    echo "<li><a href='admin.html' target='_blank'>Admin Dashboard</a> (login first)</li>";
    echo "<li><a href='index.html' target='_blank'>Main Site</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Database Not Found!</h4>";
        echo "<p>The database 'streaming_website' doesn't exist. Please:</p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin</li>";
        echo "<li>Import the file: <code>database/schema.sql</code></li>";
        echo "<li>This will create the database and all required tables</li>";
        echo "<li>Then run this check again</li>";
        echo "</ol>";
        echo "</div>";
    }
}
?>
