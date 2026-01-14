<?php
// DEBUG_MARKER_FINAL - If you see this, this is the correct get_orders.php file.

require_once __DIR__ . '/cors.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'orders' => []];

// Database connection (using centralized config)
require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // This SQL query specifically uses 'o.total_amount' and 'o.items_json'
    // Only show orders where payment is confirmed (paid)
    $sql = "SELECT
                o.id AS order_id,
                o.user_id,
                u.full_name AS customer_name,
                u.email AS customer_email,
                u.phone AS customer_phone,
                o.total_amount AS total_amount,
                o.status,
                o.order_date,
                o.items_json,
                o.payment_status
            FROM
                orders o
            JOIN
                users u ON o.user_id = u.id
            WHERE
                o.payment_status = 'paid' OR o.payment_status IS NULL
            ORDER BY
                o.order_date DESC"; // Order by most recent

    $result = $conn->query($sql);

    if ($result) {
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // Decode the JSON string from items_json into a PHP array/object
            $decoded_items = json_decode($row['items_json'], true);

            // Robust check for JSON decoding errors
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_items)) {
                $decoded_items = []; // Default to empty array if JSON is invalid or null
                error_log("Invalid JSON in items_json for order ID: " . $row['order_id'] . ". Error: " . json_last_error_msg());
            }

            $orders[] = [
                'id' => $row['order_id'],
                'user_id' => $row['user_id'],
                'customer_name' => $row['customer_name'],
                'customer_email' => $row['customer_email'],
                'customer_phone' => $row['customer_phone'],
                'total_amount' => $row['total_amount'], // Standardized key
                'status' => $row['status'],
                'order_date' => $row['order_date'],
                'items' => $decoded_items // This array will be passed to the frontend
            ];
        }

        $response['success'] = true;
        $response['orders'] = $orders;
        $response['message'] = 'Orders fetched successfully.';
    } else {
        $response['message'] = 'Error fetching orders: ' . $conn->error;
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
