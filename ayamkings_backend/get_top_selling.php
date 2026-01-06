<?php
// get_top_selling.php - Get top selling menu items

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'items' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Database connection
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Get limit parameter (default 10)
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    if ($limit < 1 || $limit > 50) $limit = 10;

    // Fetch all non-cancelled orders and aggregate by item
    $sql = "SELECT id, items_json FROM orders WHERE status != 'cancelled'";
    $result = $conn->query($sql);
    $row_count = ($result ? $result->num_rows : 0);
    error_log("[DEBUG] get_top_selling.php - Found rows: " . $row_count);

    $itemSales = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items = json_decode($row['items_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("[DEBUG] get_top_selling.php - JSON Decoded Error: " . json_last_error_msg());
            }
            if ($items && is_array($items)) {
                foreach ($items as $item) {
                    $itemName = $item['name'] ?? 'Unknown Item';
                    $quantity = intval($item['quantity'] ?? 1);
                    
                    if (isset($itemSales[$itemName])) {
                        $itemSales[$itemName] += $quantity;
                    } else {
                        $itemSales[$itemName] = $quantity;
                    }
                }
            }
        }
    }

    // Sort by quantity descending
    arsort($itemSales);

    // Convert to array format and limit results
    $topItems = [];
    $count = 0;
    foreach ($itemSales as $name => $quantity) {
        if ($count >= $limit) break;
        $topItems[] = [
            'name' => $name,
            'quantity_sold' => $quantity
        ];
        $count++;
    }

    $response['success'] = true;
    $response['items'] = $topItems;
    $response['row_count'] = $row_count;
    $response['message'] = 'Top selling items fetched successfully.';

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
