# AyamKings System - Complete Code Documentation

**Project:** AyamKings Food Ordering System  
**Date:** 24 December 2025  
**Technology Stack:** PHP, MySQL, HTML, CSS (Tailwind), JavaScript

---

## Table of Contents

### Backend APIs (PHP)
1. [Authentication](#1-authentication)
   - login.php
   - register.php
2. [Menu Management](#2-menu-management)
   - get_menu.php
3. [Order Management](#3-order-management)
   - place_order.php
   - get_orders.php
   - update_order_status.php
4. [Review System](#4-review-system)
   - add_review.php
   - update_review.php
5. [Coupon System](#5-coupon-system)
   - validate_coupon.php
6. [Daily Specials](#6-daily-specials)
   - daily_specials_crud.php
7. [Statistics & Analytics](#7-statistics--analytics)
   - get_statistics.php

### Frontend (JavaScript)
8. [Configuration & Session](#8-configuration--session)
   - config.js

---

## 1. Authentication

### login.php
**Purpose:** Handles user authentication and returns session token.

```php
<?php
// login.php - User Authentication

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'], $input['password'])) {
        $response['message'] = 'Missing email or password.';
        echo json_encode($response);
        exit();
    }

    $email = $input['email'];
    $password = $input['password'];

    // Database connection
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "ayamkings_db";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed: ' . $conn->connect_error;
        echo json_encode($response);
        exit();
    }

    // Retrieve user from database
    $stmt = $conn->prepare("SELECT id, full_name, email, password, phone, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            $response['token'] = bin2hex(random_bytes(16));
            $response['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role']
            ];
        } else {
            $response['message'] = 'Invalid credentials.';
        }
    } else {
        $response['message'] = 'Invalid credentials.';
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
```

**Key Functions:**
- `password_verify()` - Verifies hashed password
- `bin2hex(random_bytes(16))` - Generates session token

---

### register.php
**Purpose:** Handles new user registration with password hashing.

```php
<?php
// register.php - User Registration

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
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['full_name'], $input['email'], $input['password'], $input['phone'], $input['role'])) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit();
    }

    $full_name = $input['full_name'];
    $email = $input['email'];
    $password = $input['password'];
    $phone = $input['phone'];
    $role = $input['role'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "ayamkings_db");

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response['message'] = 'Email already registered.';
        echo json_encode($response);
        exit();
    }
    $stmt->close();

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $role);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Registration successful!';
    } else {
        $response['message'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
```

**Key Functions:**
- `password_hash($password, PASSWORD_DEFAULT)` - Securely hashes password
- Duplicate email check before registration

---

## 2. Menu Management

### get_menu.php
**Purpose:** Fetches all menu items for display.

```php
<?php
// get_menu.php - Fetch all menu items

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'menuItems' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = new mysqli("localhost", "root", "", "ayamkings_db");

    // Fetch menu items, ordered by ID
    $sql = "SELECT id, name, description, price, category, image_url FROM menu ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result) {
        $menuItems = [];
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        $response['success'] = true;
        $response['menuItems'] = $menuItems;
        $response['message'] = 'Menu items fetched successfully.';
    }

    $conn->close();
}

echo json_encode($response);
?>
```

---

## 3. Order Management

### place_order.php
**Purpose:** Creates new order with coupon support.

```php
<?php
// place_order.php - Create new order

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

    $user_id = $data['user_id'] ?? null;
    $total_price = $data['total_price'] ?? null;
    $items = $data['items'] ?? [];
    $coupon_code = $data['coupon_code'] ?? null;
    $discount_amount = $data['discount_amount'] ?? 0;

    if (empty($user_id) || !is_numeric($total_price) || empty($items)) {
        $response['message'] = 'Missing or invalid order data.';
        echo json_encode($response);
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "ayamkings_db");

    // Verify Coupon if provided
    if ($coupon_code) {
        $stmt_coupon = $conn->prepare("SELECT id, times_used FROM coupons WHERE code = ? AND is_active = 1");
        $stmt_coupon->bind_param("s", $coupon_code);
        $stmt_coupon->execute();
        $res_coupon = $stmt_coupon->get_result();

        if ($res_coupon->num_rows > 0) {
            $coupon = $res_coupon->fetch_assoc();
            // Increment usage
            $stmt_update = $conn->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE id = ?");
            $stmt_update->bind_param("i", $coupon['id']);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $discount_amount = 0;
            $coupon_code = null;
        }
        $stmt_coupon->close();
    }

    $final_amount = $total_price - $discount_amount;
    if ($final_amount < 0) $final_amount = 0;

    $initial_status = "Pending";
    $items_json = json_encode($items);

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, items_json) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $final_amount, $initial_status, $items_json);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Order placed successfully!' . ($coupon_code ? ' (Coupon Applied)' : '');
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
```

**Key Features:**
- Coupon validation and usage tracking
- Items stored as JSON in database

---

### get_orders.php
**Purpose:** Fetches all orders for staff dashboard.

```php
<?php
// get_orders.php - Fetch all orders

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$response = ['success' => false, 'orders' => []];

$conn = new mysqli("localhost", "root", "", "ayamkings_db");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT
                o.id AS order_id,
                o.user_id,
                u.full_name AS customer_name,
                u.email AS customer_email,
                u.phone AS customer_phone,
                o.total_amount,
                o.status,
                o.order_date,
                o.items_json
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.order_date DESC";

    $result = $conn->query($sql);

    if ($result) {
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $decoded_items = json_decode($row['items_json'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_items)) {
                $decoded_items = [];
            }

            $orders[] = [
                'id' => $row['order_id'],
                'user_id' => $row['user_id'],
                'customer_name' => $row['customer_name'],
                'customer_email' => $row['customer_email'],
                'customer_phone' => $row['customer_phone'],
                'total_amount' => $row['total_amount'],
                'status' => $row['status'],
                'order_date' => $row['order_date'],
                'items' => $decoded_items
            ];
        }

        $response['success'] = true;
        $response['orders'] = $orders;
    }
}

$conn->close();
echo json_encode($response);
?>
```

---

### update_order_status.php
**Purpose:** Updates order status (Pending → Preparing → Ready → Finished).

```php
<?php
// update_order_status.php - Update order status

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    $order_id = $data['order_id'] ?? null;
    $status = $data['status'] ?? null;

    // Validate status against allowed values
    $allowed_statuses = ['Pending', 'Preparing', 'Ready for Pickup', 'Finished', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $response['message'] = 'Invalid status value provided.';
        echo json_encode($response);
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "ayamkings_db");

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Order status updated successfully.';
        }
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
```

**Allowed Statuses:**
- Pending
- Preparing
- Ready for Pickup
- Finished
- Cancelled

---

## 4. Review System

### add_review.php
**Purpose:** Submits new customer review for menu item.

```php
<?php
// add_review.php - Submit new review

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $menu_item_id = $data['menu_item_id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? null;

    if (empty($menu_item_id) || empty($user_id) || empty($rating) || $rating < 1 || $rating > 5) {
        $response['message'] = 'Missing or invalid review data.';
        echo json_encode($response);
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "ayamkings_db");

    // Check if user already reviewed this item
    $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE menu_item_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $menu_item_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response['message'] = 'You have already submitted a review for this item.';
        echo json_encode($response);
        exit();
    }
    $check_stmt->close();

    // Insert new review
    $stmt = $conn->prepare("INSERT INTO reviews (menu_item_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $menu_item_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Review submitted successfully!';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
```

---

### update_review.php
**Purpose:** Updates existing customer review.

```php
<?php
// update_review.php - Update existing review

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $review_id = $data['review_id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? '';

    if (empty($review_id) || empty($user_id) || empty($rating) || $rating < 1 || $rating > 5) {
        $response['message'] = 'Missing or invalid data.';
        echo json_encode($response);
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "ayamkings_db");

    // Verify review belongs to user
    $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $review_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $response['message'] = 'Review not found or no permission to edit.';
        echo json_encode($response);
        exit();
    }
    $check_stmt->close();

    // Update the review
    $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ?, review_date = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Review updated successfully!';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
```

---

## 5. Coupon System

### validate_coupon.php
**Purpose:** Validates coupon code and returns discount info.

```php
<?php
// validate_coupon.php - Validate coupon code

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['code'])) {
    echo json_encode(['success' => false, 'message' => 'Coupon code is required.']);
    exit();
}

$code = $input['code'];
$conn = new mysqli("localhost", "root", "", "ayamkings_db");

// Check coupon availability
$stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or inactive coupon code.']);
    $conn->close();
    exit();
}

$coupon = $result->fetch_assoc();
$now = new DateTime();

// Check Expiry
if ($coupon['valid_until'] && new DateTime($coupon['valid_until']) < $now) {
    echo json_encode(['success' => false, 'message' => 'This coupon has expired.']);
    $conn->close();
    exit();
}

// Check Max Uses
if ($coupon['max_uses'] !== null && $coupon['times_used'] >= $coupon['max_uses']) {
    echo json_encode(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
    $conn->close();
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'Coupon applied successfully!',
    'discount_type' => $coupon['discount_type'],
    'discount_value' => floatval($coupon['discount_value']),
    'code' => $coupon['code']
]);

$conn->close();
?>
```

**Validation Checks:**
1. Coupon exists and is active
2. Not expired (valid_until)
3. Usage limit not reached (max_uses)

---

## 6. Daily Specials

### daily_specials_crud.php
**Purpose:** CRUD operations for daily specials management.

```php
<?php
// daily_specials_crud.php - CRUD for Daily Specials

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "ayamkings_db");
$response = ['success' => false, 'message' => 'Unknown error'];
$method = $_SERVER['REQUEST_METHOD'];

// ========== GET: Fetch all specials ==========
if ($method === 'GET') {
    $activeOnly = isset($_GET['active']) && $_GET['active'] == '1';
    
    if ($activeOnly) {
        $sql = "SELECT * FROM daily_specials WHERE is_active = 1 AND end_date > NOW() ORDER BY created_at DESC";
    } else {
        $sql = "SELECT * FROM daily_specials ORDER BY created_at DESC";
    }
    
    $result = $conn->query($sql);
    $specials = [];
    
    while ($row = $result->fetch_assoc()) {
        $specials[] = $row;
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
    
    $stmt = $conn->prepare("INSERT INTO daily_specials (name, description, price, image_url, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $end_date, $created_by);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Daily Special created!', 'id' => $conn->insert_id];
    }
    $stmt->close();
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
    
    $stmt = $conn->prepare("UPDATE daily_specials SET name=?, description=?, price=?, image_url=?, end_date=?, is_active=? WHERE id=?");
    $stmt->bind_param("ssdssii", $name, $description, $price, $image_url, $end_date, $is_active, $id);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Daily Special updated!'];
    }
    $stmt->close();
}

// ========== DELETE: Remove special ==========
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    $stmt = $conn->prepare("DELETE FROM daily_specials WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Daily Special deleted!'];
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
```

**CRUD Operations:**
- GET: Fetch all specials (with optional active filter)
- POST: Create new special
- PUT: Update existing special
- DELETE: Remove special

---

## 7. Statistics & Analytics

### get_statistics.php
**Purpose:** Fetches sales, expenses, profit data with chart information.

```php
<?php
// get_statistics.php - Fetch analytics data

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = new mysqli("localhost", "root", "", "ayamkings_db");

    $statistics = [
        'daily_sales' => 0.00,
        'monthly_sales' => 0.00,
        'monthly_expenses' => 0.00,
        'monthly_profit' => 0.00,
        'chart_data' => ['labels' => [], 'sales' => [], 'expenses' => []]
    ];

    // Date Range (from URL or default)
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');

    // Sales in Range
    $sql_range_sales = "SELECT SUM(total_amount) AS total FROM orders 
                         WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' 
                         AND status != 'cancelled'";
    $result = $conn->query($sql_range_sales);
    $statistics['monthly_sales'] = ($result->fetch_assoc()['total']) ?? 0.00;

    // Expenses in Range
    $sql_range_expenses = "SELECT SUM(amount) AS total FROM expenses 
                            WHERE DATE(expense_date) BETWEEN '$start_date' AND '$end_date'";
    $result = $conn->query($sql_range_expenses);
    $statistics['monthly_expenses'] = ($result->fetch_assoc()['total']) ?? 0.00;

    // Daily Sales (Today)
    $sql_today = "SELECT SUM(total_amount) AS total FROM orders 
                   WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'";
    $result = $conn->query($sql_today);
    $statistics['daily_sales'] = ($result->fetch_assoc()['total']) ?? 0.00;

    // Profit Calculation
    $statistics['monthly_profit'] = $statistics['monthly_sales'] - $statistics['monthly_expenses'];

    // Chart Data (Last 12 Months)
    $sql_chart_sales = "SELECT YEAR(order_date) AS year, MONTH(order_date) AS month, SUM(total_amount) AS total_sales
                        FROM orders
                        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND status != 'cancelled'
                        GROUP BY year, month
                        ORDER BY year ASC, month ASC";
    $sales_result = $conn->query($sql_chart_sales);
    $sales_data = [];
    while ($row = $sales_result->fetch_assoc()) {
        $sales_data[sprintf('%04d-%02d', $row['year'], $row['month'])] = $row['total_sales'];
    }

    // Top Selling Items (Last 30 Days)
    $sql_top_items = "SELECT items_json FROM orders 
                       WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status != 'cancelled'";
    $res_top = $conn->query($sql_top_items);
    $item_sales = [];

    while ($row = $res_top->fetch_assoc()) {
        $items = json_decode($row['items_json'], true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $name = $item['name'] ?? 'Unknown';
                $qty = $item['quantity'] ?? 0;
                $item_sales[$name] = ($item_sales[$name] ?? 0) + $qty;
            }
        }
    }

    arsort($item_sales);
    $top_5 = array_slice($item_sales, 0, 5, true);
    
    $statistics['top_selling'] = [];
    foreach ($top_5 as $name => $qty) {
        $statistics['top_selling'][] = ['name' => $name, 'sold' => $qty];
    }

    $response['success'] = true;
    $response['statistics'] = $statistics;

    $conn->close();
}

echo json_encode($response);
?>
```

**Data Returned:**
- Daily Sales (today)
- Monthly Sales (range)
- Monthly Expenses (range)
- Monthly Profit (calculated)
- Chart Data (12 months)
- Top 5 Selling Items

---

## 8. Configuration & Session

### config.js
**Purpose:** Central configuration for API URLs and session management.

```javascript
// config.js - Central Configuration

const CONFIG = {
    // API Backend URL
    API_BASE_URL: window.location.port === '5500'
        ? 'http://localhost:8000'
        : window.location.origin + '/Coding%20PSM/ayamkings_backend',

    // Frontend Base URL
    FRONTEND_BASE_URL: window.location.origin + 
        (window.location.port === '5500' ? '' : '/Coding%20PSM/ayamkings_frontend'),

    // Uploads folder URL
    UPLOADS_URL: window.location.port === '5500'
        ? 'http://localhost:5500/uploads'
        : window.location.origin + '/Coding%20PSM/ayamkings_frontend/uploads'
};

// Helper function to get API endpoint
function getApiUrl(endpoint) {
    return `${CONFIG.API_BASE_URL}/${endpoint}`;
}

// Helper function to get upload image URL
function getUploadUrl(imageUrl) {
    if (!imageUrl || imageUrl.trim() === '') {
        return 'https://placehold.co/100x100/FFD700/8B4513?text=Item';
    }
    
    if (imageUrl.includes('placehold.co')) {
        return imageUrl;
    }

    if (imageUrl.includes('localhost')) {
        const parts = imageUrl.split('/');
        const filename = parts[parts.length - 1];
        return `${CONFIG.UPLOADS_URL}/${filename}`;
    }

    if (!imageUrl.startsWith('http')) {
        const filename = imageUrl.includes('/') ? imageUrl.split('/').pop() : imageUrl;
        return `${CONFIG.UPLOADS_URL}/${filename}`;
    }

    return imageUrl;
}

// ==========================================
// Session Management (Auto-Logout after 1 Hour)
// ==========================================
const SESSION_TIMEOUT_MS = 1 * 60 * 60 * 1000; // 1 Hour

function startSession() {
    localStorage.setItem('lastActivity', Date.now());
}

function checkSession() {
    const token = localStorage.getItem('userToken');
    if (!token) return;

    const lastActivity = localStorage.getItem('lastActivity');
    if (lastActivity) {
        const timeElapsed = Date.now() - parseInt(lastActivity);

        if (timeElapsed > SESSION_TIMEOUT_MS) {
            localStorage.clear();
            alert("Your session has expired. Please log in again.");
            window.location.href = 'index.html';
        }
    } else {
        startSession();
    }
}

function resetSessionTimer() {
    if (localStorage.getItem('userToken')) {
        startSession();
    }
}

// Activity listeners
window.addEventListener('mousemove', resetSessionTimer);
window.addEventListener('keydown', resetSessionTimer);
window.addEventListener('click', resetSessionTimer);
window.addEventListener('scroll', resetSessionTimer);

// Check on load and every minute
checkSession();
setInterval(checkSession, 60 * 1000);

// Export functions
window.startSession = startSession;
window.checkSession = checkSession;
```

**Key Features:**
- Dynamic URL configuration for development/production
- Session management with 1-hour timeout
- Activity-based session refresh
- Helper functions for API and upload URLs

---

## Database Schema Summary

### Tables:
1. **users** - id, full_name, email, password, phone, role
2. **menu** - id, name, description, price, category, image_url
3. **orders** - id, user_id, total_amount, status, items_json, order_date
4. **reviews** - id, menu_item_id, user_id, rating, comment, review_date
5. **coupons** - id, code, discount_type, discount_value, valid_until, max_uses, times_used, is_active
6. **daily_specials** - id, name, description, price, image_url, end_date, is_active, created_by
7. **expenses** - id, amount, expense_date, description

---

*Document End*
