<?php
// check_can_review.php - Check if user can review an item (purchased it)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$user_id = $_GET['user_id'] ?? null;
$menu_item_id = $_GET['menu_item_id'] ?? null;

if (empty($user_id) || empty($menu_item_id)) {
    echo json_encode(['success' => false, 'can_review' => false, 'message' => 'Missing user_id or menu_item_id']);
    exit();
}

require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

// Check if user has purchased this item (completed order)
$purchase_stmt = $conn->prepare("
    SELECT oi.id 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = ? 
    AND oi.menu_item_id = ? 
    AND o.status IN ('completed', 'finished', 'ready', 'Ready for Pickup')
    LIMIT 1
");
$purchase_stmt->bind_param("ii", $user_id, $menu_item_id);
$purchase_stmt->execute();
$purchase_result = $purchase_stmt->get_result();
$has_purchased = $purchase_result->num_rows > 0;
$purchase_stmt->close();

// Check if user already reviewed this item
$review_stmt = $conn->prepare("SELECT id FROM reviews WHERE menu_item_id = ? AND user_id = ?");
$review_stmt->bind_param("ii", $menu_item_id, $user_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
$already_reviewed = $review_result->num_rows > 0;
$review_stmt->close();

$conn->close();

$can_review = $has_purchased && !$already_reviewed;

$response = [
    'success' => true,
    'can_review' => $can_review,
    'has_purchased' => $has_purchased,
    'already_reviewed' => $already_reviewed
];

if (!$has_purchased) {
    $response['message'] = 'You can only review items you have purchased.';
} else if ($already_reviewed) {
    $response['message'] = 'You have already reviewed this item.';
}

echo json_encode($response);
?>
