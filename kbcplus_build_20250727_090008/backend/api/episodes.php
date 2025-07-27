<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $podcast_id = $_GET['podcast_id'] ?? null;
    
    if (!$podcast_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Podcast ID is required']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM podcast_episodes 
        WHERE podcast_id = ? AND is_active = 1 
        ORDER BY published_date DESC, episode_number DESC
    ");
    $stmt->execute([$podcast_id]);
    $episodes = $stmt->fetchAll();
    
    echo json_encode($episodes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
