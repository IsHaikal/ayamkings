<?php
// get_order_review.php - Check if an order has been reviewed

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$order_id = $_GET['order_id'] ?? null;

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit();
}

require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

// Get the review for this order
$sql = "
    SELECT orv.id, orv.rating, orv.comment, orv.review_date, u.full_name 
    FROM order_reviews orv
    JOIN users u ON orv.user_id = u.id
    WHERE orv.order_id = ?
";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
    $conn->close();
    exit();
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $review = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'has_review' => true,
        'review' => $review
    ]);
} else {
    echo json_encode([
        'success' => true,
        'has_review' => false,
        'review' => null
    ]);
}

$stmt->close();
$conn->close();
?>
