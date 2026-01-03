<?php
// generate_report_pdf.php
// Generates a printable HTML view for Save-As-PDF

header("Access-Control-Allow-Origin: *");

// Date Params
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Database Connection
require_once __DIR__ . '/db_config.php';
$conn = getDbConnection();

// Fetch Data
$sql = "SELECT 
            o.id, 
            o.order_date, 
            u.full_name, 
            o.total_amount, 
            o.status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'
          AND o.status != 'cancelled'
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);

$total_sales = 0;
$count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report (<?php echo "$start_date to $end_date"; ?>)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8 font-sans text-gray-800">

    <div class="max-w-4xl mx-auto bg-white p-10 rounded shadow-sm">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 border-b pb-4">
            <div>
                <h1 class="text-3xl font-bold text-amber-500">AYAM KINGS</h1>
                <p class="text-gray-500 text-sm">Delicious Chicken, Premium Experience</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold">Sales Report</h2>
                <p class="text-sm text-gray-500">Date Range:</p>
                <p class="font-medium"><?php echo $start_date; ?> to <?php echo $end_date; ?></p>
            </div>
        </div>

        <!-- Summary -->
        <div class="mb-8">
            <h3 class="text-lg font-bold mb-4">Summary</h3>
            <table class="w-full text-left border-collapse">
                <tbody>
                    <!-- Table Body populated below -->
                </tbody>
            </table>
        </div>

        <!-- Table -->
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <th class="p-3 border-b">Order ID</th>
                    <th class="p-3 border-b">Date</th>
                    <th class="p-3 border-b">Customer</th>
                    <th class="p-3 border-b">Status</th>
                    <th class="p-3 border-b text-right">Amount (RM)</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $total_sales += $row['total_amount'];
                        $count++;
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">#<?php echo $row['id']; ?></td>
                        <td class="p-3"><?php echo date('d M Y, H:i', strtotime($row['order_date'])); ?></td>
                        <td class="p-3"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td class="p-3 capitalize"><?php echo $row['status']; ?></td>
                        <td class="p-3 text-right font-medium"><?php echo number_format($row['total_amount'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="p-4 text-center text-gray-500">No records found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-amber-50 font-bold text-amber-900 border-t-2 border-amber-200">
                    <td colspan="4" class="p-3 text-right">Total Sales:</td>
                    <td class="p-3 text-right">RM <?php echo number_format($total_sales, 2); ?></td>
                </tr>
                <tr class="text-gray-500 text-xs">
                    <td colspan="5" class="p-3 text-right">Total Orders: <?php echo $count; ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="mt-12 text-center text-xs text-gray-400 border-t pt-4">
            <p>Generated on <?php echo date('d M Y H:i:s'); ?> | Ayam Kings Admin Dashboard</p>
        </div>
    </div>

    <!-- Print Control -->
    <div class="fixed bottom-8 right-8 no-print flex gap-2">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-3 rounded-full shadow-lg hover:bg-blue-700 font-bold flex items-center gap-2">
             <span>🖨️ Print / Save PDF</span>
        </button>
        <button onclick="window.close()" class="bg-gray-500 text-white px-6 py-3 rounded-full shadow-lg hover:bg-gray-600 font-bold">
             Close
        </button>
    </div>

    <script>
        // Auto print on load
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500); // Slight delay to ensure styles load
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
