<?php
// place_order.php (Standard functionality)

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

    $user_id = $data['user_id'] ?? null;
    $total_price = $data['total_price'] ?? null; // This is the original subtotal
    $items = $data['items'] ?? [];
    $coupon_code = $data['coupon_code'] ?? null;
    $discount_amount = $data['discount_amount'] ?? 0;

    if (empty($user_id) || !is_numeric($total_price) || empty($items)) {
        $response['message'] = 'Missing or invalid order data.';
        echo json_encode($response);
        exit();
    }

    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Verify Coupon if provided
    if ($coupon_code) {
        $stmt_coupon = $conn->prepare("SELECT id, times_used FROM coupons WHERE code = ? AND is_active = 1");
        $stmt_coupon->bind_param("s", $coupon_code);
        $stmt_coupon->execute();
        $res_coupon = $stmt_coupon->get_result();

        if ($res_coupon->num_rows > 0) {
            $coupon = $res_coupon->fetch_assoc();
            // Increment usage
            $stmt_update = $conn->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE id = ?");
            $stmt_update->bind_param("i", $coupon['id']);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Invalid coupon, currently we just ignore and proceed with 0 discount or error?
            // Use case: Client validated, but maybe it expired just now.
            // Let's reset discount to 0 for safety if invalid
            $discount_amount = 0;
            $coupon_code = null;
        }
        $stmt_coupon->close();
    }

    $final_amount = $total_price - $discount_amount;
    if ($final_amount < 0) $final_amount = 0;

    $initial_status = "Pending";
    $items_json = json_encode($items);

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, items_json) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $final_amount, $initial_status, $items_json);

    if ($stmt->execute()) {
        $response['success'] = true;
        // Include usage info
        $response['message'] = 'Order placed successfully!' . ($coupon_code ? ' (Coupon Applied)' : '');
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