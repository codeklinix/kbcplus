<?php
echo "<h2>Testing Login API</h2>\n";

// Simulate a login request
$loginData = [
    'username' => 'admin',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/kbcplus/backend/api/login.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Login Test Results:</h3>\n";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>\n";
echo "<p><strong>Response:</strong></p>\n";
echo "<pre>" . htmlspecialchars($response) . "</pre>\n";

$responseData = json_decode($response, true);
if ($responseData && isset($responseData['success']) && $responseData['success']) {
    echo "<p style='color: green;'>✅ <strong>Login Successful!</strong></p>\n";
    echo "<p>You can now log in with:</p>\n";
    echo "<ul>\n";
    echo "<li>Username: admin</li>\n";
    echo "<li>Password: admin123</li>\n";
    echo "</ul>\n";
} else {
    echo "<p style='color: red;'>❌ <strong>Login Failed</strong></p>\n";
    if (isset($responseData['error'])) {
        echo "<p>Error: " . htmlspecialchars($responseData['error']) . "</p>\n";
    }
}

echo "<p><a href='login.html'>Try logging in</a> | <a href='admin.html'>Admin Panel</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
pre { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
a { color: #667eea; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
