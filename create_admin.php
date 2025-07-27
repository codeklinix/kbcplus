<?php
// Create Admin User Script
require_once 'backend/config.php';

echo "<h2>Create Admin User</h2>";

try {
    // Check if users table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
    
    if (!$tableCheck) {
        echo "❌ Users table doesn't exist. Please run database setup first.<br>";
        echo "<a href='simple_setup.php'>Run Database Setup</a><br>";
        exit;
    }
    
    // Create admin user
    $username = 'admin';
    $email = 'admin@kbcplus.co.ke';
    $password = 'admin123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $existingUser = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $existingUser->execute([$username, $email]);
    
    if ($existingUser->fetch()) {
        echo "⚠️ Admin user already exists!<br>";
        echo "<strong>Login Details:</strong><br>";
        echo "Username: <code>admin</code><br>";
        echo "Password: <code>admin123</code><br>";
    } else {
        // Insert admin user
        $insertAdmin = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, 'admin', 1)");
        
        if ($insertAdmin->execute([$username, $email, $passwordHash])) {
            echo "✅ Admin user created successfully!<br>";
            echo "<strong>Login Details:</strong><br>";
            echo "Username: <code>admin</code><br>";
            echo "Password: <code>admin123</code><br>";
        } else {
            echo "❌ Failed to create admin user<br>";
        }
    }
    
    // Show all users in database
    echo "<br><h3>Current Users:</h3>";
    $users = $pdo->query("SELECT id, username, email, role, is_active, created_at FROM users")->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No users found in database.<br>";
    }
    
    echo "<br><h3>Next Steps:</h3>";
    echo "<p>1. <a href='login.html'>Try logging in</a> with the credentials above</p>";
    echo "<p>2. <a href='admin.html'>Go to Admin Panel</a> after logging in</p>";
    echo "<p>3. <a href='index.html'>Visit your website</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
