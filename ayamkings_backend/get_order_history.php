<?php
// get_order_history.php - Get order history for a specific user

require_once __DIR__ . '/cors.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'orders' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

    if (empty($user_id)) {
        $response['message'] = 'User ID is required.';
        echo json_encode($response);
        exit();
    }

    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Fetch orders for this user
    $stmt = $conn->prepare("SELECT id, total_amount, status, order_date, items_json 
                            FROM orders 
                            WHERE user_id = ? 
                            ORDER BY order_date DESC 
                            LIMIT 50");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Parse the items JSON
        $items = json_decode($row['items_json'], true);
        
        $orders[] = [
            'id' => $row['id'],
            'total_amount' => floatval($row['total_amount']),
            'status' => $row['status'],
            'order_date' => $row['order_date'],
            'items' => $items ? $items : []
        ];
    }

    $response['success'] = true;
    $response['orders'] = $orders;
    $response['message'] = 'Order history fetched successfully.';
    $response['debug'] = [
        'user_id_received' => $user_id,
        'orders_found' => count($orders)
    ];

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
