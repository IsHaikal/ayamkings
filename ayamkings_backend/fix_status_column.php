<?php
// fix_status_column.php - One-time script to fix status column size
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';

$response = ['success' => false, 'message' => 'Failed'];

try {
    $conn = getDbConnection();
    
    // Alter the status column to be larger
    $result = $conn->query("ALTER TABLE orders MODIFY COLUMN status VARCHAR(20)");
    
    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Status column updated to VARCHAR(20) successfully!';
    } else {
        throw new Exception($conn->error);
    }
    
    $conn->close();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
