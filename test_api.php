<?php
header('Content-Type: application/json');

// Test all API endpoints and return results
$results = [];

// Test radio API
try {
    $response = file_get_contents('http://localhost/streaming/backend/api/radio.php');
    $data = json_decode($response, true);
    $results['radio'] = [
        'success' => is_array($data),
        'count' => is_array($data) ? count($data) : 0,
        'data' => is_array($data) ? array_slice($data, 0, 3) : $data,
        'raw_response' => substr($response, 0, 200)
    ];
} catch (Exception $e) {
    $results['radio'] = ['error' => $e->getMessage()];
}

// Test TV API
try {
    $response = file_get_contents('http://localhost/streaming/backend/api/tv.php');
    $data = json_decode($response, true);
    $results['tv'] = [
        'success' => is_array($data),
        'count' => is_array($data) ? count($data) : 0
    ];
} catch (Exception $e) {
    $results['tv'] = ['error' => $e->getMessage()];
}

// Test podcasts API
try {
    $response = file_get_contents('http://localhost/streaming/backend/api/podcasts.php');
    $data = json_decode($response, true);
    $results['podcasts'] = [
        'success' => is_array($data),
        'count' => is_array($data) ? count($data) : 0
    ];
} catch (Exception $e) {
    $results['podcasts'] = ['error' => $e->getMessage()];
}

// Test news API
try {
    $response = file_get_contents('http://localhost/streaming/backend/api/news.php');
    $data = json_decode($response, true);
    $results['news'] = [
        'success' => is_array($data),
        'count' => is_array($data) ? count($data) : 0
    ];
} catch (Exception $e) {
    $results['news'] = ['error' => $e->getMessage()];
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>
