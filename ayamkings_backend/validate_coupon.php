<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['code'])) {
    echo json_encode(['success' => false, 'message' => 'Coupon code is required.']);
    exit();
}

$code = $input['code'];

// Database connection
require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

// Check coupon availability
$stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or inactive coupon code.']);
    $conn->close();
    exit();
}

$coupon = $result->fetch_assoc();
$now = new DateTime();

// Check Expiry
if ($coupon['valid_until'] && new DateTime($coupon['valid_until']) < $now) {
    echo json_encode(['success' => false, 'message' => 'This coupon has expired.']);
    $conn->close();
    exit();
}

// Check Max Uses
if ($coupon['max_uses'] !== null && $coupon['times_used'] >= $coupon['max_uses']) {
    echo json_encode(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
    $conn->close();
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'Coupon applied successfully!',
    'discount_type' => $coupon['discount_type'],
    'discount_value' => floatval($coupon['discount_value']),
    'code' => $coupon['code']
]);

$stmt->close();
$conn->close();
?>
