<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT * FROM podcasts WHERE is_active = 1 ORDER BY title");
    $stmt->execute();
    $podcasts = $stmt->fetchAll();
    
    echo json_encode($podcasts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
