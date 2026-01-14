<?php
// DEBUG_MARKER_MENU_CRUD_TEST_20240625_X

require_once __DIR__ . '/cors.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

error_log("[DEBUG] menu_crud.php received a " . $_SERVER['REQUEST_METHOD'] . " request.");

// Database connection
require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

// Get JSON data for POST and PUT requests
$data = json_decode(file_get_contents('php://input'), true);
error_log("[DEBUG] Raw input: " . file_get_contents('php://input'));
error_log("[DEBUG] Decoded JSON data: " . print_r($data, true));

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST': // Add new menu item
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0.00;
        $image_url = $data['image_url'] ?? 'https://placehold.co/100x100/FFD700/8B4513?text=Item';
        error_log("[DEBUG POST] Name: '$name', Desc: '$description', Price: '$price', Category: '$category', Image_URL: '$image_url'");

        if (empty($name) || !is_numeric($price) || empty($category)) {
            $response['message'] = 'Name, price, and category are required.';
            error_log("[ERROR POST] Missing required fields.");
            echo json_encode($response);
            exit();
        }

        if ($price < 0) {
            $response['message'] = 'Price cannot be negative.';
            echo json_encode($response);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO menu (name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
             error_log("[ERROR POST] Prepare failed: (" . $conn->errno . ") " . $conn->error);
             $response['message'] = 'Prepare failed: ' . $conn->error;
             echo json_encode($response);
             exit();
        }
        $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_url);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Menu item added successfully!';
            $response['id'] = $conn->insert_id;
            error_log("[DEBUG POST] Item added successfully. ID: " . $conn->insert_id);
        } else {
            $response['message'] = 'Error adding menu item: ' . $stmt->error;
            error_log("[ERROR POST] Execute failed: " . $stmt->error);
        }
        $stmt->close();
        break;

    case 'PUT': // Update existing menu item
        $id = $_GET['id'] ?? null;
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0.00;
        $category = $data['category'] ?? ''; // Expecting string
        $image_url = $data['image_url'] ?? null;
        $is_available = isset($data['is_available']) ? intval($data['is_available']) : null; // Only update if provided

        error_log("[DEBUG PUT] ID from GET: '$id'");
        error_log("[DEBUG PUT] Name from POST data: '$name'");
        error_log("[DEBUG PUT] Category from POST data: '$category'");
        error_log("[DEBUG PUT] Image_URL from POST data: " . ($image_url === null ? 'null' : "'$image_url'"));
        error_log("[DEBUG PUT] Available from POST data: " . ($is_available === null ? 'null' : "'$is_available'"));

        if (empty($id) || empty($name) || empty($price) || empty($category)) {
            $response['message'] = 'ID, name, price, and category are required for update.';
            error_log("[ERROR PUT] Missing required fields for update. ID: $id, Name: $name, Price: $price, Category: $category");
            echo json_encode($response);
            exit();
        }

        // Construct the UPDATE query dynamically based on what's provided
        $update_fields = [];
        $bind_params = '';
        $bind_values = [];

        // Always include name, description, price, category
        $update_fields[] = "name = ?"; $bind_params .= "s"; $bind_values[] = $name;
        $update_fields[] = "description = ?"; $bind_params .= "s"; $bind_values[] = $description;
        $update_fields[] = "price = ?"; $bind_params .= "d"; $bind_values[] = $price;
        $update_fields[] = "category = ?"; $bind_params .= "s"; $bind_values[] = $category;

        // Update is_available removed


        // Only update image_url if it was explicitly provided (e.g., from a new upload)
        if ($image_url !== null) {
            $update_fields[] = "image_url = ?";
            $bind_params .= "s";
            $bind_values[] = $image_url;
            error_log("[DEBUG PUT] Image URL included in update: '$image_url'");
        }

        if (empty($update_fields)) {
            $response['message'] = 'No fields to update.';
            error_log("[ERROR PUT] No fields to update generated.");
            echo json_encode($response);
            exit();
        }

        $sql = "UPDATE menu SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $bind_params .= "i"; // Add 'i' for the ID at the end
        $bind_values[] = $id;

        error_log("[DEBUG PUT] SQL Query: '$sql'");
        error_log("[DEBUG PUT] Bind Parameters: '$bind_params'");
        error_log("[DEBUG PUT] Bind Values (raw): " . print_r($bind_values, true));

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
             error_log("[ERROR PUT] Prepare failed: (" . $conn->errno . ") " . $conn->error);
             $response['message'] = 'Prepare failed: ' . $conn->error;
             echo json_encode($response);
             exit();
        }

        // Use call_user_func_array to bind_param
        // The values need to be passed by reference for bind_param
        $refs = [];
        foreach($bind_values as $key => $value) {
            $refs[$key] = &$bind_values[$key]; // Pass by reference
        }
        // Prepend the bind_params string to the array of references
        array_unshift($refs, $bind_params);

        error_log("[DEBUG PUT] Bind Values (references for bind_param): " . print_r($refs, true));
        
        call_user_func_array([$stmt, 'bind_param'], $refs);


        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Menu item updated successfully!';
            error_log("[DEBUG PUT] Item updated successfully. ID: '$id'");
        } else {
            $response['message'] = 'Error updating menu item: ' . $stmt->error;
            error_log("[ERROR PUT] Execute failed for ID '$id': " . $stmt->error);
        }
        $stmt->close();
        break;

    case 'DELETE': // Delete menu item
        $id = $_GET['id'] ?? null;
        error_log("[DEBUG DELETE] ID from GET: '$id'");
        if (empty($id)) {
            $response['message'] = 'ID is required for deletion.';
            error_log("[ERROR DELETE] Missing ID for deletion.");
            echo json_encode($response);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
        if ($stmt === false) {
             error_log("[ERROR DELETE] Prepare failed: (" . $conn->errno . ") " . $conn->error);
             $response['message'] = 'Prepare failed: ' . $conn->error;
             echo json_encode($response);
             exit();
        }
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Menu item deleted successfully!';
            error_log("[DEBUG DELETE] Item ID '$id' deleted successfully.");
        } else {
            $response['message'] = 'Error deleting menu item: ' . $stmt->error;
            error_log("[ERROR DELETE] Execute failed for ID '$id': " . $stmt->error);
        }
        $stmt->close();
        break;

    default:
        $response['message'] = 'Invalid request method.';
        error_log("[ERROR] Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        break;
}

$conn->close();
echo json_encode($response);
?>