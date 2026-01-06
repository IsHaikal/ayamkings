<?php
// generate_report.php (UPDATED: Supports CSV and PDF formats)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

// Get format parameter (default to CSV)
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';

// Fetch all orders with customer details
$sql = "SELECT
            o.id AS order_id,
            u.full_name AS customer_name,
            u.email AS customer_email,
            u.phone AS customer_phone,
            o.total_amount AS total_amount,
            o.status,
            o.order_date,
            o.items_json
        FROM
            orders o
        JOIN
            users u ON o.user_id = u.id
        ORDER BY
            o.order_date DESC";

$result = $conn->query($sql);
$orders = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

    // Calculate total revenue and total orders
    $sql_total = "SELECT SUM(total_amount) as total_revenue, COUNT(*) as total_orders FROM orders";
    $result_total = $conn->query($sql_total);
    $total_data = $result_total->fetch_assoc();

    $report_data['summary']['total_revenue'] = $total_data['total_revenue'] ?? 0;
    $report_data['summary']['total_orders'] = $total_data['total_orders'] ?? 0;

    // Calculate revenue per day (for chart)
    $sql_daily = "SELECT DATE(order_date) as date, SUM(total_amount) as daily_revenue FROM orders GROUP BY DATE(order_date) ORDER BY date ASC";
    $result_daily = $conn->query($sql_daily);

    while($row = $result_daily->fetch_assoc()) {
        $report_data['daily_sales']['labels'][] = $row['date'];
        $report_data['daily_sales']['data'][] = $row['daily_revenue'];
    }
if ($format === 'pdf') {
    // Generate PDF-ready HTML page
    header('Content-Type: text/html; charset=utf-8');
    
    // Calculate summary
    $totalSales = 0;
    $finishedOrders = 0;
    $pendingOrders = 0;
    
    foreach ($orders as $order) {
        $totalSales += floatval($order['total_amount']);
        if ($order['status'] === 'completed') $finishedOrders++;
        if ($order['status'] === 'pending') $pendingOrders++;
    }
    
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayam Kings - Sales Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f59e0b;
        }
        .header h1 { 
            color: #f59e0b; 
            font-size: 28px; 
            margin-bottom: 5px;
        }
        .header p { color: #666; font-size: 14px; }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .summary-card {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .summary-card.total { background: #dbeafe; color: #1e40af; }
        .summary-card.finished { background: #dcfce7; color: #166534; }
        .summary-card.pending { background: #fef3c7; color: #92400e; }
        .summary-card h3 { font-size: 24px; margin-bottom: 5px; }
        .summary-card p { font-size: 12px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            font-size: 12px;
        }
        th { 
            background: #f59e0b; 
            color: white; 
            padding: 12px 8px; 
            text-align: left;
            font-weight: 600;
        }
        td { 
            padding: 10px 8px; 
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) { background: #f9fafb; }
        tr:hover { background: #fef3c7; }
        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-preparing { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f59e0b;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .print-btn:hover { background: #d97706; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        🖨️ Print / Save as PDF
    </button>
    
    <div class="header">
        <h1>🍗 AYAM KINGS</h1>
        <p>Sales Report - Generated on <?php echo date('d F Y, h:i A'); ?></p>
    </div>
    
    <div class="summary">
        <div class="summary-card total">
            <h3>RM <?php echo number_format($totalSales, 2); ?></h3>
            <p>Total Sales</p>
        </div>
        <div class="summary-card finished">
            <h3><?php echo $finishedOrders; ?></h3>
            <p>Completed Orders</p>
        </div>
        <div class="summary-card pending">
            <h3><?php echo $pendingOrders; ?></h3>
            <p>Pending Orders</p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Total (RM)</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): 
                    $statusClass = 'status-' . strtolower(str_replace(' ', '-', $order['status']));
                ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                    <td><strong>RM <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    <td><span class="status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #9ca3af;">
                        No orders found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>© <?php echo date('Y'); ?> Ayam Kings. This report was automatically generated.</p>
        <p>Total Orders: <?php echo count($orders); ?></p>
    </div>
</body>
</html>
    <?php
    
} else {
    // Generate CSV (default)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ayam_kings_sales_report_' . date('Y-m-d_H-i-s') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Customer Name', 'Customer Email', 'Customer Phone', 'Total Price (RM)', 'Status', 'Order Date', 'Items Details (JSON)']);

    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_id'],
            $order['customer_name'],
            $order['customer_email'],
            $order['customer_phone'],
            number_format($order['total_amount'], 2),
            $order['status'],
            $order['order_date'],
            $order['items_json']
        ]);
    }

    fclose($output);
}

$conn->close();
exit();
?>