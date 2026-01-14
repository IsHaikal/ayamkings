<?php
/**
 * Award Rebate Points API
 * 
 * Called from payment_success.html when payment is successful (status_id=1)
 * Awards 10% rebate points to the user's wallet (users.points)
 * 
 * Simple flow: No ToyyibPay callback needed
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/db_config.php';

$response = ['success' => false];

try {
    // Accept both POST and GET for flexibility
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? '';
    } else {
        $orderId = $_GET['order_id'] ?? '';
    }
    
    if (empty($orderId)) {
        throw new Exception('Missing order_id');
    }
    
    // Extract numeric ID from "AK123" format
    $numericId = preg_replace('/[^0-9]/', '', $orderId);
    
    if (empty($numericId)) {
        throw new Exception('Invalid order_id format');
    }
    
    $conn = getDbConnection();
    
    // Find the order
    $stmt = $conn->prepare("SELECT id, user_id, total_amount, payment_status, points_awarded FROM orders WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $numericId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        throw new Exception('Order not found: ' . $numericId);
    }
    
    // Check if points already awarded (prevent duplicates)
    $alreadyAwarded = isset($order['points_awarded']) && intval($order['points_awarded']) === 1;
    if ($alreadyAwarded) {
        $response['success'] = true;
        $response['message'] = 'Points already awarded for this order';
        $response['points_earned'] = 0;
        $response['already_awarded'] = true;
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    // Update order to paid status if not already
    if ($order['payment_status'] !== 'paid') {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
        $stmt->bind_param("i", $order['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Flat rebate: RM 0.10 per order (not percentage of amount)
    $userId = (int)$order['user_id'];
    $pointsEarned = 0.10; // Fixed RM 0.10 per order
    
    if ($pointsEarned > 0 && $userId > 0) {
        // Award points to user's wallet
        $stmt = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $stmt->bind_param("di", $pointsEarned, $userId);
        $stmt->execute();
        $stmt->close();
        
        // Mark order as points awarded
        $stmt = $conn->prepare("UPDATE orders SET points_awarded = 1 WHERE id = ?");
        $stmt->bind_param("i", $order['id']);
        $stmt->execute();
        $stmt->close();
        
        $response['success'] = true;
        $response['message'] = "Awarded RM $pointsEarned rebate points";
        $response['points_earned'] = $pointsEarned;
        $response['user_id'] = $userId;
    } else {
        $response['success'] = true;
        $response['message'] = 'No points to award';
        $response['points_earned'] = 0;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
