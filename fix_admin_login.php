<?php
// Fix admin login by creating/updating the admin user properly
require_once 'backend/config.php';

echo "<h2>KBC+ Admin Login Fix</h2>\n";

try {
    // First, check if users table exists, if not create it
    $checkTable = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($checkTable->rowCount() == 0) {
        echo "<p>Creating users table...</p>\n";
        
        $createUsersTable = "
        CREATE TABLE users (
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
        
        $pdo->exec($createUsersTable);
        echo "<p>✅ Users table created successfully</p>\n";
    } else {
        echo "<p>✅ Users table already exists</p>\n";
    }
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "<p>⚠️ Admin user already exists, updating password...</p>\n";
        
        // Update existing admin user
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ?, is_active = 1, role = 'admin' WHERE username = ?");
        $result = $updateStmt->execute([$newPassword, 'admin']);
        
        if ($result) {
            echo "<p>✅ Admin password updated successfully!</p>\n";
        } else {
            echo "<p>❌ Failed to update admin password</p>\n";
        }
    } else {
        echo "<p>🆕 Creating new admin user...</p>\n";
        
        // Create new admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active) 
            VALUES (?, ?, ?, 'admin', 1)
        ");
        $result = $insertStmt->execute(['admin', 'admin@kbcplus.local', $adminPassword]);
        
        if ($result) {
            echo "<p>✅ Admin user created successfully!</p>\n";
        } else {
            echo "<p>❌ Failed to create admin user</p>\n";
        }
    }
    
    echo "<hr>";
    echo "<h3>📋 Login Credentials:</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Email:</strong> admin@kbcplus.local</p>";
    echo "<p><strong>Role:</strong> admin</p>";
    
    echo "<hr>";
    echo "<h3>👥 Current Users in Database:</h3>";
    $stmt = $pdo->query("SELECT id, username, email, role, is_active FROM users ORDER BY id");
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th></tr>";
    
    while($user = $stmt->fetch()) {
        $activeStatus = $user['is_active'] ? 'Yes' : 'No';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . $activeStatus . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='login.html'>Go to Login Page</a></li>";
    echo "<li>Login with username: <code>admin</code> and password: <code>admin123</code></li>";
    echo "<li><a href='admin.html'>Access Admin Panel</a></li>";
    echo "<li><a href='index.html'>Back to Main Site</a></li>";
    echo "</ol>";
    
    echo "<p><em>✅ Admin login should now work properly!</em></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2 { color: #333; }
h3 { color: #666; }
p { margin: 10px 0; }
table { background: white; border-collapse: collapse; margin: 10px 0; }
th { background: #667eea; color: white; padding: 8px; }
td { padding: 8px; }
code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
a { color: #667eea; text-decoration: none; }
a:hover { text-decoration: underline; }
hr { margin: 20px 0; border: none; border-top: 1px solid #ddd; }
</style>
