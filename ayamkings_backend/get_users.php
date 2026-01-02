<?php
// get_users.php (Place this in your XAMPP htdocs/ayamkings_backend/ directory)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'users' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Fetch all users. For security, in a production app, you might want to
    // Fetch all users, sorted by role: admin -> staff -> customer
    $sql = "SELECT id, full_name, email, phone, role FROM users ORDER BY FIELD(role, 'admin', 'staff', 'customer'), full_name ASC";
    $result = $conn->query($sql);

    if ($result) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $response['success'] = true;
        $response['users'] = $users;
        $response['message'] = 'Users fetched successfully.';
    } else {
        $response['message'] = 'Error fetching users: ' . $conn->error;
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>