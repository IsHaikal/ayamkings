<?php
// get_all_ratings_summary.php (NEW FILE: Fetches average rating and count for all menu items)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'ratings_summary' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // SQL to get average rating and count for each menu item
    $sql = "SELECT
                menu_item_id,
                AVG(rating) AS average_rating,
                COUNT(id) AS review_count
            FROM
                reviews
            GROUP BY
                menu_item_id";

    $result = $conn->query($sql);

    $ratings_summary = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $ratings_summary[] = $row;
        }
        $response['success'] = true;
        $response['ratings_summary'] = $ratings_summary;
        $response['message'] = 'Ratings summary fetched successfully.';
    } else {
        $response['message'] = 'Error fetching ratings summary: ' . $conn->error;
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>