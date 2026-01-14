<?php
// update_order_status.php (NEW FILE: Handles updating order status)

require_once __DIR__ . '/cors.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $order_id = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;

        if (empty($order_id) || empty($status)) {
            throw new Exception('Missing order ID or status');
        }

        // Validate status
        $allowed_statuses = ['Pending', 'Preparing', 'Ready', 'Finished', 'Cancelled'];
        if (!in_array($status, $allowed_statuses)) {
             throw new Exception('Invalid status value provided: ' . $status);
        }

        // Database connection
        if (!file_exists(__DIR__ . '/db_config.php')) {
             throw new Exception('db_config.php missing');
        }
        require_once __DIR__ . '/db_config.php';
        
        $conn = getDbConnection();

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Order status updated successfully.';
            } else {
                // Check if order exists
                $check = $conn->query("SELECT id FROM orders WHERE id = $order_id");
                if ($check->num_rows === 0) {
                     $response['message'] = "Order #$order_id not found.";
                } else {
                     // Status was already same
                     $response['success'] = true;
                     $response['message'] = 'Status updated (no change needed).';
                }
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();

    } catch (Throwable $e) {
        http_response_code(500);
        $response['message'] = 'Server Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method: ' . $_SERVER['REQUEST_METHOD'];
}

echo json_encode($response);
?>