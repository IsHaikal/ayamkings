<?php
// add_order_review.php - Submit review for an order

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
    $data = json_decode(file_get_contents('php://input'), true);

    $order_id = $data['order_id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? null;

    // Validate required fields
    if (empty($order_id) || empty($user_id) || empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        $response['message'] = 'Missing or invalid review data (order ID, user ID, or rating 1-5).';
        echo json_encode($response);
        exit();
    }

    // Database connection
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Check if the order belongs to this user and is completed
    $order_stmt = $conn->prepare("
        SELECT id, status 
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $order_stmt->bind_param("ii", $order_id, $user_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows === 0) {
        $response['message'] = 'Order not found or does not belong to you.';
        echo json_encode($response);
        $order_stmt->close();
        $conn->close();
        exit();
    }

    $order = $order_result->fetch_assoc();
    $order_stmt->close();

    // Check if order is completed
    $completed_statuses = ['completed', 'finished', 'ready', 'Ready for Pickup', 'Finished'];
    if (!in_array($order['status'], $completed_statuses)) {
        $response['message'] = 'You can only review completed orders.';
        echo json_encode($response);
        $conn->close();
        exit();
    }

    // Check if already reviewed
    $check_stmt = $conn->prepare("SELECT id FROM order_reviews WHERE order_id = ?");
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response['message'] = 'You have already reviewed this order.';
        echo json_encode($response);
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Insert the review
    $stmt = $conn->prepare("INSERT INTO order_reviews (order_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $order_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Thank you for your feedback!';
        $response['review_id'] = $conn->insert_id;
    } else {
        $response['message'] = 'Error submitting review: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
