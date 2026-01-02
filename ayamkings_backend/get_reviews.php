<?php
// get_reviews.php (NEW FILE: Fetches reviews for a specific menu item)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'reviews' => [], 'average_rating' => 0.0];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $menu_item_id = isset($_GET['menu_item_id']) ? intval($_GET['menu_item_id']) : 0;

    if (empty($menu_item_id)) {
        $response['message'] = 'Menu item ID is required.';
        echo json_encode($response);
        exit();
    }

    // Database connection
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Fetch reviews
    $stmt = $conn->prepare("SELECT r.id, r.user_id, u.full_name AS reviewer_name, r.rating, r.comment, r.review_date
                            FROM reviews r
                            JOIN users u ON r.user_id = u.id
                            WHERE r.menu_item_id = ?
                            ORDER BY r.review_date DESC");
    $stmt->bind_param("i", $menu_item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];
    $total_rating = 0;
    $review_count = 0;

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
            $total_rating += $row['rating'];
            $review_count++;
        }
    }
    $stmt->close();

    // Calculate average rating
    $average_rating = ($review_count > 0) ? round($total_rating / $review_count, 1) : 0.0;

    $response['success'] = true;
    $response['reviews'] = $reviews;
    $response['average_rating'] = $average_rating;
    $response['message'] = 'Reviews fetched successfully.';

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>