<?php
// get_statistics.php (NEW FILE: Fetches daily sales, monthly profit, expenses, and chart data)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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

    $statistics = [
        'daily_sales' => 0.00,
        'monthly_sales' => 0.00,
        'monthly_expenses' => 0.00,
        'monthly_profit' => 0.00,
        'chart_data' => [
            'labels' => [],
            'sales' => [],
            'expenses' => []
        ]
    ];

    // Date Range Logic
    $start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default: Start of this month
    $end_date = $_GET['end_date'] ?? date('Y-m-d');     // Default: Today

    // --- Daily Sales (Sales on End Date or Last Day of Range) ---
    // If filtering, 'Daily' might be confusing. Let's keep it as "Today's Sales" or "Last Day of Range". 
    // Actually, dashboard typically shows "Daily Sales" = TODAY regardless of filter, OR Average?
    // Let's make "Daily Sales" always = TODAY for consistency, unless user wants specific day?
    // Better: Make the cards show summary of the RANGE.
    // "Daily Sales" -> "Total Sales" (Label in HTML is Daily.. I should probably rename it in HTML if I change logic, but let's stick to existing labels for now and just update 'Monthly' to be 'Range Total').
    
    // Actually, let's just make the cards reflect the RANGE totals.
    // 'Monthly Sales' -> 'Total Sales (Range)'
    // 'Monthly Expenses' -> 'Total Expenses (Range)'
    // 'Monthly Profit' -> 'Profit (Range)'

    // --- Sales in Range ---
    $sql_range_sales = "SELECT SUM(total_amount) AS total FROM orders WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' AND status != 'cancelled'";
    $result = $conn->query($sql_range_sales);
    $statistics['monthly_sales'] = ($result->fetch_assoc()['total']) ?? 0.00;

    // --- Expenses in Range ---
    $sql_range_expenses = "SELECT SUM(amount) AS total FROM expenses WHERE DATE(expense_date) BETWEEN '$start_date' AND '$end_date'";
    $result = $conn->query($sql_range_expenses);
    $statistics['monthly_expenses'] = ($result->fetch_assoc()['total']) ?? 0.00;

    // --- Daily Sales (Keep as Today for real-time monitoring) ---
    $sql_today = "SELECT SUM(total_amount) AS total FROM orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'";
    $result = $conn->query($sql_today);
    $statistics['daily_sales'] = ($result->fetch_assoc()['total']) ?? 0.00;


    // --- Monthly Profit (Sales - Expenses) ---
    $statistics['monthly_profit'] = $statistics['monthly_sales'] - $statistics['monthly_expenses'];

    // --- Chart Data (Last 12 Months Sales & Expenses) ---
    // Sales data
    $sql_chart_sales = "SELECT
                            YEAR(order_date) AS year,
                            MONTH(order_date) AS month,
                            SUM(total_amount) AS total_sales
                        FROM
                            orders
                        WHERE
                            order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND status != 'cancelled'
                        GROUP BY
                            year, month
                        ORDER BY
                            year ASC, month ASC";
    $sales_result = $conn->query($sql_chart_sales);
    $sales_data = [];
    if ($sales_result) {
        while ($row = $sales_result->fetch_assoc()) {
            $sales_data[sprintf('%04d-%02d', $row['year'], $row['month'])] = $row['total_sales'];
        }
    }

    // Expenses data
    $sql_chart_expenses = "SELECT
                                YEAR(expense_date) AS year,
                                MONTH(expense_date) AS month,
                                SUM(amount) AS total_expenses
                            FROM
                                expenses
                            WHERE
                                expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                            GROUP BY
                                year, month
                            ORDER BY
                                year ASC, month ASC";
    $expenses_result = $conn->query($sql_chart_expenses);
    $expenses_data = [];
    if ($expenses_result) {
        while ($row = $expenses_result->fetch_assoc()) {
            $expenses_data[sprintf('%04d-%02d', $row['year'], $row['month'])] = $row['total_expenses'];
        }
    }

    // Combine chart data for the last 12 months
    $current_date = new DateTime();
    for ($i = 11; $i >= 0; $i--) {
        $date = (clone $current_date)->modify("-$i month");
        $label = $date->format('M Y'); // e.g., Jan 2024
        $key = $date->format('Y-m'); // e.g., 2024-01

        $statistics['chart_data']['labels'][] = $label;
        $statistics['chart_data']['sales'][] = $sales_data[$key] ?? 0.00;
        $statistics['chart_data']['expenses'][] = $expenses_data[$key] ?? 0.00;
    }

    // --- Top Selling Items (Aggregation from JSON) ---
    // Fetch orders from last 30 days
    $sql_top_items = "SELECT items_json FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status != 'cancelled'";
    $res_top = $conn->query($sql_top_items);
    $item_sales = [];

    if ($res_top) {
        while ($row = $res_top->fetch_assoc()) {
            $items = json_decode($row['items_json'], true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    $name = $item['name'] ?? 'Unknown';
                    $qty = $item['quantity'] ?? 0;
                    if (!isset($item_sales[$name])) {
                        $item_sales[$name] = 0;
                    }
                    $item_sales[$name] += $qty;
                }
            }
        }
    }

    // Sort by quantity desc
    arsort($item_sales);
    // Take top 5
    $top_5 = array_slice($item_sales, 0, 5, true);
    
    $statistics['top_selling'] = [];
    foreach ($top_5 as $name => $qty) {
        $statistics['top_selling'][] = ['name' => $name, 'sold' => $qty];
    }

    $response['success'] = true;
    $response['statistics'] = $statistics;
    $response['message'] = 'Statistics fetched successfully.';

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>