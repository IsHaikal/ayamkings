<?php
// get_menu.php - Fetch all menu items

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = [
    'success' => false, 
    'message' => 'An unknown error occurred.', 
    'menuItems' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Database connection
        require_once __DIR__ . '/db_config.php';
        $conn = getDbConnection();

        // Fetch menu items including is_sold_out
        $sql = "SELECT id, name, description, price, category, image_url, is_sold_out FROM menu ORDER BY id ASC";
        $result = $conn->query($sql);

        if ($result) {
            $menuItems = [];
            while ($row = $result->fetch_assoc()) {
                // Ensure numeric types are correct
                $row['price'] = (float)$row['price'];
                $row['id'] = (int)$row['id'];
                $row['is_sold_out'] = (int)$row['is_sold_out']; // Cast to int
                $menuItems[] = $row;
            }
            $response['success'] = true;
            $response['menuItems'] = $menuItems;
            $response['message'] = 'Menu items fetched successfully.';
        } else {
            throw new Exception("Database query failed: " . $conn->error);
        }

        $conn->close();
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (Exception $e) {
    if (isset($conn)) {
        $dbInfo = "[DB: " . DB_NAME . " @ " . DB_HOST . "]";
    } else {
        $dbInfo = "[DB: Not Connected]";
    }
    $response['success'] = false;
    $response['message'] = 'Server Error ' . $dbInfo . ': ' . $e->getMessage();
}

echo json_encode($response);
?>