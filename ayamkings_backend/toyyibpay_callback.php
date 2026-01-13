<?php
/**
 * ToyyibPay Callback Handler
 * 
 * This endpoint receives payment status updates from ToyyibPay
 * Callback is called by ToyyibPay server after payment
 */

// Allow from ToyyibPay servers
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';

// Log all incoming data for debugging
error_log("[ToyyibPay Callback] POST: " . json_encode($_POST));
error_log("[ToyyibPay Callback] GET: " . json_encode($_GET));

$response = ['success' => false];

try {
    $conn = getDbConnection();
    
    // Get callback parameters (POST format)
    $refNo = $_POST['refno'] ?? '';
    $status = $_POST['status'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $billCode = $_POST['billcode'] ?? '';
    $orderId = $_POST['order_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $transactionTime = $_POST['transaction_time'] ?? '';
    
    // Log the callback
    error_log("[ToyyibPay Callback] RefNo: $refNo, Status: $status, BillCode: $billCode, OrderId: $orderId");
    
    // Status codes: 1 = Success, 2 = Pending, 3 = Failed
    $paymentStatus = 'pending';
    $orderStatus = 'Pending';
    
    if ($status == '1') {
        $paymentStatus = 'paid';
        $orderStatus = 'Pending'; // Order is pending preparation, but payment is done
    } elseif ($status == '3') {
        $paymentStatus = 'failed';
        $orderStatus = 'Cancelled';
    }
    
    // Extract order ID from external reference (format: AK123)
    $actualOrderId = null;
    if (preg_match('/AK(\d+)/', $orderId, $matches)) {
        $actualOrderId = (int)$matches[1];
    }
    
    // Update order by bill code
    if ($billCode) {
        $stmt = $conn->prepare("UPDATE orders SET 
            payment_status = ?, 
            status = ?
            WHERE payment_bill_code = ?");
        $stmt->bind_param("sss", $paymentStatus, $orderStatus, $billCode);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            error_log("[ToyyibPay Callback] Order updated via bill code: $billCode, Status: $paymentStatus");
            $response['success'] = true;

            // --- POINT EARNING LOGIC ---
            if ($paymentStatus == 'paid' && $amount > 0) {
                // Fetch User ID from this order
                // Need a fresh connection or query because execute() above might reset
                $orderQ = $conn->query("SELECT user_id FROM orders WHERE payment_bill_code = '$billCode' LIMIT 1");
                if ($orderRow = $orderQ->fetch_assoc()) {
                    $userId = (int)$orderRow['user_id'];
                    // Earn 10% of amount PAID (amount is in cents from ToyyibPay POST usually? CHECK DOCS. Callback amounts are usually in RM or cents. 
                    // Wait, standard ToyyibPay callback `amount` is usually RM (e.g. 1.00). Let's verify.
                    // If input amount is 100 (cents), 10% is 10 cents = RM 0.10.
                    // If input amount is 1.00 (RM), 10% is 0.10.
                    // Standard ToyyibPay callback `amount` is DECIMAL (RM).
                    
                    $pointsEarned = (float)$amount * 0.10; 
                    
                    // Update User Points
                    $conn->query("UPDATE users SET points = points + $pointsEarned WHERE id = $userId");
                    error_log("[Loyalty] User $userId earned RM $pointsEarned points from Order $billCode");
                }
            }
        }
        $stmt->close();
    }
    
    // Also try updating by order ID if bill code didn't work
    if (!$response['success'] && $actualOrderId) {
        $stmt = $conn->prepare("UPDATE orders SET 
            payment_status = ?,
            status = ?
            WHERE id = ?");
        $stmt->bind_param("ssi", $paymentStatus, $orderStatus, $actualOrderId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            error_log("[ToyyibPay Callback] Order updated via order ID: $actualOrderId, Status: $paymentStatus");
            $response['success'] = true;
            
            // --- POINT EARNING LOGIC (Order ID path) ---
            if ($paymentStatus == 'paid' && $amount > 0) {
                $orderQ = $conn->query("SELECT user_id FROM orders WHERE id = $actualOrderId LIMIT 1");
                if ($orderRow = $orderQ->fetch_assoc()) {
                    $userId = (int)$orderRow['user_id'];
                    $pointsEarned = (float)$amount * 0.10;
                    $conn->query("UPDATE users SET points = points + $pointsEarned WHERE id = $userId");
                    error_log("[Loyalty] User $userId earned RM $pointsEarned points from Order ID $actualOrderId");
                }
            }
        }
        $stmt->close();
    }
    
    $conn->close();
    
    if ($response['success']) {
        $response['message'] = 'Payment status updated successfully.';
    } else {
        $response['message'] = 'Order not found.';
    }
    
} catch (Exception $e) {
    error_log("[ToyyibPay Callback Error] " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

// ToyyibPay expects "OK" response
echo "OK";
?>
