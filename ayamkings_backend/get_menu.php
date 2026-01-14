<?php
// get_menu.php - Fetch all menu items

require_once __DIR__ . '/cors.php'; // Handle CORS and Preflight

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

        // Fetch menu items
        $sql = "SELECT id, name, description, price, category, image_url FROM menu ORDER BY id ASC";
        $result = $conn->query($sql);

        if ($result) {
            $menuItems = [];
            while ($row = $result->fetch_assoc()) {
                // Ensure numeric types are correct
                $row['price'] = (float)$row['price'];
                $row['id'] = (int)$row['id'];
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
    $response['success'] = false;
    $response['message'] = 'Server Error: ' . $e->getMessage();
}

echo json_encode($response);
?>