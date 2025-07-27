<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT * FROM news_articles 
        WHERE is_published = 1 
        ORDER BY published_at DESC, created_at DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $articles = $stmt->fetchAll();
    
    echo json_encode($articles);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
