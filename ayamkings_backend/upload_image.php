<?php
// upload_image.php - Uploads images to Cloudinary

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error_log.txt');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An error occurred during upload.'];

// Include Cloudinary config
require_once __DIR__ . '/cloudinary_config.php';

// Check if file was uploaded without errors
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image_file']['tmp_name'];
    $fileName = $_FILES['image_file']['name'];
    $fileSize = $_FILES['image_file']['size'];
    $fileType = $_FILES['image_file']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedfileExtensions = array('jpg', 'jpeg', 'gif', 'png', 'webp');

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Generate unique public ID
        $publicId = 'menu_' . md5(time() . $fileName);
        
        // Upload to Cloudinary
        $uploadResult = uploadToCloudinary($fileTmpPath, $publicId);
        
        if ($uploadResult['success']) {
            $response['success'] = true;
            $response['message'] = 'Image uploaded successfully to Cloudinary!';
            $response['image_url'] = $uploadResult['url'];
            $response['public_id'] = $uploadResult['public_id'];
        } else {
            // Fallback: Try local upload if Cloudinary fails
            $uploadFileDir = '../ayamkings_frontend/uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $destPath = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $response['success'] = true;
                $response['message'] = 'Image uploaded locally (Cloudinary unavailable).';
                $response['image_url'] = 'uploads/' . $newFileName;
            } else {
                $response['message'] = 'Cloudinary error: ' . $uploadResult['error'] . '. Local fallback also failed.';
            }
        }
    } else {
        $response['message'] = 'Invalid file type. Only JPG, JPEG, GIF, PNG, and WEBP files are allowed.';
    }
} else {
    $response['message'] = 'No file uploaded or upload error: ' . ($_FILES['image_file']['error'] ?? 'Unknown error.');
}

echo json_encode($response);
?>