<?php
/**
 * Test ToyyibPay Configuration
 */
header('Content-Type: application/json');

require_once __DIR__ . '/toyyibpay_config.php';

echo json_encode([
    'toyyibpay_configured' => isToyyibPayConfigured(),
    'secret_key_set' => !empty(TOYYIBPAY_SECRET_KEY),
    'category_code_set' => !empty(TOYYIBPAY_CATEGORY_CODE),
    'sandbox_mode' => TOYYIBPAY_SANDBOX,
    'api_url' => TOYYIBPAY_API_URL,
    'payment_url' => TOYYIBPAY_PAYMENT_URL,
    'secret_key_preview' => substr(TOYYIBPAY_SECRET_KEY, 0, 8) . '...'
], JSON_PRETTY_PRINT);
?>
