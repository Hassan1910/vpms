<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Debug mode - add ?debug=1 to URL to see debug info
if (isset($_GET['debug'])) {
    echo "<h3>Debug Information</h3>";
    echo "<p>GET parameters: " . print_r($_GET, true) . "</p>";
    echo "<p>Session ID: " . $_SESSION['vpmsaid'] . "</p>";
    echo "<p>Current file: " . __FILE__ . "</p>";
    echo "<p>Export files exist: PDF=" . (file_exists('export_pdf_report.php') ? 'Yes' : 'No') . 
         ", Excel=" . (file_exists('export_excel_report.php') ? 'Yes' : 'No') . "</p>";
    echo "<p>Vendor autoload exists: " . (file_exists('../vendor/autoload.php') ? 'Yes' : 'No') . "</p>";
    if (isset($_GET['export'])) {
        echo "<p style='color: red;'>EXPORT REQUEST DETECTED: " . $_GET['export'] . "</p>";
    }
    echo "<p>Test exports: ";
    echo "<a href='?export=excel&debug=1' style='color: blue;'>Test Excel</a> | ";
    echo "<a href='?export=pdf&debug=1' style='color: red;'>Test PDF</a>";
    echo "</p>";
    echo "<hr>";
}

// Handle export requests BEFORE any HTML output
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    $fromdate = $_GET['fromdate'] ?? '';
    $todate = $_GET['todate'] ?? '';
    
    // Get the same data that the main report uses
    $date_range_query = mysqli_query($con, "SELECT MIN(DATE(start_time)) as min_date, MAX(DATE(start_time)) as max_date FROM bookings");
    $date_range = mysqli_fetch_array($date_range_query);
    
    $export_fromdate = $date_range['min_date'] ? $date_range['min_date'] : date('Y-m-d');
    $export_todate = $date_range['max_date'] ? $date_range['max_date'] : date('Y-m-d');
    
    // Fetch the same data as the main report
    $exportQuery = "SELECT 
        b.id as booking_id,
        b.parking_number,
        b.start_time,
        b.end_time,
        b.status,
        b.user_id,
        COALESCE(ps.price_per_hour, 100) as price_per_hour,
        COALESCE(v.VehicleCompanyname, 'N/A') as VehicleCompanyname,
        COALESCE(v.RegistrationNumber, 'N/A') as RegistrationNumber,
        COALESCE(v.VehicleCategory, 'N/A') as VehicleCategory,
        COALESCE(v.OwnerName, '') as OwnerName,
        COALESCE(v.OwnerContactNumber, '') as MobileNumber,
        COALESCE(u.FirstName, 'N/A') as FirstName,
        COALESCE(u.LastName, 'N/A') as LastName,
        COALESCE(u.Email, 'N/A') as Email,
        COALESCE(p.amount, 0) as payment_amount,
        COALESCE(p.status, 'pending') as payment_status,
        p.created_at as payment_date,
        COALESCE('M-Pesa', 'N/A') as payment_method,
        COALESCE(TIMESTAMPDIFF(HOUR, b.start_time, b.end_time), 1) as duration_hours,
        COALESCE((TIMESTAMPDIFF(HOUR, b.start_time, b.end_time) * ps.price_per_hour), 100) as calculated_amount
    FROM bookings b
    LEFT JOIN parking_space ps ON b.parking_number = ps.parking_number
    LEFT JOIN tblvehicle v ON b.vehicle_id = v.id
    LEFT JOIN tblregusers u ON b.user_id = u.id
    LEFT JOIN payment p ON b.id = p.booking_id
    WHERE DATE(b.start_time) BETWEEN '$export_fromdate' AND '$export_todate'
    ORDER BY b.start_time DESC";
    
    $exportResult = mysqli_query($con, $exportQuery);
    $exportData = [];
    $exportTotalRevenue = 0;
    $exportTotalBookings = 0;
    $exportTotalPaidBookings = 0;
    
    if ($exportResult) {
        while ($row = mysqli_fetch_array($exportResult)) {
            $exportData[] = $row;
            $exportTotalBookings++;
            if ($row['payment_status'] == 'completed' || $row['payment_status'] == 'paid') {
                $exportTotalPaidBookings++;
                $exportTotalRevenue += $row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'];
            }
        }
    }
    
    if ($exportType == 'pdf') {
        // Create simple PDF export
        createPDFExport($exportData, $export_fromdate, $export_todate, $exportTotalRevenue, $exportTotalBookings);
        exit();
    } elseif ($exportType == 'excel') {
        // Create simple Excel/CSV export
        createExcelExport($exportData, $export_fromdate, $export_todate, $exportTotalRevenue, $exportTotalBookings);
        exit();
    }
}

// Function to create PDF export
function createPDFExport($data, $fromdate, $todate, $totalRevenue, $totalBookings) {
    try {
        // Check if DomPDF is available
        if (file_exists('../vendor/autoload.php')) {
            require_once '../vendor/autoload.php';
            
            ob_start();
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Parking Report</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 10px; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .stats { background: #f8f9fa; padding: 10px; margin: 15px 0; border: 1px solid #ddd; }
                    h1 { font-size: 18px; margin: 5px 0; }
                    h2 { font-size: 14px; margin: 5px 0; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Vehicle Parking Management System</h1>
                    <h2>Complete Analytics Report</h2>
                    <p>Period: <?php echo date('F j, Y', strtotime($fromdate)); ?> - <?php echo date('F j, Y', strtotime($todate)); ?></p>
                    <p>Generated on: <?php echo date('F j, Y \a\t g:i A'); ?></p>
                </div>
                
                <div class="stats">
                    <strong>Report Summary:</strong><br>
                    Total Bookings: <?php echo $totalBookings; ?><br>
                    Total Revenue: KES <?php echo number_format($totalRevenue, 2); ?><br>
                    Report Period: <?php echo abs(strtotime($todate) - strtotime($fromdate)) / (60*60*24) + 1; ?> Days
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 10%;">Booking ID</th>
                            <th style="width: 12%;">Date</th>
                            <th style="width: 10%;">Space</th>
                            <th style="width: 20%;">Vehicle</th>
                            <th style="width: 15%;">Owner</th>
                            <th style="width: 8%;">Duration</th>
                            <th style="width: 10%;">Amount</th>
                            <th style="width: 10%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $cnt = 1;
                        foreach ($data as $row): 
                        ?>
                        <tr>
                            <td><?php echo $cnt++; ?></td>
                            <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['start_time'])); ?></td>
                            <td>Space <?php echo htmlspecialchars($row['parking_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['VehicleCompanyname'] . ' ' . $row['RegistrationNumber']); ?></td>
                            <td><?php echo htmlspecialchars($row['OwnerName']); ?></td>
                            <td><?php echo $row['duration_hours']; ?> hrs</td>
                            <td>KES <?php echo number_format($row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'], 2); ?></td>
                            <td><?php echo ucfirst($row['payment_status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </body>
            </html>
            <?php
            $html = ob_get_clean();
            
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'Arial');
            $options->set('isRemoteEnabled', true);
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="parking_report_' . date('Y-m-d') . '.pdf"');
            echo $dompdf->output();
            
        } else {
            // Fallback to HTML download if DomPDF not available
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="parking_report_' . date('Y-m-d') . '.html"');
            
            echo "<h1>Vehicle Parking Management System - Complete Report</h1>";
            echo "<p>DomPDF library not found. Here's your report in HTML format:</p>";
            echo "<p>Period: " . date('F j, Y', strtotime($fromdate)) . " - " . date('F j, Y', strtotime($todate)) . "</p>";
            echo "<p>Total Bookings: $totalBookings | Total Revenue: KES " . number_format($totalRevenue, 2) . "</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>#</th><th>Booking ID</th><th>Date</th><th>Vehicle</th><th>Owner</th><th>Amount</th><th>Status</th></tr>";
            
            $cnt = 1;
            foreach ($data as $row) {
                echo "<tr>";
                echo "<td>" . $cnt++ . "</td>";
                echo "<td>" . htmlspecialchars($row['booking_id']) . "</td>";
                echo "<td>" . date('M j, Y', strtotime($row['start_time'])) . "</td>";
                echo "<td>" . htmlspecialchars($row['VehicleCompanyname'] . ' ' . $row['RegistrationNumber']) . "</td>";
                echo "<td>" . htmlspecialchars($row['OwnerName']) . "</td>";
                echo "<td>KES " . number_format($row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'], 2) . "</td>";
                echo "<td>" . ucfirst($row['payment_status']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="error_' . date('Y-m-d') . '.txt"');
        echo "Error generating PDF: " . $e->getMessage();
        echo "\nPlease contact system administrator.";
    }
}

// Function to create Excel/CSV export
function createExcelExport($data, $fromdate, $todate, $totalRevenue, $totalBookings) {
    try {
        $filename = "parking_report_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel to recognize UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add report header
        fputcsv($output, ['Vehicle Parking Management System - Complete Report']);
        fputcsv($output, ['Period: ' . date('F j, Y', strtotime($fromdate)) . ' - ' . date('F j, Y', strtotime($todate))]);
        fputcsv($output, ['Generated: ' . date('F j, Y \a\t g:i A')]);
        fputcsv($output, ['Total Bookings: ' . $totalBookings]);
        fputcsv($output, ['Total Revenue: KES ' . number_format($totalRevenue, 2)]);
        fputcsv($output, []); // Empty row
        
        // Add headers
        fputcsv($output, [
            '#',
            'Booking ID',
            'Start Date & Time',
            'End Date & Time',
            'Parking Space',
            'Vehicle Company',
            'Registration Number',
            'Vehicle Category',
            'Owner Name',
            'Contact Number',
            'Duration (Hours)',
            'Amount (KES)',
            'Payment Status',
            'Payment Method'
        ]);
        
        // Add data
        $cnt = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $cnt++,
                $row['booking_id'],
                date('M j, Y g:i A', strtotime($row['start_time'])),
                date('M j, Y g:i A', strtotime($row['end_time'])),
                'Space ' . $row['parking_number'],
                $row['VehicleCompanyname'],
                $row['RegistrationNumber'],
                $row['VehicleCategory'],
                $row['OwnerName'],
                $row['MobileNumber'],
                $row['duration_hours'],
                number_format($row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'], 2),
                ucfirst($row['payment_status']),
                $row['payment_method']
            ]);
        }
        
        fclose($output);
    } catch (Exception $e) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="error_' . date('Y-m-d') . '.txt"');
        echo "Error generating Excel export: " . $e->getMessage();
        echo "\nPlease contact system administrator.";
    }
}

// Get ALL data from first booking to last booking automatically
$date_range_query = mysqli_query($con, "SELECT MIN(DATE(start_time)) as min_date, MAX(DATE(start_time)) as max_date FROM bookings");
$date_range = mysqli_fetch_array($date_range_query);

$fromdate = $date_range['min_date'] ? $date_range['min_date'] : date('Y-m-d');
$todate = $date_range['max_date'] ? $date_range['max_date'] : date('Y-m-d');

// Initialize variables
$reportData = [];
$totalRevenue = 0;
$totalBookings = 0;
$totalPaidBookings = 0;
$totalPendingBookings = 0;
$averageBookingValue = 0;

// Always generate report for ALL bookings
// Fetch comprehensive report data
    $query = "SELECT 
        b.id as booking_id,
        b.parking_number,
        b.start_time,
        b.end_time,
        b.status,
        b.user_id,
        COALESCE(ps.price_per_hour, 100) as price_per_hour,
        COALESCE(v.VehicleCompanyname, 'N/A') as VehicleCompanyname,
        COALESCE(v.RegistrationNumber, 'N/A') as RegistrationNumber,
        COALESCE(v.VehicleCategory, 'N/A') as VehicleCategory,
        COALESCE(v.OwnerName, '') as OwnerName,
        COALESCE(v.OwnerContactNumber, '') as MobileNumber,
        COALESCE(u.FirstName, 'N/A') as FirstName,
        COALESCE(u.LastName, 'N/A') as LastName,
        COALESCE(u.Email, 'N/A') as Email,
        COALESCE(p.amount, 0) as payment_amount,
        COALESCE(p.status, 'pending') as payment_status,
        p.created_at as payment_date,
        COALESCE('M-Pesa', 'N/A') as payment_method,
        COALESCE(TIMESTAMPDIFF(HOUR, b.start_time, b.end_time), 1) as duration_hours,
        COALESCE((TIMESTAMPDIFF(HOUR, b.start_time, b.end_time) * ps.price_per_hour), 100) as calculated_amount
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
        
        if ($totalPaidBookings > 0) {
            $averageBookingValue = $totalRevenue / $totalPaidBookings;
        } else {
            $averageBookingValue = 0;
        }
    }

// Include analytics functions if file exists
if (file_exists('includes/report_analytics.php')) {
    include('includes/report_analytics.php');
}
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - Complete Analytics Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pixeden-stroke-7-icon@1.2.3/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.bootstrap4.min.css">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>
    
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stats-card h4 {
            margin-bottom: 10px;
            font-weight: 600;
        }
        .stats-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .export-buttons {
            margin: 20px 0;
        }
        .report-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        @media print {
            /* Hide navigation and UI elements */
            .no-print { display: none !important; }
            #left-panel { display: none !important; }
            #right-panel { margin-left: 0 !important; }
            #header { display: none !important; }
            .breadcrumbs { display: none !important; }
            .export-buttons { display: none !important; }
            .site-footer { display: none !important; }
            footer { display: none !important; }
            
            /* Reset body and html margins */
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
            }
            
            /* Adjust main content for print */
            .content { 
                margin-left: 0 !important; 
                margin-top: 0 !important;
                padding: 15px !important;
                width: 100% !important;
            }
            
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            
            /* Style cards for print */
            .stats-card { 
                background: #f8f9fa !important; 
                color: #333 !important;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                margin-bottom: 10px !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                page-break-inside: avoid;
            }
            
            .card-header {
                background: #f8f9fa !important;
                color: #333 !important;
                border-bottom: 1px solid #ddd !important;
            }
            
            /* Ensure table prints properly */
            .table-responsive {
                overflow: visible !important;
                max-height: none !important;
            }
            
            .table {
                font-size: 10pt !important;
            }
            
            .table th,
            .table td {
                padding: 4px !important;
                border: 1px solid #ddd !important;
            }
            
            /* Page breaks */
            .page-break {
                page-break-after: always;
            }
            
            /* Font adjustments for print */
            body {
                font-size: 11pt !important;
                line-height: 1.3 !important;
                color: #000 !important;
            }
            
            h1, h2, h3, h4, h5, h6 {
                color: #000 !important;
                margin-top: 10px !important;
                margin-bottom: 8px !important;
            }
            
            /* Report header styling */
            .report-header {
                background: #fff !important;
                border: 1px solid #ddd !important;
                margin-bottom: 15px !important;
                page-break-inside: avoid;
            }
            
            /* Statistics row */
            .stats-card .stat-value {
                font-size: 18pt !important;
                font-weight: bold !important;
            }
        }
    </style>

</head>
<body>
<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="breadcrumbs no-print">
    <div class="breadcrumbs-inner">
        <div class="row m-0">
            <div class="col-sm-4">
                <div class="page-header float-left">
                    <div class="page-title">
                        <h1>Analytics Dashboard</h1>
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="page-header float-right">
                    <div class="page-title">
                        <ol class="breadcrumb text-right">
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li class="active">Complete Analytics</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="animated fadeIn">
        <div class="container-fluid">
            
            <!-- Comprehensive Report Info -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-chart-line"></i> Complete Parking Management Analytics</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><i class="fas fa-database"></i> All-Time Report</h5>
                            <p class="mb-2">This comprehensive report shows ALL parking data from your first booking to the latest.</p>
                            <p class="text-muted">
                                <strong>Data Range:</strong> <?php echo $fromdate ? date('F j, Y', strtotime($fromdate)) : 'No data'; ?> 
                                to <?php echo $todate ? date('F j, Y', strtotime($todate)) : 'No data'; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="mt-2">
                                <span class="badge badge-info p-2">
                                    <i class="fas fa-sync-alt"></i> Auto-Generated
                                </span>
                                <br><small class="text-muted">Last updated: <?php echo date('g:i A'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($reportData)): ?>
            
            <!-- Report Header -->
            <div class="report-header">
                <div class="row">
                    <div class="col-md-8">
                        <h2><i class="fas fa-file-alt"></i> Complete Parking Management Analytics</h2>
                        <p class="lead">All-Time Report: <?php echo $fromdate ? date('F j, Y', strtotime($fromdate)) : 'No data'; ?> - <?php echo $todate ? date('F j, Y', strtotime($todate)) : 'No data'; ?></p>
                        <p><small>Generated on: <?php echo date('F j, Y \a\t g:i A'); ?> | Total Records: <?php echo count($reportData); ?></small></p>
                    </div>
                    <div class="col-md-4 text-right no-print">
                        <div class="export-buttons">
                            <button onclick="window.print()" class="btn btn-success">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                            <button onclick="exportPDF()" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                            <button onclick="exportExcel()" class="btn btn-info">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><i class="fas fa-car"></i> Total Bookings</h4>
                        <div class="stat-value"><?php echo $totalBookings; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><i class="fas fa-money-bill-wave"></i> Total Revenue</h4>
                        <div class="stat-value">KES <?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><i class="fas fa-check-circle"></i> Paid Bookings</h4>
                        <div class="stat-value"><?php echo $totalPaidBookings; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><i class="fas fa-clock"></i> Pending Payments</h4>
                        <div class="stat-value"><?php echo $totalPendingBookings; ?></div>
                    </div>
                </div>
            </div>

            <!-- Additional Statistics Row -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>Average Booking Value</h5>
                            <h3 class="text-primary">KES <?php echo number_format($averageBookingValue, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>Payment Success Rate</h5>
                            <h3 class="text-success">
                                <?php echo $totalBookings > 0 ? round(($totalPaidBookings / $totalBookings) * 100, 1) : 0; ?>%
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>Report Period</h5>
                            <h3 class="text-info"><?php echo abs(strtotime($todate) - strtotime($fromdate)) / (60*60*24) + 1; ?> Days</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Report Table -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4><i class="fas fa-table"></i> Detailed Booking Report</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="reportTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Booking ID</th>
                                    <th>Date & Time</th>
                                    <th>Parking Space</th>
                                    <th>Vehicle Details</th>
                                    <th>Owner Details</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Payment Status</th>
                                    <th>Payment Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $cnt = 1;
                                foreach ($reportData as $row): 
                                ?>
                                <tr>
                                    <td><?php echo $cnt++; ?></td>
                                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                                    <td>
                                        <strong>Start:</strong> <?php echo date('M j, Y g:i A', strtotime($row['start_time'])); ?><br>
                                        <strong>End:</strong> <?php echo date('M j, Y g:i A', strtotime($row['end_time'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">Space <?php echo htmlspecialchars($row['parking_number']); ?></span><br>
                                        <small>KES <?php echo number_format($row['price_per_hour'], 2); ?>/hr</small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['VehicleCompanyname']); ?></strong><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($row['RegistrationNumber']); ?></span><br>
                                        <small class="badge badge-secondary"><?php echo htmlspecialchars($row['VehicleCategory']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['OwnerName']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['MobileNumber']); ?></small><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $row['duration_hours']; ?> hrs</span>
                                    </td>
                                    <td>
                                        <strong>KES <?php echo number_format($row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $row['payment_status'];
                                        $badgeClass = 'secondary';
                                        if ($status == 'completed' || $status == 'paid') {
                                            $badgeClass = 'success';
                                        } elseif ($status == 'pending') {
                                            $badgeClass = 'warning';
                                        } elseif ($status == 'failed') {
                                            $badgeClass = 'danger';
                                        }
                                        ?>
                                        <span class="badge badge-<?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $row['payment_method'] ? htmlspecialchars($row['payment_method']) : 'N/A'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Analytics Section -->
            <?php if (!empty($reportData)): 
                if (file_exists('includes/report_analytics.php')) {
                    include_once('includes/report_analytics.php');
                    $analytics = generateReportSummary($reportData, $fromdate, $todate);
                } else {
                    echo "<p class='text-danger'>Analytics file not found.</p>";
                    // Create basic analytics array as fallback
                    $analytics = [
                        'most_used_parking' => 'N/A',
                        'peak_booking_hour' => 'N/A',
                        'avg_duration' => 0,
                        'vehicle_categories' => [],
                        'payment_methods' => []
                    ];
                }
            ?>
            <!-- Simple Analytics Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> Analytics Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6><i class="fas fa-car"></i> Top Vehicle Category</h6>
                                        <?php 
                                        if (!empty($analytics['vehicle_categories'])) {
                                            $topCategory = array_keys($analytics['vehicle_categories'], max($analytics['vehicle_categories']))[0];
                                            $topCount = max($analytics['vehicle_categories']);
                                            echo "<h4 class='text-primary'>$topCategory</h4>";
                                            echo "<small class='text-muted'>$topCount bookings</small>";
                                        } else {
                                            echo "<h4 class='text-muted'>N/A</h4>";
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6><i class="fas fa-credit-card"></i> Payment Method</h6>
                                        <?php 
                                        if (!empty($analytics['payment_methods'])) {
                                            $topMethod = array_keys($analytics['payment_methods'], max($analytics['payment_methods']))[0];
                                            $methodCount = max($analytics['payment_methods']);
                                            echo "<h4 class='text-success'>$topMethod</h4>";
                                            echo "<small class='text-muted'>$methodCount payments</small>";
                                        } else {
                                            echo "<h4 class='text-muted'>N/A</h4>";
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6><i class="fas fa-calendar-day"></i> Busiest Day</h6>
                                        <?php 
                                        if (!empty($analytics['daily_breakdown'])) {
                                            // Find the day with most bookings
                                            $maxBookings = 0;
                                            $busiestDay = '';
                                            foreach ($analytics['daily_breakdown'] as $date => $data) {
                                                if ($data['bookings'] > $maxBookings) {
                                                    $maxBookings = $data['bookings'];
                                                    $busiestDay = $date;
                                                }
                                            }
                                            if ($busiestDay) {
                                                echo "<h4 class='text-info'>" . date('M j', strtotime($busiestDay)) . "</h4>";
                                                echo "<small class='text-muted'>$maxBookings bookings</small>";
                                            } else {
                                                echo "<h4 class='text-muted'>N/A</h4>";
                                            }
                                        } else {
                                            echo "<h4 class='text-muted'>N/A</h4>";
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6><i class="fas fa-percentage"></i> Success Rate</h6>
                                        <?php 
                                        $successRate = $totalBookings > 0 ? round(($totalPaidBookings / $totalBookings) * 100, 1) : 0;
                                        echo "<h4 class='text-warning'>$successRate%</h4>";
                                        echo "<small class='text-muted'>Payment success</small>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Insights -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Most Used Parking Space</h6>
                            <h3 class="text-primary">Space <?php echo $analytics['most_used_parking']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Peak Booking Hour</h6>
                            <h3 class="text-success"><?php echo $analytics['peak_booking_hour']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Average Duration</h6>
                            <h3 class="text-info"><?php echo round($analytics['avg_duration'], 1); ?> hrs</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Success Rate</h6>
                            <h3 class="text-warning"><?php echo $totalBookings > 0 ? round(($totalPaidBookings / $totalBookings) * 100, 1) : 0; ?>%</h3>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- No Data Available -->
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                    <h4>No Booking Data Available</h4>
                    <p>There are no parking bookings in the database yet.</p>
                    <p>Start by adding some bookings to see comprehensive analytics and reports here.</p>
                    <a href="add-vehicle.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Booking
                    </a>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include_once('includes/footer.php'); ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>

<!-- DataTables Scripts -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#reportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        pageLength: 25,
        responsive: true,
        order: [[1, 'desc']]
    });
    
    // Handle export links
    $('.export-link').click(function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        console.log('Exporting to:', url);
        window.open(url, '_blank');
        return false;
    });
});

// Alternative export functions
function exportPDF() {
    var url = 'bwdates-report-ds.php?export=pdf&fromdate=<?php echo $fromdate; ?>&todate=<?php echo $todate; ?>';
    console.log('PDF Export URL:', url);
    window.open(url, '_blank');
}

function exportExcel() {
    var url = 'bwdates-report-ds.php?export=excel&fromdate=<?php echo $fromdate; ?>&todate=<?php echo $todate; ?>';
    console.log('Excel Export URL:', url);
    window.open(url, '_blank');
}
</script>

</div> <!-- Close right-panel -->

</body>
</html>