<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get the action from query parameter
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($action) {
        case 'add':
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }
            addRadioStation();
            break;
            
        case 'update':
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }
            $id = $_GET['id'] ?? '';
            if (!$id) {
                throw new Exception('ID is required for update');
            }
            updateRadioStation($id);
            break;
            
        case 'delete':
            if ($method !== 'DELETE') {
                throw new Exception('Invalid request method');
            }
            $id = $_GET['id'] ?? '';
            if (!$id) {
                throw new Exception('ID is required for delete');
            }
            deleteRadioStation($id);
            break;
            
        case 'get':
            if ($method !== 'GET') {
                throw new Exception('Invalid request method');
            }
            $id = $_GET['id'] ?? '';
            if (!$id) {
                throw new Exception('ID is required');
            }
            getRadioStation($id);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function addRadioStation() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'] ?? '';
    $url = $input['url'] ?? '';
    $logo = $input['logo'] ?? '';
    $category = $input['category'] ?? 'general';
    $description = $input['description'] ?? '';
    $status = $input['status'] ?? 1;
    $display_order = $input['display_order'] ?? 0;
    
    if (empty($name) || empty($url)) {
        throw new Exception('Name and URL are required');
    }
    
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO radio_stations (name, stream_url, logo_url, category, description, is_active, display_order, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $success = $stmt->execute([$name, $url, $logo, $category, $description, $status, $display_order]);
    
    if ($success) {
        $id = $pdo->lastInsertId();
        echo json_encode([
            'success' => true, 
            'message' => 'Radio station added successfully',
            'id' => $id
        ]);
    } else {
        throw new Exception('Failed to add radio station');
    }
}

function updateRadioStation($id) {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'] ?? '';
    $url = $input['url'] ?? '';
    $logo = $input['logo'] ?? '';
    $category = $input['category'] ?? 'general';
    $description = $input['description'] ?? '';
    $status = $input['status'] ?? 1;
    $display_order = $input['display_order'] ?? 0;
    
    if (empty($name) || empty($url)) {
        throw new Exception('Name and URL are required');
    }
    
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }
    
    $stmt = $pdo->prepare("
        UPDATE radio_stations 
        SET name = ?, stream_url = ?, logo_url = ?, category = ?, description = ?, is_active = ?, display_order = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $success = $stmt->execute([$name, $url, $logo, $category, $description, $status, $display_order, $id]);
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Radio station updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update radio station');
    }
}

function deleteRadioStation($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM radio_stations WHERE id = ?");
    $success = $stmt->execute([$id]);
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Radio station deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete radio station');
    }
}

function getRadioStation($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM radio_stations WHERE id = ?");
    $stmt->execute([$id]);
    $station = $stmt->fetch();
    
    if ($station) {
        echo json_encode([
            'success' => true, 
            'data' => $station
        ]);
    } else {
        throw new Exception('Radio station not found');
    }
}
?>
