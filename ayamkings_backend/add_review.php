<?php
// add_review.php (NEW FILE: Handles submission of customer reviews)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $menu_item_id = $data['menu_item_id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? null;

    if (empty($menu_item_id) || empty($user_id) || empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        $response['message'] = 'Missing or invalid review data (menu item ID, user ID, or rating).';
        echo json_encode($response);
        exit();
    }

    // Database connection
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Check if the user has already reviewed this item
    $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE menu_item_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $menu_item_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response['message'] = 'You have already submitted a review for this item.';
        echo json_encode($response);
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Insert new review
    $stmt = $conn->prepare("INSERT INTO reviews (menu_item_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $menu_item_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Review submitted successfully!';
    } else {
        $response['message'] = 'Error submitting review: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>