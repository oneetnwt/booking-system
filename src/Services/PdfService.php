<?php

namespace App\Services;

use App\Config\Database;

class PdfService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function generateSalesReport($start_date = null, $end_date = null)
    {
        $start_date = $start_date ?? date('Y-m-01');
        $end_date = $end_date ?? date('Y-m-d');

        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(p.amount) as total_revenue,
                AVG(p.amount) as avg_booking_value
            FROM booking b
            JOIN payment p ON b.payment_id = p.id
            WHERE p.created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $prev_start = date('Y-m-d', strtotime($start_date . ' -1 month'));
        $prev_end = date('Y-m-d', strtotime($end_date . ' -1 month'));

        $stmt = $this->pdo->prepare("
    SELECT 
        COUNT(*) as prev_bookings,
        SUM(p.amount) as prev_revenue,
        AVG(p.amount) as prev_avg_value
    FROM booking b
    JOIN payment p ON b.payment_id = p.id
    WHERE p.created_at BETWEEN ? AND ?
");
$stmt->execute([$prev_start, $prev_end]);
$prev_summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate percentage changes
$booking_change = $prev_summary['prev_bookings'] ?
    (($summary['total_bookings'] - $prev_summary['prev_bookings']) / $prev_summary['prev_bookings']) * 100 : 0;
$revenue_change = $prev_summary['prev_revenue'] ?
    (($summary['total_revenue'] - $prev_summary['prev_revenue']) / $prev_summary['prev_revenue']) * 100 : 0;
$avg_value_change = $prev_summary['prev_avg_value'] ?
    (($summary['avg_booking_value'] - $prev_summary['prev_avg_value']) / $prev_summary['prev_avg_value']) * 100 : 0;

// Get room type analysis
$stmt = $this->pdo->prepare("
    SELECT 
        r.room_name,
        COUNT(*) as booking_count,
        SUM(p.amount) as total_revenue,
        AVG(p.amount) as avg_value
    FROM booking b
    JOIN room r ON b.room_id = r.id
    JOIN payment p ON b.payment_id = p.id
    WHERE p.created_at BETWEEN ? AND ?
    GROUP BY r.id, r.room_name
    ORDER BY total_revenue DESC
");
$stmt->execute([$start_date, $end_date]);
$room_analysis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment method distribution
$stmt = $this->pdo->prepare("
    SELECT 
        p.payment_method,
        COUNT(*) as method_count,
        SUM(p.amount) as method_revenue
    FROM booking b
    JOIN payment p ON b.payment_id = p.id
    WHERE p.created_at BETWEEN ? AND ?
    GROUP BY p.payment_method
");
$stmt->execute([$start_date, $end_date]);
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily revenue for the last 7 days
$stmt = $this->pdo->prepare("
    SELECT 
        DATE(p.created_at) as date,
        SUM(p.amount) as daily_revenue
    FROM booking b
    JOIN payment p ON b.payment_id = p.id
    WHERE p.created_at BETWEEN DATE_SUB(?, INTERVAL 6 DAY) AND ?
    GROUP BY DATE(p.created_at)
    ORDER BY date ASC
");
$stmt->execute([$end_date, $end_date]);
$daily_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate max revenue for chart scaling
$max_revenue = 0;
foreach ($daily_revenue as $day) {
    $max_revenue = max($max_revenue, $day['daily_revenue']);
}
// Add 20% padding to max revenue for better visualization
$max_revenue = $max_revenue > 0 ? $max_revenue * 1.2 : 1000; // Ensure a minimum max_revenue if all values are 0

// Generate HTML content
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 10,
    'margin_footer' => 10
]);

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking System Sales Report</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: white;
            font-size: 10pt;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }

        .report-header {
            text-align: center;
            padding: 5px 0;
            border-bottom: 2px solid #0066cc;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 5px;
        }

        .logo {
            display: block;
            max-width: 50px;
            height: auto;
            margin-right: 5px
        }

        .logo1 {
            display: block;
            max-width: 215px;
            height: auto;
            margin-left: 5px
        }

        .resort-name {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 5px;
            color: #333;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }

        .report-date {
            font-size: 12px;
            color: #666;
        }

        /* Updated Summary Section CSS */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .summary-table td {
            padding: 10px;
            text-align: center;
            background-color: #f5f9ff;
            border-radius: 5px;
            /* Removed border */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .summary-table tr {
            /* Removed border */
        }

        .summary-table td:not(:last-child) {
            margin-right: 10px; /* Add spacing between columns */
        }

        .summary-title {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #0066cc;
        }

        .summary-change {
            font-size: 10px;
            margin-top: 3px;
        }

        .positive-change {
            color: #28a745;
        }

        .negative-change {
            color: #dc3545;
        }

        .chart-section {
            margin: 15px 0;
        }

        .chart-container {
            width: 95%;
            height: 180px;
            background-color: #f5f9ff;
            border-radius: 5px;
            padding: 15px 5px;
            text-align: left;
            position: relative;
            margin: 0 auto;
            overflow: hidden;
        }

        .bar-container {
            position: absolute;
            bottom: 30px;
            width: 85%;
            left: 50px;
            height: 150px;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
        }

        .bar {
            width: 25px;
            background-color: #0066cc;
            border-radius: 3px 3px 0 0;
            margin: 0 2px;
        }

        .bar-label {
            position: absolute;
            bottom: 10px;
            width: 40px;
            text-align: center;
            font-size: 8pt;
            transform: translateX(-50%);
        }

        .chart-y-axis {
            position: absolute;
            left: 40px;
            height: 150px;
            border-left: 1px dashed #ccc;
            bottom: 30px;
        }

        .chart-x-axis {
            position: absolute;
            left: 40px;
            right: 5px;
            bottom: 30px;
            border-bottom: 1px solid #ccc;
        }

        .y-axis-label {
            position: absolute;
            left: 5px;
            font-size: 8pt;
            color: #666;
            text-align: right;
            width: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }

        th {
            background-color: #f0f6ff;
            padding: 8px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #0066cc;
        }

        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            font-size: 9pt;
            color: #666;
        }

        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="report-header">
            <div class="logo-container">
                <img src="../assets/K&ALogo.png" alt="K&A Resort Booking System Logo" class="logo">
                <img src="../assets/K&A_Dark.png" alt="K&A Dark Logo" class="logo1">
            </div>
            <div class="resort-name">K&A Natural Spring Resort</div>
            <div class="report-title">Booking System Sales Report</div>
            <div class="report-date">' . date('F d', strtotime($start_date)) . ' - ' . date('F d, Y', strtotime($end_date)) . '</div>
        </div>

        <!-- Updated Summary Section HTML -->
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-title">TOTAL BOOKINGS</div>
                    <div class="summary-value">' . number_format($summary['total_bookings']) . '</div>
                    <div class="summary-change ' . ($booking_change >= 0 ? 'positive-change' : 'negative-change') . '">
                        ' . ($booking_change >= 0 ? '↑' : '↓') . ' ' . abs(round($booking_change, 1)) . '% vs. Last Month
                    </div>
                </td>
                <td>
                    <div class="summary-title">TOTAL REVENUE</div>
                    <div class="summary-value">₱' . number_format($summary['total_revenue'], 2) . '</div>
                    <div class="summary-change ' . ($revenue_change >= 0 ? 'positive-change' : 'negative-change') . '">
                        ' . ($revenue_change >= 0 ? '↑' : '↓') . ' ' . abs(round($revenue_change, 1)) . '% vs. Last Month
                    </div>
                </td>
                <td>
                    <div class="summary-title">AVG. BOOKING VALUE</div>
                    <div class="summary-value">₱' . number_format($summary['avg_booking_value'], 2) . '</div>
                    <div class="summary-change ' . ($avg_value_change >= 0 ? 'positive-change' : 'negative-change') . '">
                        ' . ($avg_value_change >= 0 ? '↑' : '↓') . ' ' . abs(round($avg_value_change, 1)) . '% vs. Last Month
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-title">Room Type Analysis</div>
        <table>
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th>Total Bookings</th>
                    <th>Revenue</th>
                    <th>Avg. Value</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>';

foreach ($room_analysis as $room) {
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($room['room_name']) . '</td>
                    <td>' . number_format($room['booking_count']) . '</td>
                    <td>₱' . number_format($room['total_revenue'], 2) . '</td>
                    <td>₱' . number_format($room['avg_value'], 2) . '</td>
                    <td>' . round(($room['total_revenue'] / $summary['total_revenue']) * 100, 1) . '%</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>

        <div class="section-title">Payment Method Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Number of Transactions</th>
                    <th>Total Revenue</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>';

foreach ($payment_methods as $method) {
    $html .= '
                <tr>
                    <td>' . ucfirst($method['payment_method']) . '</td>
                    <td>' . number_format($method['method_count']) . '</td>
                    <td>₱' . number_format($method['method_revenue'], 2) . '</td>
                    <td>' . round(($method['method_revenue'] / $summary['total_revenue']) * 100, 1) . '%</td>
                </tr>';
}

        $html .= '
            </tbody>
        </table>

        <div class="footer">
            <p>Report generated on ' . date('F d, Y') . ' | K&A Resort Booking System</p>
            <p>This report is confidential and intended only for authorized personnel.</p>
        </div>
    </div>
</body>
</html>';

        $mpdf->WriteHTML($html);
        $mpdf->Output('booking_sales.pdf', 'D');
    }
}
