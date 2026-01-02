<?php
// AyamKings Backend - Entry Point
// This file helps Nixpacks detect PHP and serves as router

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple router - redirect to health check by default
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/index.php') {
    echo json_encode([
        'status' => 'ok',
        'message' => 'AyamKings Backend API',
        'version' => '1.0.0',
        'endpoints' => [
            '/health.php' => 'Health check',
            '/login.php' => 'User login',
            '/register.php' => 'User registration',
            '/get_menu.php' => 'Get menu items'
        ]
    ]);
}
?>
