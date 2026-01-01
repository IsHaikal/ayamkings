<?php
// ==========================================
// AyamKings Database Configuration
// ==========================================
// COPY this file to db_config.php and update credentials

// Docker/EC2 environment detection
$is_docker = getenv('ENVIRONMENT') === 'production' || 
             getenv('MYSQL_HOST') !== false;

if ($is_docker) {
    // ==========================================
    // DOCKER/PRODUCTION DATABASE
    // ==========================================
    define('DB_HOST', 'mysql');  // Docker service name
    define('DB_USERNAME', 'ayamkings_user');
    define('DB_PASSWORD', 'ayamkings_password');
    define('DB_NAME', 'ayamkings_db');
} else {
    // ==========================================
    // LOCAL DEVELOPMENT (XAMPP)
    // ==========================================
    define('DB_HOST', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'ayamkings_db');
}

// Database Connection Function
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
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
