<?php
// Excel Export functionality for parking reports

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

// Set headers for Excel download
$filename = 'Parking_Report_' . $fromdate . '_to_' . $todate . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');

// Create file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add BOM to handle UTF-8 encoding properly in Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add report header information
fputcsv($output, array('VEHICLE PARKING MANAGEMENT SYSTEM - COMPREHENSIVE REPORT'));
fputcsv($output, array(''));
fputcsv($output, array('Report Period:', date('F j, Y', strtotime($fromdate)) . ' - ' . date('F j, Y', strtotime($todate))));
fputcsv($output, array('Generated on:', date('F j, Y \a\t g:i A')));
fputcsv($output, array(''));

// Add summary statistics
fputcsv($output, array('SUMMARY STATISTICS'));
fputcsv($output, array('Metric', 'Value'));
fputcsv($output, array('Total Bookings', $totalBookings));
fputcsv($output, array('Total Revenue (KES)', number_format($totalRevenue, 2)));
fputcsv($output, array('Paid Bookings', $totalPaidBookings));
fputcsv($output, array('Pending Payments', $totalPendingBookings));
fputcsv($output, array('Average Booking Value (KES)', number_format($averageBookingValue, 2)));
fputcsv($output, array('Payment Success Rate (%)', $successRate));
fputcsv($output, array('Report Period (Days)', abs(strtotime($todate) - strtotime($fromdate)) / (60*60*24) + 1));
fputcsv($output, array(''));

// Add detailed data header
fputcsv($output, array('DETAILED BOOKING RECORDS'));
fputcsv($output, array(
    'No.',
    'Booking ID',
    'Start Date',
    'Start Time',
    'End Date',
    'End Time',
    'Parking Space',
    'Price per Hour (KES)',
    'Vehicle Company',
    'Registration Number',
    'Vehicle Category',
    'Owner Name',
    'Mobile Number',
    'User First Name',
    'User Last Name',
    'User Email',
    'Duration (Hours)',
    'Payment Amount (KES)',
    'Calculated Amount (KES)',
    'Payment Status',
    'Payment Method',
    'Payment Date'
));

// Add detailed data
$cnt = 1;
foreach ($reportData as $row) {
    fputcsv($output, array(
        $cnt++,
        $row['booking_id'],
        date('Y-m-d', strtotime($row['start_time'])),
        date('H:i:s', strtotime($row['start_time'])),
        date('Y-m-d', strtotime($row['end_time'])),
        date('H:i:s', strtotime($row['end_time'])),
        $row['parking_number'],
        number_format($row['price_per_hour'], 2),
        $row['VehicleCompanyname'],
        $row['RegistrationNumber'],
        $row['VehicleCategory'],
        $row['OwnerName'],
        $row['MobileNumber'],
        $row['FirstName'],
        $row['LastName'],
        $row['Email'],
        $row['duration_hours'],
        number_format($row['payment_amount'] ? $row['payment_amount'] : 0, 2),
        number_format($row['calculated_amount'], 2),
        ucfirst($row['payment_status']),
        $row['payment_method'],
        $row['payment_date'] ? date('Y-m-d H:i:s', strtotime($row['payment_date'])) : ''
    ));
}

// Add footer
fputcsv($output, array(''));
fputcsv($output, array('Report generated by Vehicle Parking Management System'));
fputcsv($output, array('Generated at: ' . date('Y-m-d H:i:s')));

fclose($output);
exit();
?>
