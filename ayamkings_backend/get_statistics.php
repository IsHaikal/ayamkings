<?php
// get_statistics.php (NEW FILE: Fetches daily sales, monthly profit, expenses, and chart data)

require_once __DIR__ . '/cors.php'; // Handle CORS and Preflight

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Database connection (using centralized config)
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();

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

    // --- Chart Data (Dynamic: Daily vs Monthly) ---
    $start_dt = new DateTime($start_date);
    $end_dt = new DateTime($end_date);
    $interval = $start_dt->diff($end_dt);
    $days_diff = $interval->days;

    // Decide grouping: Daily if <= 31 days, Monthly otherwise
    $is_daily = $days_diff <= 31;

    if ($is_daily) {
        // --- DAILY GROUPING ---
        $sql_chart_sales = "SELECT DATE(order_date) as date, SUM(total_amount) as total_sales 
                            FROM orders 
                            WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' AND status != 'cancelled'
                            GROUP BY DATE(order_date) ORDER BY date ASC";
        
        $sql_chart_expenses = "SELECT DATE(expense_date) as date, SUM(amount) as total_expenses
                               FROM expenses
                               WHERE DATE(expense_date) BETWEEN '$start_date' AND '$end_date'
                               GROUP BY DATE(expense_date) ORDER BY date ASC";
        
        // Fetch Data
        $sales_data = [];
        $res = $conn->query($sql_chart_sales);
        if ($res) while ($row = $res->fetch_assoc()) $sales_data[$row['date']] = $row['total_sales'];

        $expenses_data = [];
        $res = $conn->query($sql_chart_expenses);
        if ($res) while ($row = $res->fetch_assoc()) $expenses_data[$row['date']] = $row['total_expenses'];

        // Fill missing days
        $period = new DatePeriod($start_dt, new DateInterval('P1D'), $end_dt->modify('+1 day'));
        foreach ($period as $dt) {
            $date_key = $dt->format('Y-m-d');
            $label = $dt->format('M d'); // e.g. Jan 01
            
            $statistics['chart_data']['labels'][] = $label;
            $statistics['chart_data']['sales'][] = $sales_data[$date_key] ?? 0.00;
            $statistics['chart_data']['expenses'][] = $expenses_data[$date_key] ?? 0.00;
        }

    } else {
        // --- MONTHLY GROUPING ---
        $sql_chart_sales = "SELECT YEAR(order_date) as year, MONTH(order_date) as month, SUM(total_amount) as total_sales
                            FROM orders
                            WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' AND status != 'cancelled'
                            GROUP BY year, month ORDER BY year ASC, month ASC";

        $sql_chart_expenses = "SELECT YEAR(expense_date) as year, MONTH(expense_date) as month, SUM(amount) as total_expenses
                               FROM expenses
                               WHERE DATE(expense_date) BETWEEN '$start_date' AND '$end_date'
                               GROUP BY year, month ORDER BY year ASC, month ASC";

        // Fetch Data
        $sales_data = [];
        $res = $conn->query($sql_chart_sales);
        if ($res) while ($row = $res->fetch_assoc()) $sales_data[sprintf('%04d-%02d', $row['year'], $row['month'])] = $row['total_sales'];

        $expenses_data = [];
        $res = $conn->query($sql_chart_expenses);
        if ($res) while ($row = $res->fetch_assoc()) $expenses_data[sprintf('%04d-%02d', $row['year'], $row['month'])] = $row['total_expenses'];

        // Fill missing months
        // Logic: Start from Month of start_date, go until Month of end_date
        $curr = clone $start_dt;
        // Reset to first day of month to avoid skipping issues
        $curr->modify('first day of this month');
        $end_limit = clone $end_dt;
        $end_limit->modify('first day of this month');

        while ($curr <= $end_limit) {
            $key = $curr->format('Y-m');
            $label = $curr->format('M Y'); // e.g. Jan 2024

            $statistics['chart_data']['labels'][] = $label;
            $statistics['chart_data']['sales'][] = $sales_data[$key] ?? 0.00;
            $statistics['chart_data']['expenses'][] = $expenses_data[$key] ?? 0.00;

            $curr->modify('+1 month');
        }
    }

    // --- Top Selling Items (Aggregation from JSON) ---
    // Fetch orders within the selected date range
    $sql_top_items = "SELECT items_json FROM orders WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' AND status != 'cancelled'";
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