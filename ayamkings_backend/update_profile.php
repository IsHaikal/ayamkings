<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_config.php';
$conn = getDbConnection();

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit();
}

$user_id = $input['user_id'];
$full_name = isset($input['full_name']) ? $input['full_name'] : null;
$phone = isset($input['phone']) ? $input['phone'] : null;
$current_password = isset($input['current_password']) ? $input['current_password'] : null;
$new_password = isset($input['new_password']) && !empty($input['new_password']) ? $input['new_password'] : null;

// If changing password, require current password
if ($new_password) {
    if (!$current_password) {
        echo json_encode(['success' => false, 'message' => 'Current password is required to change password.']);
        exit();
    }
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        $conn->close();
        exit();
    }
    
    $user = $result->fetch_assoc();
    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // Update password
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_new_password, $user_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
    }
    $update_stmt->close();
    
} else if ($full_name !== null) {
    // Update name only (no password required)
    $update_stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
    $update_stmt->bind_param("si", $full_name, $user_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Name updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update name.']);
    }
    $update_stmt->close();
    
} else if ($phone !== null) {
    // Update phone only (no password required)
    $update_stmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
    $update_stmt->bind_param("si", $phone, $user_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Phone updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update phone.']);
    }
    $update_stmt->close();
    
} else {
    echo json_encode(['success' => false, 'message' => 'Nothing to update.']);
}

$conn->close();
?>
