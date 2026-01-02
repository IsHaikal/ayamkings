<?php
// ==========================================
// AyamKings Database Configuration
// ==========================================
// COPY this file to db_config.php

// Railway/Docker environment detection
$is_production = getenv('MYSQLHOST') !== false || 
                 getenv('ENVIRONMENT') === 'production';

if ($is_production) {
    // ==========================================
    // RAILWAY/PRODUCTION DATABASE
    // ==========================================
    // Railway auto-injects these environment variables
    define('DB_HOST', getenv('MYSQLHOST') ?: 'mysql');
    define('DB_USERNAME', getenv('MYSQLUSER') ?: 'root');
    define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: '');
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
} else {
    // ==========================================
    // LOCAL DEVELOPMENT (XAMPP)
    // ==========================================
    define('DB_HOST', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'ayamkings_db');
    define('DB_PORT', '3306');
}

// Database Connection Function
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'success' => false, 
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
