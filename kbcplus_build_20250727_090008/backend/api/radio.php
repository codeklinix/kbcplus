<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT * FROM radio_stations WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $stations = $stmt->fetchAll();
    
    echo json_encode($stations);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
