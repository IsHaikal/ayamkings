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
    
    // Update order by bill code or order ID
    if ($billCode) {
        $stmt = $conn->prepare("UPDATE orders SET 
            payment_status = ?, 
            payment_ref_no = ?,
            payment_reason = ?,
            status = ?
            WHERE payment_bill_code = ?");
        $stmt->bind_param("sssss", $paymentStatus, $refNo, $reason, $orderStatus, $billCode);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            error_log("[ToyyibPay Callback] Order updated via bill code: $billCode");
            $response['success'] = true;
        }
        $stmt->close();
    }
    
    // Also try updating by order ID if bill code didn't work
    if (!$response['success'] && $actualOrderId) {
        $stmt = $conn->prepare("UPDATE orders SET 
            payment_status = ?, 
            payment_ref_no = ?,
            payment_reason = ?
            WHERE id = ?");
        $stmt->bind_param("sssi", $paymentStatus, $refNo, $reason, $actualOrderId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            error_log("[ToyyibPay Callback] Order updated via order ID: $actualOrderId");
            $response['success'] = true;
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
