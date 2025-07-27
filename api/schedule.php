<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Get current day of week
    $currentDay = date('l'); // Monday, Tuesday, etc.
    
    $stmt = $pdo->prepare("
        SELECT rs.*, s.name as station_name 
        FROM radio_schedules rs 
        JOIN radio_stations s ON rs.station_id = s.id 
        WHERE rs.day_of_week = ? AND rs.is_active = 1 AND s.is_active = 1
        ORDER BY rs.start_time
    ");
    $stmt->execute([$currentDay]);
    $schedules = $stmt->fetchAll();
    
    echo json_encode($schedules);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
