<?php
// create_order.php (Place this in your XAMPP htdocs/ayamkings_backend/ directory)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON received: ' . json_last_error_msg();
        echo json_encode($response);
        exit();
    }

    // You would typically get user_id from session/token validation here
    // For this example, we'll assume a user ID is sent or mock it.
    // In a real system, the user_id would come from the authenticated session.
    // For now, we'll temporarily use a hardcoded user_id for testing.
    // !!! IMPORTANT: Replace with actual user ID from login session in production !!!
    $user_id = isset($input['user_id']) ? intval($input['user_id']) : 1; // Default to 1 for testing if not provided

    if (!isset($input['items']) || !isset($input['total_amount'])) {
        $response['message'] = 'Missing required order data (items or total_amount).';
        echo json_encode($response);
        exit();
    }

    $items_json = json_encode($input['items']);
    $total_amount = floatval($input['total_amount']);

    // Database connection
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    $stmt = $conn->prepare("INSERT INTO orders (user_id, items_json, total_amount, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("isd", $user_id, $items_json, $total_amount);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Order placed successfully! Order ID: ' . $conn->insert_id;
    } else {
        $response['message'] = 'Error placing order: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>