<?php
/**
 * Add Payment Columns Migration
 * Run this once to add payment columns to orders table
 */
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

$results = [];

// Check if columns exist first
$checkQuery = "SHOW COLUMNS FROM orders LIKE 'payment_bill_code'";
$result = $conn->query($checkQuery);

if ($result->num_rows === 0) {
    // Columns don't exist, add them
    $alterQuery = "ALTER TABLE orders 
        ADD COLUMN payment_bill_code VARCHAR(50) DEFAULT NULL,
        ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'";
    
    if ($conn->query($alterQuery)) {
        $results['payment_columns'] = 'Added successfully';
    } else {
        $results['payment_columns'] = 'Error: ' . $conn->error;
    }
} else {
    $results['payment_columns'] = 'Already exist';
}

$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'Migration completed',
    'results' => $results
], JSON_PRETTY_PRINT);
?>
