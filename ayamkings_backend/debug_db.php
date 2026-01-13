<?php
// debug_db.php - Diagnostic script

header('Content-Type: text/plain');

require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

echo "Connected to Host: " . DB_HOST . "\n";
echo "Database Name: " . DB_NAME . "\n";
echo "User: " . DB_USERNAME . "\n";

echo "\n--- Columns in 'menu' table ---\n";
$result = $conn->query("SHOW COLUMNS FROM menu");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error showing columns: " . $conn->error . "\n";
}

$conn->close();
?>
