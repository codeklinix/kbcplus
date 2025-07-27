<?php
// Create admin user for existing database structure
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kbcplus', 'root', '');
    echo "✅ Database connection successful\n";
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $exists = $stmt->fetch()['count'] > 0;
    
    if ($exists) {
        echo "⚠️ Admin user already exists, updating password...\n";
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $result = $stmt->execute([$admin_password, 'admin']);
    } else {
        echo "🆕 Creating new admin user...\n";
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['admin', 'admin@kbcplus.local', $admin_password, 'admin']);
    }
    
    if ($result) {
        echo "✅ Admin user configured successfully!\n";
        echo "📋 Login credentials:\n";
        echo "   Username: admin\n";
        echo "   Password: admin123\n";
        echo "   Email: admin@kbcplus.local\n";
    } else {
        echo "❌ Failed to configure admin user\n";
    }
    
    // Show current users
    echo "\n👥 Current users in database:\n";
    $stmt = $pdo->query("SELECT id, username, email, role FROM users");
    while($row = $stmt->fetch()) {
        echo "- ID: " . $row['id'] . ", Username: " . $row['username'] . ", Email: " . $row['email'] . ", Role: " . $row['role'] . "\n";
    }
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
