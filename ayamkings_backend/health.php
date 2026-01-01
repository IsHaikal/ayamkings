<?php
// ==========================================
// AyamKings Backend Health Check
// ==========================================
// URL: https://ayamkings.kesug.com/ayamkings_backend/health.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => 'InfinityFree',
    'php_version' => phpversion(),
    'database' => 'unknown'
];

// Test database connection
try {
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();
    
    if ($conn) {
        $health['database'] = 'connected';
        
        // Test query
        $result = $conn->query("SELECT COUNT(*) as count FROM menu");
        if ($result) {
            $row = $result->fetch_assoc();
            $health['menu_items'] = (int)$row['count'];
        }
        $conn->close();
    }
} catch (Exception $e) {
    $health['database'] = 'error';
    $health['database_error'] = $e->getMessage();
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>
