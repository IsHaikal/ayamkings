<?php
// test_db.php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    if (!file_exists(__DIR__ . '/db_config.php')) {
        throw new Exception("db_config.php not found");
    }

    require_once __DIR__ . '/db_config.php';

    if (!function_exists('getDbConnection')) {
        throw new Exception("getDbConnection function not defined");
    }

    $conn = getDbConnection();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Database connection successful',
        'host' => defined('DB_HOST') ? DB_HOST : 'undefined'
    ]);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Connection failed: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
