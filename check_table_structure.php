<?php
require_once 'backend/config.php';

echo "<h2>Database Table Structure Check</h2>\n";

try {
    // Check if users table exists and show its structure
    $stmt = $pdo->query("DESCRIBE users");
    
    echo "<h3>Current 'users' table structure:</h3>\n";
    echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>\n";
    
    // Show current users
    echo "<h3>Current users in the table:</h3>\n";
    $userStmt = $pdo->query("SELECT * FROM users");
    echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Column</th><th>Role</th><th>Active</th></tr>\n";
    
    while ($user = $userStmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
        
        // Check which password column exists
        if (isset($user['password_hash'])) {
            echo "<td>password_hash: " . substr($user['password_hash'], 0, 20) . "...</td>";
        } elseif (isset($user['password'])) {
            echo "<td>password: " . substr($user['password'], 0, 20) . "...</td>";
        } else {
            echo "<td>No password column found</td>";
        }
        
        echo "<td>" . htmlspecialchars($user['role'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($user['is_active'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>\n";
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
table { background: white; border-collapse: collapse; margin: 10px 0; }
th { background: #667eea; color: white; padding: 8px; }
td { padding: 8px; }
</style>
