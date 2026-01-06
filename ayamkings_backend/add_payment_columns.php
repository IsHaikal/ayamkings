<?php
/**
 * Add Payment Columns to Orders Table
 * 
 * Run this once to add necessary columns for ToyyibPay integration
 */
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';

$response = ['success' => false, 'message' => 'Failed', 'details' => []];

try {
    $conn = getDbConnection();
    
    $columns = [
        'payment_bill_code' => "ALTER TABLE orders ADD COLUMN payment_bill_code VARCHAR(50) NULL",
        'payment_status' => "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'unpaid'",
        'payment_ref_no' => "ALTER TABLE orders ADD COLUMN payment_ref_no VARCHAR(100) NULL",
        'payment_reason' => "ALTER TABLE orders ADD COLUMN payment_reason VARCHAR(255) NULL",
        'payment_method' => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) DEFAULT 'cash'"
    ];
    
    foreach ($columns as $colName => $sql) {
        // Check if column exists
        $checkSql = "SHOW COLUMNS FROM orders LIKE '$colName'";
        $result = $conn->query($checkSql);
        
        if ($result->num_rows == 0) {
            // Column doesn't exist, add it
            if ($conn->query($sql)) {
                $response['details'][] = "Added column: $colName";
            } else {
                $response['details'][] = "Failed to add $colName: " . $conn->error;
            }
        } else {
            $response['details'][] = "Column already exists: $colName";
        }
    }
    
    // Add index on payment_bill_code for faster lookups
    $indexCheck = $conn->query("SHOW INDEX FROM orders WHERE Key_name = 'idx_payment_bill_code'");
    if ($indexCheck->num_rows == 0) {
        $conn->query("ALTER TABLE orders ADD INDEX idx_payment_bill_code (payment_bill_code)");
        $response['details'][] = "Added index on payment_bill_code";
    }
    
    $response['success'] = true;
    $response['message'] = 'Payment columns setup complete!';
    
    $conn->close();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
