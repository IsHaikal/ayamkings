<?php
// test_cloudinary.php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/cloudinary_config.php';

$response = [
    'config' => [
        'cloud_name' => CLOUDINARY_CLOUD_NAME,
        'api_key_length' => strlen(CLOUDINARY_API_KEY),
        'api_secret_length' => strlen(CLOUDINARY_API_SECRET)
    ],
    'test_upload' => null
];

// Create a small base64 image (1x1 pixel red dot)
$base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

try {
    $result = uploadBase64ToCloudinary($base64Image, 'test_connection_' . time());
    $response['test_upload'] = $result;
} catch (Exception $e) {
    $response['test_upload'] = ['success' => false, 'error' => $e->getMessage()];
}

echo json_encode($response);
?>
