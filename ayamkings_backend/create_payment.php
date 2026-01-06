<?php
/**
 * Create ToyyibPay Payment Bill
 * 
 * This endpoint creates a bill for checkout and returns payment URL
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/toyyibpay_config.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    // Check if ToyyibPay is configured
    if (!isToyyibPayConfigured()) {
        throw new Exception('Payment gateway is not configured. Please contact support.');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $orderId = $input['order_id'] ?? null;
    $amount = $input['amount'] ?? null; // Amount in RM (e.g., 25.50)
    $customerName = $input['customer_name'] ?? '';
    $customerEmail = $input['customer_email'] ?? '';
    $customerPhone = $input['customer_phone'] ?? '';
    
    if (!$orderId || !$amount) {
        throw new Exception('Order ID and amount are required.');
    }
    
    // Convert amount to cents (ToyyibPay uses cents)
    $amountInCents = round($amount * 100);
    
    // Create bill
    $billData = [
        'name' => 'AyamKings Order #' . $orderId,
        'description' => 'Payment for Order #' . $orderId,
        'amount' => $amountInCents,
        'order_id' => 'AK' . $orderId,
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone
    ];
    
    $result = createToyyibPayBill($billData);
    
    if ($result['success']) {
        // Store bill code in database for reference
        $conn = getDbConnection();
        $billCode = $result['bill_code'];
        
        // Update order with bill code (add column if needed)
        $stmt = $conn->prepare("UPDATE orders SET payment_bill_code = ?, payment_status = 'pending' WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $billCode, $orderId);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
        
        $response['success'] = true;
        $response['message'] = 'Payment bill created successfully.';
        $response['bill_code'] = $result['bill_code'];
        $response['payment_url'] = $result['payment_url'];
    } else {
        throw new Exception($result['message'] ?? 'Failed to create payment bill.');
    }
    
} catch (Exception $e) {
    error_log("[ToyyibPay Create Payment] " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
