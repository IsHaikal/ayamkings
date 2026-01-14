<?php
// add_availability_column.php
// Adds 'is_available' column to 'menu' table if it doesn't exist.

require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

$sql = "SHOW COLUMNS FROM menu LIKE 'is_available'";
$result = $conn->query($sql);

if ($result && $result->num_rows == 0) {
    // Column doesn't exist, add it
    $alterSql = "ALTER TABLE menu ADD COLUMN is_available TINYINT(1) DEFAULT 1";
    if ($conn->query($alterSql) === TRUE) {
        echo "Column 'is_available' added successfully to 'menu' table.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'is_available' already exists.";
}

$conn->close();
?>
