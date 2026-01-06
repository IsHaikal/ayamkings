<?php
// ==========================================
// Cloudinary Configuration
// ==========================================
// Credentials are loaded from environment variables for security

define('CLOUDINARY_CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME') ?: 'dacdibj8e');
define('CLOUDINARY_API_KEY', getenv('CLOUDINARY_API_KEY') ?: '');
define('CLOUDINARY_API_SECRET', getenv('CLOUDINARY_API_SECRET') ?: '');
define('CLOUDINARY_UPLOAD_PRESET', getenv('CLOUDINARY_UPLOAD_PRESET') ?: 'ayamkings');

/**
 * Upload image to Cloudinary
 * @param string $imagePath - Local path to image file
 * @param string $publicId - Optional custom public ID for the image
 * @return array - Result with success status and URL or error
 */
function uploadToCloudinary($imagePath, $publicId = null) {
    $cloudName = CLOUDINARY_CLOUD_NAME;
    $apiKey = CLOUDINARY_API_KEY;
    $apiSecret = CLOUDINARY_API_SECRET;
    
    $timestamp = time();
    
    // Build signature
    $params = [
        'timestamp' => $timestamp,
        'folder' => 'ayamkings_menu'
    ];
    
    if ($publicId) {
        $params['public_id'] = $publicId;
    }
    
    // Sort params alphabetically
    ksort($params);
    
    // Build signature string
    $signatureString = '';
    foreach ($params as $key => $value) {
        $signatureString .= $key . '=' . $value . '&';
    }
    $signatureString = rtrim($signatureString, '&') . $apiSecret;
    $signature = sha1($signatureString);
    
    // Prepare upload
    $uploadUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
    
    $postFields = [
        'file' => new CURLFile($imagePath),
        'api_key' => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder' => 'ayamkings_menu'
    ];
    
    if ($publicId) {
        $postFields['public_id'] = $publicId;
    }
    
    // Execute upload
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploadUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['secure_url'])) {
        return [
            'success' => true,
            'url' => $result['secure_url'],
            'public_id' => $result['public_id']
        ];
    } else {
        return [
            'success' => false,
            'error' => $result['error']['message'] ?? 'Unknown upload error'
        ];
    }
}

/**
 * Upload base64 image to Cloudinary
 * @param string $base64Data - Base64 encoded image data
 * @param string $publicId - Optional custom public ID
 * @return array - Result with success status and URL or error
 */
function uploadBase64ToCloudinary($base64Data, $publicId = null) {
    $cloudName = CLOUDINARY_CLOUD_NAME;
    $apiKey = CLOUDINARY_API_KEY;
    $apiSecret = CLOUDINARY_API_SECRET;
    
    $timestamp = time();
    
    // Build signature
    $params = [
        'timestamp' => $timestamp,
        'folder' => 'ayamkings_menu'
    ];
    
    if ($publicId) {
        $params['public_id'] = $publicId;
    }
    
    ksort($params);
    
    $signatureString = '';
    foreach ($params as $key => $value) {
        $signatureString .= $key . '=' . $value . '&';
    }
    $signatureString = rtrim($signatureString, '&') . $apiSecret;
    $signature = sha1($signatureString);
    
    // Prepare upload
    $uploadUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
    
    $postFields = [
        'file' => $base64Data,
        'api_key' => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder' => 'ayamkings_menu'
    ];
    
    if ($publicId) {
        $postFields['public_id'] = $publicId;
    }
    
    // Execute upload
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploadUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['secure_url'])) {
        return [
            'success' => true,
            'url' => $result['secure_url'],
            'public_id' => $result['public_id']
        ];
    } else {
        return [
            'success' => false,
            'error' => $result['error']['message'] ?? 'Unknown upload error'
        ];
    }
}
?>
