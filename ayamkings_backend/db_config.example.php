<?php
// ==========================================
// AyamKings Database Configuration TEMPLATE
// ==========================================
// 📋 INSTRUCTIONS:
// 1. Copy this file and rename to: db_config.php
// 2. Update the credentials below with your actual values
// 3. db_config.php is in .gitignore so your credentials stay safe
// ==========================================

// Environment Detection
$is_production = (getenv('ENVIRONMENT') === 'production') || 
                  (isset($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost'));

if ($is_production) {
    // ==========================================
    // PRODUCTION DATABASE (InfinityFree/Railway)
    // ==========================================
    define('DB_HOST', getenv('DB_HOST') ?: 'YOUR_PRODUCTION_HOST');
    define('DB_USERNAME', getenv('DB_USERNAME') ?: 'YOUR_PRODUCTION_USERNAME');
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'YOUR_PRODUCTION_PASSWORD');
    define('DB_NAME', getenv('DB_NAME') ?: 'YOUR_PRODUCTION_DATABASE');
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
            'message' => 'Database connection failed'
        ]));
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
