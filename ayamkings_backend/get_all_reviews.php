<?php
// get_all_reviews.php - Fetch all reviews for Admin Dashboard

// Headers removed to prevent duplication

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = [
    'success' => false, 
    'message' => 'An unknown error occurred.', 
    'reviews' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Join reviews with users and menu tables
    $sql = "SELECT 
                r.id, 
                r.rating, 
                r.comment, 
                r.review_date,
                u.full_name as user_name,
                m.name as menu_item_name,
                m.image_url as menu_item_image
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN menu m ON r.menu_item_id = m.id
            ORDER BY r.review_date DESC";

    $result = $conn->query($sql);

    if ($result) {
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        $response['success'] = true;
        $response['reviews'] = $reviews;
    } else {
        $response['message'] = 'Database error: ' . $conn->error;
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
