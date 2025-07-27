<?php
// Test database connection and check users table
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kbcplus', 'root', '');
    echo "✅ Database connection successful\n";
    
    // Check users table structure
    $stmt = $pdo->query('DESCRIBE users');
    echo "\nUsers table structure:\n";
    while($row = $stmt->fetch()) {
        echo $row['Field'] . ' | ' . $row['Type'] . "\n";
    }
    
    // Try to create admin user with correct column name
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute(['admin', 'admin@kbcplus.local', $admin_password, 'admin']);
    
    if ($result) {
        echo "\n✅ Admin user created successfully!\n";
    } else {
        echo "\n❌ Failed to create admin user\n";
    }
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
