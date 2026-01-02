<?php
// update_order_status.php (NEW FILE: Handles updating order status)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
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

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $order_id = $data['order_id'] ?? null;
    $status = $data['status'] ?? null;

    if (empty($order_id) || empty($status)) {
        $response['message'] = 'Missing order ID or status.';
        echo json_encode($response);
        exit();
    }

    // Validate status against allowed values
    $allowed_statuses = ['Pending', 'Preparing', 'Ready for Pickup', 'Finished', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $response['message'] = 'Invalid status value provided.';
        echo json_encode($response);
        exit();
    }

    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Order status updated successfully.';
        } else {
            $response['message'] = 'No order found with the provided ID or no change in status.';
        }
    } else {
        $response['message'] = 'Error updating order status: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>