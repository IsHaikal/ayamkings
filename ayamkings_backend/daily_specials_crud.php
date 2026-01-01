<?php
// daily_specials_crud.php
// CRUD operations for Daily Specials (Staff feature)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "ayamkings_db";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB Connection failed']);
    exit();
}

$response = ['success' => false, 'message' => 'Unknown error'];
$method = $_SERVER['REQUEST_METHOD'];

// ========== GET: Fetch all specials ==========
if ($method === 'GET') {
    $activeOnly = isset($_GET['active']) && $_GET['active'] == '1';
    
    if ($activeOnly) {
        // Only active and not expired
        $sql = "SELECT * FROM daily_specials WHERE is_active = 1 AND end_date > NOW() ORDER BY created_at DESC";
    } else {
        // All specials for management
        $sql = "SELECT * FROM daily_specials ORDER BY created_at DESC";
    }
    
    $result = $conn->query($sql);
    $specials = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $specials[] = $row;
        }
    }
    
    $response = ['success' => true, 'specials' => $specials];
}

// ========== POST: Create new special ==========
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? 0;
    $image_url = $data['image_url'] ?? '';
    $end_date = $data['end_date'] ?? '';
    $created_by = $data['created_by'] ?? null;
    
    if (empty($name) || empty($price) || empty($end_date)) {
        $response['message'] = 'Name, price, and end date are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO daily_specials (name, description, price, image_url, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $end_date, $created_by);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Daily Special created!', 'id' => $conn->insert_id];
        } else {
            $response['message'] = 'Error creating special: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// ========== PUT: Update special ==========
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? null;
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? 0;
    $image_url = $data['image_url'] ?? '';
    $end_date = $data['end_date'] ?? '';
    $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
    
    if (empty($id)) {
        $response['message'] = 'Special ID is required.';
    } else {
        $stmt = $conn->prepare("UPDATE daily_specials SET name=?, description=?, price=?, image_url=?, end_date=?, is_active=? WHERE id=?");
        $stmt->bind_param("ssdssii", $name, $description, $price, $image_url, $end_date, $is_active, $id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Daily Special updated!'];
        } else {
            $response['message'] = 'Error updating special: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// ========== DELETE: Remove special ==========
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    if (empty($id)) {
        $response['message'] = 'Special ID is required.';
    } else {
        $stmt = $conn->prepare("DELETE FROM daily_specials WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Daily Special deleted!'];
        } else {
            $response['message'] = 'Error deleting special: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>
