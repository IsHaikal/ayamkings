<?php
/**
 * Fix Old Orders - Set payment_status to 'paid' for orders without ToyyibPay
 */
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

// Update old orders (no payment_bill_code) to be visible
$sql = "UPDATE orders SET payment_status = 'paid' WHERE payment_bill_code IS NULL OR payment_bill_code = ''";
$result = $conn->query($sql);

$affected = $conn->affected_rows;

$conn->close();

echo json_encode([
    'success' => true,
    'message' => "Fixed $affected old orders - set payment_status to 'paid'",
    'affected_rows' => $affected
], JSON_PRETTY_PRINT);
?>
