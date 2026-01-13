<?php
// migrate_db.php - Auto-migrate database schema

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';

try {
    $conn = getDbConnection();
    
    $response = [
        'success' => false,
        'message' => '',
        'database' => DB_NAME,
        'host' => DB_HOST
    ];

    // Check if column exists
    $result = $conn->query("SHOW COLUMNS FROM menu LIKE 'is_sold_out'");
    
    if ($result && $result->num_rows > 0) {
        $response['success'] = true;
        $response['message'] = "Column 'is_sold_out' already exists. No action needed.";
    } else {
        // Add column
        $sql = "ALTER TABLE menu ADD COLUMN is_sold_out TINYINT(1) DEFAULT 0";
        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = "Migration successful: Column 'is_sold_out' added to table 'menu'.";
        } else {
            throw new Exception("Error adding column: " . $conn->error);
        }
    }

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage()
    ]);
}
?>
