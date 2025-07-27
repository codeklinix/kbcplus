<?php
require_once 'backend/config.php';

try {
    $stmt = $pdo->query('SELECT id, name FROM radio_stations ORDER BY id');
    echo "Radio Stations in database:\n";
    while($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . " - " . $row['name'] . "\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
