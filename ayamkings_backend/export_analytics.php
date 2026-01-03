<?php
// export_analytics.php
// Exports current month's sales data to CSV

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();
$conn->set_charset("utf8mb4");

// Set Headers for CSV Download
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$filename = "ayamkings_sales_report_{$start_date}_to_{$end_date}.csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel compatibility with UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Headings
fputcsv($output, ['Order ID', 'Date', 'Customer Name', 'Items Summary', 'Total Amount (RM)', 'Status']);

// Use prepared statement for security
$sql = "SELECT 
            o.id, 
            o.order_date, 
            u.full_name, 
            o.items_json, 
            o.total_amount, 
            o.status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE DATE(o.order_date) BETWEEN ? AND ?
          AND o.status != 'cancelled'
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Parse Items to string - with proper escaping
        $items_str = "";
        $items_json = $row['items_json'];
        
        // Handle potential double-encoded JSON
        if (is_string($items_json) && substr($items_json, 0, 1) === '"') {
            $items_json = json_decode($items_json);
        }
        
        $items = json_decode($items_json, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($items)) {
            $item_parts = [];
            foreach ($items as $item) {
                $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                $name = isset($item['name']) ? trim($item['name']) : 'Unknown';
                // Remove any quotes or special chars that break CSV
                $name = str_replace(['"', ',', "\n", "\r"], ['', ' ', ' ', ''], $name);
                $item_parts[] = "{$qty}x {$name}";
            }
            $items_str = implode("; ", $item_parts);
        } else {
            $items_str = "N/A";
        }

        // Clean customer name
        $customer_name = str_replace(['"', ',', "\n", "\r"], ['', ' ', ' ', ''], $row['full_name']);
        
        // Format total without commas (commas break CSV)
        $total = number_format((float)$row['total_amount'], 2, '.', '');

        fputcsv($output, [
            $row['id'],
            $row['order_date'],
            $customer_name,
            $items_str,
            $total,
            ucfirst($row['status'])
        ]);
    }
}

$stmt->close();
fclose($output);
$conn->close();
?>
