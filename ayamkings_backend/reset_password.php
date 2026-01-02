<?php
// reset_password.php - Handle password reset with token
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error_log.txt');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_config.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    $conn = getDbConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? '';
    $newPassword = $input['password'] ?? '';
    
    if (empty($token)) {
        throw new Exception('Reset token is required.');
    }
    
    if (empty($newPassword) || strlen($newPassword) < 6) {
        throw new Exception('Password must be at least 6 characters.');
    }
    
    // Find valid token
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid or expired reset link.');
    }
    
    $resetData = $result->fetch_assoc();
    $stmt->close();
    
    // Check if token expired
    if (strtotime($resetData['expires_at']) < time()) {
        // Delete expired token
        $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $deleteStmt->bind_param("s", $token);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        throw new Exception('Reset link has expired. Please request a new one.');
    }
    
    $email = $resetData['email'];
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $email);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update password.');
    }
    $updateStmt->close();
    
    // Delete used token
    $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $deleteStmt->bind_param("s", $token);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    $response['success'] = true;
    $response['message'] = 'Password has been reset successfully!';
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("[Reset Password Error] " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
