<?php
// fix_password_resets_table.php - One-time script to fix id column
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';

$response = ['success' => false, 'message' => 'Failed'];

try {
    $conn = getDbConnection();
    
    // First, drop the old id column and recreate with AUTO_INCREMENT
    // This is safe because the table should be empty or have test data only
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'password_resets'");
    
    if ($tableCheck->num_rows == 0) {
        // Table doesn't exist, create it properly
        $createSql = "CREATE TABLE password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            INDEX idx_email (email),
            INDEX idx_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($createSql)) {
            $response['success'] = true;
            $response['message'] = 'Table created successfully with AUTO_INCREMENT!';
        } else {
            throw new Exception($conn->error);
        }
    } else {
        // Table exists, fix the id column
        // First try to modify the column
        $alterSql = "ALTER TABLE password_resets MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY";
        
        // Try different approaches
        $conn->query("ALTER TABLE password_resets DROP PRIMARY KEY");
        $result = $conn->query("ALTER TABLE password_resets MODIFY id INT AUTO_INCREMENT PRIMARY KEY");
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Table fixed - id column now has AUTO_INCREMENT!';
        } else {
            // Alternative: Recreate the table
            $conn->query("DROP TABLE password_resets");
            $createSql = "CREATE TABLE password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                INDEX idx_email (email),
                INDEX idx_token (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if ($conn->query($createSql)) {
                $response['success'] = true;
                $response['message'] = 'Table recreated with proper AUTO_INCREMENT!';
            } else {
                throw new Exception($conn->error);
            }
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
