<?php
// coupon_crud.php - Admin Coupon Management
require_once __DIR__ . '/cors.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $input['action'] ?? '';

require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

if ($action === 'list') {
    $sql = "SELECT * FROM coupons ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $coupons = [];
    while ($row = $result->fetch_assoc()) {
        $coupons[] = $row;
    }
    echo json_encode(['success' => true, 'coupons' => $coupons]);

} elseif ($action === 'create') {
    $code = $input['code'] ?? '';
    $type = $input['discount_type'] ?? 'percent';
    $value = $input['discount_value'] ?? 0;
    
    if (empty($code) || empty($value)) {
        echo json_encode(['success' => false, 'message' => 'Code and value are required.']);
        exit();
    }

    if ($value < 0) {
        echo json_encode(['success' => false, 'message' => 'Discount value cannot be negative.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $code, $type, $value);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Coupon created.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();

} elseif ($action === 'delete') {
    // Soft delete (set active = 0) or hard delete? Let's do Toggle Active for now or Hard Delete if requested.
    // Let's implement Delete for cleanup.
    $id = $input['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Coupon deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>
