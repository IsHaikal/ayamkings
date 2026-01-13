<?php
// toggle_sold_out.php - Toggle sold out status for a menu item

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

    $item_id = $data['item_id'] ?? null;
    $is_sold_out = isset($data['is_sold_out']) ? (int)$data['is_sold_out'] : null;

    if (empty($item_id) || $is_sold_out === null) {
        $response['message'] = 'Missing item_id or is_sold_out status.';
        echo json_encode($response);
        exit();
    }

    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

    // Update the sold out status
    $stmt = $conn->prepare("UPDATE menu SET is_sold_out = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_sold_out, $item_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = $is_sold_out ? 'Item marked as sold out.' : 'Item now available.';
        $response['is_sold_out'] = $is_sold_out;
    } else {
        $response['message'] = 'Error updating status: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
