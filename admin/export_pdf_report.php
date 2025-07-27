<?php
// PDF Export functionality for parking reports
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Get parameters
$fromdate = $_GET['fromdate'];
$todate = $_GET['todate'];

// Fetch report data
include('includes/dbconnection.php');

$reportData = [];
$totalRevenue = 0;
$totalBookings = 0;
$totalPaidBookings = 0;
$totalPendingBookings = 0;

$query = "SELECT 
    b.id as booking_id,
    b.parking_number,
    b.start_time,
    b.end_time,
    b.status,
    b.user_id,
    ps.price_per_hour,
    v.VehicleCompanyname,
    v.RegistrationNumber,
    v.VehicleCategory,
    v.OwnerName,
    v.MobileNumber,
    u.FirstName,
    u.LastName,
    u.Email,
    p.amount as payment_amount,
    p.status as payment_status,
    p.payment_date,
    p.payment_method,
    TIMESTAMPDIFF(HOUR, b.start_time, b.end_time) as duration_hours,
    (TIMESTAMPDIFF(HOUR, b.start_time, b.end_time) * ps.price_per_hour) as calculated_amount
FROM bookings b
LEFT JOIN parking_space ps ON b.parking_number = ps.parking_number
LEFT JOIN tblvehicle v ON b.vehicle_id = v.id
LEFT JOIN tblregusers u ON b.user_id = u.id
LEFT JOIN payment p ON b.id = p.booking_id
WHERE DATE(b.start_time) BETWEEN '$fromdate' AND '$todate'
ORDER BY b.start_time DESC";

$result = mysqli_query($con, $query);

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $reportData[] = $row;
        $totalBookings++;
        if ($row['payment_status'] == 'completed' || $row['payment_status'] == 'paid') {
            $totalPaidBookings++;
            $totalRevenue += $row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'];
        } else {
            $totalPendingBookings++;
        }
    }
}

$averageBookingValue = $totalPaidBookings > 0 ? $totalRevenue / $totalPaidBookings : 0;
$successRate = $totalBookings > 0 ? round(($totalPaidBookings / $totalBookings) * 100, 1) : 0;

// Create HTML content for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parking Management Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats-section {
            margin-bottom: 30px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .stat-item h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 14px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #3498db;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .report-table th,
        .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .report-table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }
        .report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
            font-size: 9px;
        }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; }
        .badge-primary { background-color: #007bff; }
        .badge-secondary { background-color: #6c757d; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Vehicle Parking Management System</h1>
        <h2>Comprehensive Parking Report</h2>
        <p><strong>Report Period:</strong> ' . date('F j, Y', strtotime($fromdate)) . ' - ' . date('F j, Y', strtotime($todate)) . '</p>
        <p><strong>Generated on:</strong> ' . date('F j, Y \a\t g:i A') . '</p>
    </div>

    <div class="stats-section">
        <h3>Summary Statistics</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <h4>Total Bookings</h4>
                <div class="stat-value">' . $totalBookings . '</div>
            </div>
            <div class="stat-item">
                <h4>Total Revenue</h4>
                <div class="stat-value">KES ' . number_format($totalRevenue, 2) . '</div>
            </div>
            <div class="stat-item">
                <h4>Paid Bookings</h4>
                <div class="stat-value">' . $totalPaidBookings . '</div>
            </div>
            <div class="stat-item">
                <h4>Pending Payments</h4>
                <div class="stat-value">' . $totalPendingBookings . '</div>
            </div>
        </div>
        
        <div class="stats-grid" style="margin-top: 10px;">
            <div class="stat-item">
                <h4>Average Booking Value</h4>
                <div class="stat-value">KES ' . number_format($averageBookingValue, 2) . '</div>
            </div>
            <div class="stat-item">
                <h4>Payment Success Rate</h4>
                <div class="stat-value">' . $successRate . '%</div>
            </div>
            <div class="stat-item">
                <h4>Report Period</h4>
                <div class="stat-value">' . (abs(strtotime($todate) - strtotime($fromdate)) / (60*60*24) + 1) . ' Days</div>
            </div>
            <div class="stat-item">
                <h4>Total Records</h4>
                <div class="stat-value">' . count($reportData) . '</div>
            </div>
        </div>
    </div>

    <h3>Detailed Booking Records</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Booking ID</th>
                <th>Date & Time</th>
                <th>Parking Space</th>
                <th>Vehicle</th>
                <th>Owner</th>
                <th>Duration</th>
                <th>Amount</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>';

$cnt = 1;
foreach ($reportData as $row) {
    $status = $row['payment_status'];
    $badgeClass = 'secondary';
    if ($status == 'completed' || $status == 'paid') {
        $badgeClass = 'success';
    } elseif ($status == 'pending') {
        $badgeClass = 'warning';
    } elseif ($status == 'failed') {
        $badgeClass = 'danger';
    }
    
    $html .= '
            <tr>
                <td>' . $cnt++ . '</td>
                <td>' . htmlspecialchars($row['booking_id']) . '</td>
                <td>
                    <strong>Start:</strong> ' . date('M j, Y g:i A', strtotime($row['start_time'])) . '<br>
                    <strong>End:</strong> ' . date('M j, Y g:i A', strtotime($row['end_time'])) . '
                </td>
                <td>
                    Space ' . htmlspecialchars($row['parking_number']) . '<br>
                    KES ' . number_format($row['price_per_hour'], 2) . '/hr
                </td>
                <td>
                    <strong>' . htmlspecialchars($row['VehicleCompanyname']) . '</strong><br>
                    ' . htmlspecialchars($row['RegistrationNumber']) . '<br>
                    ' . htmlspecialchars($row['VehicleCategory']) . '
                </td>
                <td>
                    <strong>' . htmlspecialchars($row['OwnerName']) . '</strong><br>
                    ' . htmlspecialchars($row['MobileNumber']) . '
                </td>
                <td>' . $row['duration_hours'] . ' hrs</td>
                <td>KES ' . number_format($row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'], 2) . '</td>
                <td><span class="badge badge-' . $badgeClass . '">' . ucfirst($status) . '</span></td>
            </tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the Vehicle Parking Management System.</p>
        <p>For any questions or concerns, please contact the system administrator.</p>
    </div>
</body>
</html>';

// Configure Dompdf options
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

// Create Dompdf instance
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Output the generated PDF
$filename = 'Parking_Report_' . $fromdate . '_to_' . $todate . '.pdf';
$dompdf->stream($filename, array('Attachment' => 1));
?>
