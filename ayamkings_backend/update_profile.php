<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id'], $input['current_password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

$user_id = $input['user_id'];
$full_name = $input['full_name'];
$phone = $input['phone'];
$current_password = $input['current_password'];
$new_password = isset($input['new_password']) && !empty($input['new_password']) ? $input['new_password'] : null;

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ayamkings_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// 1. Verify Current Password
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

// 2. Update Profile
if ($new_password) {
    // Update Name, Phone AND Password
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, password = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $full_name, $phone, $hashed_new_password, $user_id);
} else {
    // Update Name and Phone ONLY
    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $full_name, $phone, $user_id);
}

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $update_stmt->error]);
}

$update_stmt->close();
$conn->close();
?>
