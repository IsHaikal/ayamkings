<?php
// update_review.php - Handles updating existing customer reviews
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Accept both PUT and POST for compatibility
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $review_id = $data['review_id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? '';

    // Validate required fields
    if (empty($review_id) || empty($user_id) || empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        $response['message'] = 'Missing or invalid data (review ID, user ID, or rating).';
        echo json_encode($response);
        exit();
    }

    // Database connection
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Verify the review belongs to the user
    $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $review_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $response['message'] = 'Review not found or you do not have permission to edit it.';
        echo json_encode($response);
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Update the review
    $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ?, review_date = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Review updated successfully!';
    } else {
        $response['message'] = 'Error updating review: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
