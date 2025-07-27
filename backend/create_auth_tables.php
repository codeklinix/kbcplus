<?php
require_once 'config.php';

// SQL to create users table
$createUsersTable = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expires TIMESTAMP NULL
)";

// SQL to create user sessions table
$createSessionsTable = "
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// SQL to create user preferences table
$createPreferencesTable = "
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme VARCHAR(20) DEFAULT 'default',
    language VARCHAR(10) DEFAULT 'en',
    notifications BOOLEAN DEFAULT TRUE,
    favorite_stations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// SQL to create admin logs table
$createAdminLogsTable = "
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

try {
    // Create users table
    $pdo->exec($createUsersTable);
    echo "✓ Users table created successfully\n";
    
    // Create sessions table
    $pdo->exec($createSessionsTable);
    echo "✓ User sessions table created successfully\n";
    
    // Create preferences table
    $pdo->exec($createPreferencesTable);
    echo "✓ User preferences table created successfully\n";
    
    // Create admin logs table
    $pdo->exec($createAdminLogsTable);
    echo "✓ Admin logs table created successfully\n";
    
    // Create default admin user
    $adminUsername = 'admin';
    $adminEmail = 'admin@streaming.local';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $checkAdmin = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkAdmin->execute([$adminUsername]);
    
    if (!$checkAdmin->fetch()) {
        $insertAdmin = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role) 
            VALUES (?, ?, ?, 'admin')
        ");
        $insertAdmin->execute([$adminUsername, $adminEmail, $adminPassword]);
        
        $adminId = $pdo->lastInsertId();
        
        // Create default preferences for admin
        $insertPrefs = $pdo->prepare("
            INSERT INTO user_preferences (user_id, theme, language) 
            VALUES (?, 'glassmorphic', 'en')
        ");
        $insertPrefs->execute([$adminId]);
        
        echo "✓ Default admin user created successfully\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n";
        echo "  Email: admin@streaming.local\n";
    } else {
        echo "✓ Admin user already exists\n";
    }
    
    echo "\n✅ Authentication system setup completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Visit: http://localhost/streaming/login.html\n";
    echo "2. Login with admin/admin123\n";
    echo "3. Access admin panel at: http://localhost/streaming/admin/\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating tables: " . $e->getMessage() . "\n";
}
?>
