<?php
// google_login.php - Handle Google OAuth login/registration
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Disable error display
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
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['credential'])) {
        throw new Exception('Google credential is required.');
    }
    
    $credential = $input['credential'];
    
    // Decode Google JWT token (simple decode for MVP)
    $tokenParts = explode('.', $credential);
    if (count($tokenParts) !== 3) {
        throw new Exception('Invalid token format.');
    }
    
    $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);
    
    if (!$payload || !isset($payload['email'])) {
        throw new Exception('Invalid token payload.');
    }
    
    $email = $payload['email'];
    $name = $payload['name'] ?? 'Google User';
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists - login
        $user = $result->fetch_assoc();
        
        $response = [
            'success' => true,
            'message' => 'Google login successful!',
            'token' => bin2hex(random_bytes(32)),
            'user' => [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    } else {
        // New user - register as customer
        $defaultPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $role = 'customer';
        $phone = '';
        
        $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssss", $name, $email, $defaultPassword, $phone, $role);
        
        if ($insertStmt->execute()) {
            $newUserId = $conn->insert_id;
            
            $response = [
                'success' => true,
                'message' => 'Account created with Google!',
                'token' => bin2hex(random_bytes(32)),
                'user' => [
                    'id' => $newUserId,
                    'full_name' => $name,
                    'email' => $email,
                    'role' => $role
                ]
            ];
        } else {
            throw new Exception('Failed to create account: ' . $insertStmt->error);
        }
        $insertStmt->close();
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("[Google Login Error] " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
