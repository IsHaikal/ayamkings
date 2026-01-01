<?php
// user_crud.php (Place this in your XAMPP htdocs/ayamkings_backend/ directory)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "ayamkings_db";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Get ID from query string for PUT/DELETE

switch ($method) {
    case 'PUT': // Update User
        $data = json_decode(file_get_contents('php://input'), true);

        $full_name = $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $role = $data['role'] ?? '';

        if (empty($id) || empty($full_name) || empty($email) || empty($phone) || empty($role)) {
            $response['message'] = 'Missing data for user update.';
            echo json_encode($response);
            exit();
        }

        // Basic validation for role
        $allowed_roles = ['customer', 'staff', 'admin'];
        if (!in_array($role, $allowed_roles)) {
            $response['message'] = 'Invalid user role provided.';
            echo json_encode($response);
            exit();
        }

        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $role, $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'User updated successfully.';
            } else {
                $response['message'] = 'No user found with the provided ID or no changes were made.';
            }
        } else {
            $response['message'] = 'Error updating user: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'DELETE': // Delete User
        if (empty($id)) {
            $response['message'] = 'User ID is required for deletion.';
            echo json_encode($response);
            exit();
        }

        // IMPORTANT: Add logic to prevent deleting the *currently logged-in admin* account
        // This requires session management or token validation, which is out of scope for
        // a simple file like this but crucial in a real application.
        // For now, the frontend JS prevents this, but a backend check is more robust.

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'User deleted successfully.';
            } else {
                $response['message'] = 'No user found with the provided ID.';
            }
        } else {
            $response['message'] = 'Error deleting user: ' . $stmt->error;
        }
        $stmt->close();
        break;

    default:
        $response['message'] = 'Unsupported request method.';
        break;
}

$conn->close();
echo json_encode($response);
?>