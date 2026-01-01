<?php
// upload_image.php (NEW FILE: Handles image file uploads)

// Ensure we always return JSON
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

// Check if file was uploaded without errors
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image_file']['tmp_name'];
    $fileName = $_FILES['image_file']['name'];
    $fileSize = $_FILES['image_file']['size'];
    $fileType = $_FILES['image_file']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedfileExtensions = array('jpg', 'jpeg', 'gif', 'png');

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Directory to save the uploaded image
        // IMPORTANT: Adjust this path if your 'uploads' folder is elsewhere relative to the backend.
        // This path is relative to the PHP script's location.
        $uploadFileDir = '../ayamkings_frontend/uploads/';
        // Ensure the directory exists
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true); // Create directory if it doesn't exist, recursively
        }

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Generate unique file name
        $destPath = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Return the public URL of the image
            // IMPORTANT: This URL needs to be accessible by the browser.
            // It's relative to the web server's root, which typically means /Coding%20PSM/ayamkings_frontend/uploads/
            $publicImageUrl = 'http://localhost/Coding%20PSM/ayamkings_frontend/uploads/' . $newFileName;
            $response['success'] = true;
            $response['message'] = 'Image uploaded successfully!';
            $response['image_url'] = $publicImageUrl;
        } else {
            $response['message'] = 'There was an error moving the uploaded file. Check directory permissions.';
        }
    } else {
        $response['message'] = 'Invalid file type. Only JPG, JPEG, GIF, and PNG files are allowed.';
    }
} else {
    $response['message'] = 'No file uploaded or upload error: ' . ($_FILES['image_file']['error'] ?? 'Unknown error.');
}

echo json_encode($response);
?>