<?php
// migrate_add_sold_out.php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

$conn = getDbConnection();
$response = [];

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM menu LIKE 'is_sold_out'");

if ($check->num_rows == 0) {
    // Column doesn't exist, add it
    $sql = "ALTER TABLE menu ADD COLUMN is_sold_out BOOLEAN DEFAULT 0";
    if ($conn->query($sql)) {
        $response['success'] = true;
        $response['message'] = "Column 'is_sold_out' added successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error adding column: " . $conn->error;
    }
} else {
    $response['success'] = true;
    $response['message'] = "Column 'is_sold_out' already exists.";
}

$conn->close();
echo json_encode($response);
?>
