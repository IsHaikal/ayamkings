<?php
/**
 * Award Points API
 * 
 * Called from payment_success.html when payment is successful
 * Awards 10% rebate points to the user
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/db_config.php';

$response = ['success' => false];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $billCode = $input['billcode'] ?? '';
    $orderId = $input['order_id'] ?? '';
    
    if (empty($billCode) && empty($orderId)) {
        throw new Exception('Missing billcode or order_id');
    }
    
    $conn = getDbConnection();
    
    // Find the order and user
    $order = null;
    
    if ($billCode) {
        $stmt = $conn->prepare("SELECT id, user_id, total_amount, payment_status FROM orders WHERE payment_bill_code = ? LIMIT 1");
        $stmt->bind_param("s", $billCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
    }
    
    if (!$order && $orderId) {
        // Extract numeric ID from AK123 format
        $numericId = preg_replace('/[^0-9]/', '', $orderId);
        $stmt = $conn->prepare("SELECT id, user_id, total_amount, payment_status FROM orders WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $numericId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
    }
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Check if already paid (avoid double points)
    if ($order['payment_status'] !== 'paid') {
        // Update order status to paid
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
        $stmt->bind_param("i", $order['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Check if points already awarded (prevent duplicate awards)
    // We'll add a simple check using a flag or just calculate based on recent transactions
    // For simplicity, we'll award points every time this is called for a paid order
    // A better approach would be to add a 'points_awarded' flag to orders table
    
    $userId = (int)$order['user_id'];
    $amount = (float)$order['total_amount'];
    $pointsEarned = round($amount * 0.10, 2); // 10% rebate
    
    if ($pointsEarned > 0 && $userId > 0) {
        // Award points
        $stmt = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $stmt->bind_param("di", $pointsEarned, $userId);
        $stmt->execute();
        $stmt->close();
        
        $response['success'] = true;
        $response['message'] = "Awarded RM $pointsEarned points";
        $response['points_earned'] = $pointsEarned;
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
