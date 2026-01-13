<?php
// get_order_history.php - Get order history for a specific user

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

    // Fetch orders for this user with review status
    $sql = "SELECT o.id, o.total_amount, o.status, o.order_date, o.items_json,
                            orv.id as review_id, orv.rating as review_rating
                            FROM orders o
                            LEFT JOIN order_reviews orv ON o.id = orv.order_id
                            WHERE o.user_id = ? 
                            ORDER BY o.order_date DESC 
                            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        $response['message'] = 'SQL Error: ' . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit();
    }
    
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
            'items' => $items ? $items : [],
            'has_review' => !empty($row['review_id']),
            'review_rating' => $row['review_rating'] ? intval($row['review_rating']) : null
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
