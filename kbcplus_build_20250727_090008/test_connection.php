<?php
// Simple connection test - DELETE after successful deployment
require_once 'backend/config.php';
echo "<h2>KBC Plus - Connection Test</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
try {
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result["test"] == 1) {
        echo "<p style=\"color: green;\">Database connection successful!</p>";
    }
} catch (Exception $e) {
    echo "<p style=\"color: red;\">Database connection failed: " . $e->getMessage() . "</p>";
}
echo "<p><strong>Remember to delete this file after testing!</strong></p>";
?>
