<?php
// migrate_add_points.php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

$conn = getDbConnection();
$response = [];

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'points'");

if ($check->num_rows == 0) {
    // Column doesn't exist, add it
    // Using DECIMAL(10,2) to store standard currency format (e.g. 10.50)
    $sql = "ALTER TABLE users ADD COLUMN points DECIMAL(10,2) DEFAULT 0.00";
    if ($conn->query($sql)) {
        $response['success'] = true;
        $response['message'] = "Column 'points' added successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error adding column: " . $conn->error;
    }
} else {
    $response['success'] = true;
    $response['message'] = "Column 'points' already exists.";
}

$conn->close();
echo json_encode($response);
?>
