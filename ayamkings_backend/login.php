<?php
// login.php (Place this in your XAMPP htdocs/ayamkings_backend/ directory)


require_once __DIR__ . '/cors.php'; // Handle CORS and Preflight

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'], $input['password'])) {
        $response['message'] = 'Missing email or password.';
        echo json_encode($response);
        exit();
    }

    $email = $input['email'];
    $password = $input['password'];

    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Retrieve user from database
    $stmt = $conn->prepare("SELECT id, full_name, email, password, phone, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            // Simulate a token (in a real app, this would be a JWT or session ID)
            $response['token'] = bin2hex(random_bytes(16));
            $response['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role']
            ];
        } else {
            $response['message'] = 'Invalid credentials.';
        }
    } else {
        $response['message'] = 'Invalid credentials.';
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>