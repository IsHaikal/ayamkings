<?php
// delete_review.php
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
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    $input = json_decode(file_get_contents('php://input'), true);
    $reviewId = isset($input['review_id']) ? intval($input['review_id']) : 0;

    if ($reviewId > 0) {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $reviewId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Review deleted successfully.';
            } else {
                $response['message'] = 'Review not found or already deleted.';
            }
        } else {
            $response['message'] = 'Error deleting review: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Invalid review ID.';
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
