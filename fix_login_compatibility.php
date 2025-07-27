<?php
require_once 'backend/config.php';

echo "<h2>KBC+ Login Compatibility Fix</h2>\n";

try {
    // Add missing columns to the users table if they don't exist
    $stmt = $pdo->query("DESCRIBE users");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    echo "<h3>Current columns:</h3>\n";
    echo "<p>" . implode(', ', $columns) . "</p>\n";
    
    // Add is_active column if missing
    if (!in_array('is_active', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
        echo "<p>✅ Added is_active column</p>\n";
    } else {
        echo "<p>✅ is_active column already exists</p>\n";
    }
    
    // Set admin user as active
    $pdo->exec("UPDATE users SET is_active = 1 WHERE username = 'admin'");
    echo "<p>✅ Admin user set as active</p>\n";
    
    echo "<h3>📋 Login credentials (unchanged):</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    
    echo "<h3>✅ Fixed!</h3>";
    echo "<p>The login system has been updated to work with your existing table structure.</p>";
    
    echo "<p><a href='login.html'>Go to Login Page</a> | <a href='admin.html'>Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
p { margin: 10px 0; }
a { color: #667eea; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
