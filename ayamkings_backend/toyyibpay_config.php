<?php
/**
 * ToyyibPay Configuration
 * 
 * This file reads ToyyibPay credentials from environment variables.
 * Set these in Railway: Settings > Variables
 * 
 * Required Environment Variables:
 * - TOYYIBPAY_SECRET_KEY: Your User Secret Key from ToyyibPay Dashboard
 * - TOYYIBPAY_CATEGORY_CODE: Your Category Code for bills
 * - TOYYIBPAY_SANDBOX: Set to "true" for sandbox mode, "false" for production
 */

// ToyyibPay API Configuration
define('TOYYIBPAY_SECRET_KEY', getenv('TOYYIBPAY_SECRET_KEY') ?: '');
define('TOYYIBPAY_CATEGORY_CODE', getenv('TOYYIBPAY_CATEGORY_CODE') ?: '');
define('TOYYIBPAY_SANDBOX', filter_var(getenv('TOYYIBPAY_SANDBOX') ?: 'true', FILTER_VALIDATE_BOOLEAN));

// API Base URLs
define('TOYYIBPAY_API_URL', TOYYIBPAY_SANDBOX 
    ? 'https://dev.toyyibpay.com/index.php/api/' 
    : 'https://toyyibpay.com/index.php/api/'
);
define('TOYYIBPAY_PAYMENT_URL', TOYYIBPAY_SANDBOX 
    ? 'https://dev.toyyibpay.com/' 
    : 'https://toyyibpay.com/'
);

// Frontend URLs for callbacks
define('FRONTEND_URL', getenv('FRONTEND_URL') ?: 'https://ayamkings.vercel.app');
define('BACKEND_URL', getenv('BACKEND_URL') ?: 'https://ayamkings-production.up.railway.app');

/**
 * Create a ToyyibPay Bill
 * 
 * @param array $billData Bill information
 * @return array Response with bill code or error
 */
function createToyyibPayBill($billData) {
    $apiUrl = TOYYIBPAY_API_URL . 'createBill';
    
    $postData = array(
        'userSecretKey' => TOYYIBPAY_SECRET_KEY,
        'categoryCode' => TOYYIBPAY_CATEGORY_CODE,
        'billName' => $billData['name'] ?? 'AyamKings Order',
        'billDescription' => $billData['description'] ?? 'Order Payment',
        'billPriceSetting' => 1, // Fixed price
        'billPayorInfo' => 1, // Require payer info
        'billAmount' => $billData['amount'], // Amount in cents (e.g., 1000 = RM10.00)
        'billReturnUrl' => FRONTEND_URL . '/payment_success.html',
        'billCallbackUrl' => BACKEND_URL . '/toyyibpay_callback.php',
        'billExternalReferenceNo' => $billData['order_id'] ?? uniqid('AK'),
        'billTo' => $billData['customer_name'] ?? 'Customer',
        'billEmail' => $billData['customer_email'] ?? 'customer@ayamkings.com',
        'billPhone' => !empty($billData['customer_phone']) ? $billData['customer_phone'] : '0123456789',
        'billSplitPayment' => 0,
        'billSplitPaymentArgs' => '',
        'billPaymentChannel' => 2, // 0=FPX, 1=Card, 2=Both
        'billContentEmail' => 'Thank you for ordering from AyamKings!',
        'billChargeToCustomer' => 1, // Charge transaction fee to customer
        'billExpiryDays' => 1 // Bill expires in 1 day
    );
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_URL, $apiUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    if ($error) {
        error_log("[ToyyibPay] cURL Error: " . $error);
        return ['success' => false, 'message' => 'Connection error'];
    }
    
    $response = json_decode($result, true);
    
    if (isset($response[0]['BillCode'])) {
        return [
            'success' => true,
            'bill_code' => $response[0]['BillCode'],
            'payment_url' => TOYYIBPAY_PAYMENT_URL . $response[0]['BillCode']
        ];
    }
    
    error_log("[ToyyibPay] API Error: " . $result);
    return ['success' => false, 'message' => 'Failed to create bill', 'raw' => $result];
}

/**
 * Get Bill Transactions/Status
 * 
 * @param string $billCode The bill code to check
 * @return array Transaction details
 */
function getToyyibPayBillStatus($billCode) {
    $apiUrl = TOYYIBPAY_API_URL . 'getBillTransactions';
    
    $postData = array(
        'billCode' => $billCode,
        'billpaymentStatus' => '1' // 1=Success, 2=Pending, 3=Failed
    );
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_URL, $apiUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($curl);
    curl_close($curl);
    
    return json_decode($result, true);
}

/**
 * Validate ToyyibPay configuration
 * 
 * @return bool True if properly configured
 */
function isToyyibPayConfigured() {
    return !empty(TOYYIBPAY_SECRET_KEY) && !empty(TOYYIBPAY_CATEGORY_CODE);
}
?>
