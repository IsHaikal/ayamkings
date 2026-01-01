<?php
// get_menu.php (UPDATED: Now fetches 'image_url')

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'menuItems' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Fetch menu items, ordered by ID
    $sql = "SELECT id, name, description, price, category, image_url FROM menu ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result) {
        $menuItems = [];
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        $response['success'] = true;
        $response['menuItems'] = $menuItems;
        $response['message'] = 'Menu items fetched successfully.';
    } else {
        $response['message'] = 'Error fetching menu items: ' . $conn->error;
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>