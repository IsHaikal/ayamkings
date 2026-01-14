<?php
/**
 * Create ToyyibPay Payment Bill
 * 
 * This endpoint creates a bill for checkout and returns payment URL
 */
require_once __DIR__ . '/cors.php';

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
    $originalAmount = (float)$amount;
    
    // --- Point Redemption Logic ---
    $redeemPoints = $input['redeem_points'] ?? false;
    $pointsRedeemed = 0.00;
    $finalAmount = $originalAmount;

    if ($redeemPoints && isset($input['user_id'])) {
        $userId = (int)$input['user_id'];
        $conn = getDbConnection();
        
        // Check user balance
        $stmt = $conn->prepare("SELECT points FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $userPoints = $user ? (float)$user['points'] : 0.00;
        $stmt->close();
        
        if ($userPoints > 0) {
            // Cap points usage at total amount (cannot result in negative bill)
            // ToyyibPay requires min RM1.00 usually? Let's allow full redemption if possible but keep RM 1 min if creating bill requires it.
            // Actually, if amount becomes 0, we bypass ToyyibPay! But for now, let's assume min bill RM 1.00 for gateway.
            // Logic: Max redeemable is total amount.
            
            if ($userPoints >= $originalAmount) {
                $pointsRedeemed = $originalAmount; // Full coverage (Free?) - Edge case: ToyyibPay might reject 0.00
                $finalAmount = 0.00; 
                // Wait, if final amount is 0, we can't create a bill.
                // If amount is 0, we should mark as paid immediately.
            } else {
                $pointsRedeemed = $userPoints;
                $finalAmount = $originalAmount - $pointsRedeemed;
            }

            // Deduct points from user immediately (reserved)
            if ($pointsRedeemed > 0) {
                // Update user points
                $updateStmt = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
                $updateStmt->bind_param("di", $pointsRedeemed, $userId);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Log usage? (Ideally we should have a points_log table, but skipping for MVP)
            }
        }
        $conn->close();
    }
    
    // If amount is 0 (Fully paid by points), handle separately? 
    // For MVP, enable full redemption. If 0, bypass bill creation.
    
    if ($finalAmount <= 0) {
         // BYPASS PAYMENT GATEWAY - FULL POINT REDEMPTION
         $conn = getDbConnection();
         $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', payment_bill_code = 'POINTS_ redemption' WHERE id = ?");
         $stmt->bind_param("i", $orderId);
         $stmt->execute();
         $conn->close();

         $response['success'] = true;
         $response['message'] = 'Paid fully with points.';
         $response['payment_url'] = ''; // Frontend should handle redirect to success
         $response['is_free'] = true; // Signal frontend
         echo json_encode($response);
         exit();
    }
    
    $amountInCents = round($finalAmount * 100);
    
    // Create bill
    $billData = [
        'name' => 'AyamKings Order #' . $orderId . ($pointsRedeemed > 0 ? " (Pts: RM$pointsRedeemed)" : ""),
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
        $response['debug'] = $result;
        $response['message'] = $result['message'] ?? 'Failed to create payment bill.';
    }
    
} catch (Exception $e) {
    error_log("[ToyyibPay Create Payment] " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
