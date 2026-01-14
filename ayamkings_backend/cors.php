<?php
// cors.php - Centralized CORS handling
// Define allowed origins
$allowed_origins = [
    'https://ayamkings.vercel.app',
    'http://localhost',
    'http://127.0.0.1'
];

// Check if the request origin is in the allowed list
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    // Optional: Allow requests from no origin (like Postman or server-side scripts)
    // or just default to the production domain for safety
    // header("Access-Control-Allow-Origin: https://ayamkings.vercel.app");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
