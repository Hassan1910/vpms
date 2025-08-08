<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Get filter parameters from both POST and GET
$fromdate = isset($_POST['fromdate']) ? $_POST['fromdate'] : (isset($_GET['fromdate']) ? $_GET['fromdate'] : '');
$todate = isset($_POST['todate']) ? $_POST['todate'] : (isset($_GET['todate']) ? $_GET['todate'] : '');
$status_filter = isset($_POST['status']) ? $_POST['status'] : (isset($_GET['status']) ? $_GET['status'] : '');
$payment_status_filter = isset($_POST['payment_status']) ? $_POST['payment_status'] : (isset($_GET['payment_status']) ? $_GET['payment_status'] : '');
$vehicle_category_filter = isset($_POST['vehicle_category']) ? $_POST['vehicle_category'] : (isset($_GET['vehicle_category']) ? $_GET['vehicle_category'] : '');

// Validate date formats
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// If no dates provided or invalid, redirect back
if (!$fromdate || !$todate || !validateDate($fromdate) || !validateDate($todate)) {
    header('location:bwdates-report-ds.php');
    exit();
}

// Validate filter values
$allowed_statuses = ['confirmed', 'cancelled', 'completed'];
$allowed_payment_statuses = ['paid', 'completed', 'pending', 'failed'];

if (!empty($status_filter) && !in_array($status_filter, $allowed_statuses)) {
    $status_filter = '';
}

if (!empty($payment_status_filter) && !in_array($payment_status_filter, $allowed_payment_statuses)) {
    $payment_status_filter = '';
}

// Fetch comprehensive report data
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
WHERE DATE(b.start_time) BETWEEN '$fromdate' AND '$todate'";

// Add additional filters
if (!empty($status_filter)) {
    $query .= " AND b.status = '" . mysqli_real_escape_string($con, $status_filter) . "'";
}
if (!empty($payment_status_filter)) {
    $query .= " AND p.status = '" . mysqli_real_escape_string($con, $payment_status_filter) . "'";
}
if (!empty($vehicle_category_filter)) {
    $query .= " AND v.VehicleCategory = '" . mysqli_real_escape_string($con, $vehicle_category_filter) . "'";
}

$query .= " ORDER BY b.start_time DESC";

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
?>

<!doctype html>
<html lang="">
<head>
    <title>Booking Reports - Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.bootstrap4.min.css">
    
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stats-card h5 { margin-bottom: 10px; font-weight: 600; }
        .stats-card .stat-value { font-size: 1.8rem; font-weight: bold; }
        .export-buttons { margin: 20px 0; }
        .report-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        @media print {
            .no-print { display: none !important; }
            .stats-card { background: #f8f9fa !important; color: #333 !important; }
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
                        <h1>Report Details</h1>
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="page-header float-right">
                    <div class="page-title">
                        <ol class="breadcrumb text-right">
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="bwdates-report-ds.php">Reports</a></li>
                            <li class="active">Report Details</li>
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
            
            <!-- Back Button -->
            <div class="mb-3 no-print">
                <a href="bwdates-report-ds.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>

            <!-- Report Header -->
            <div class="report-header">
                <div class="row">
                    <div class="col-md-8">
                        <h2><i class="fas fa-file-alt"></i> Detailed Parking Report</h2>
                        <p class="lead">Report Period: <?php echo date('F j, Y', strtotime($fromdate)); ?> - <?php echo date('F j, Y', strtotime($todate)); ?></p>
                        <?php if (!empty($status_filter) || !empty($payment_status_filter) || !empty($vehicle_category_filter)): ?>
                        <p class="text-muted">
                            <strong>Applied Filters:</strong>
                            <?php if (!empty($status_filter)): ?>
                                <span class="badge badge-primary">Status: <?php echo ucfirst($status_filter); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($payment_status_filter)): ?>
                                <span class="badge badge-success">Payment: <?php echo ucfirst($payment_status_filter); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($vehicle_category_filter)): ?>
                                <span class="badge badge-info">Vehicle: <?php echo htmlspecialchars($vehicle_category_filter); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                        <p><small>Generated on: <?php echo date('F j, Y \a\t g:i A'); ?> | Total Records: <?php echo count($reportData); ?></small></p>
                    </div>
                    <div class="col-md-4 text-right no-print">
                        <div class="export-buttons">
                            <button onclick="window.print()" class="btn btn-success">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <?php
                            $export_params = "fromdate=" . urlencode($fromdate) . "&todate=" . urlencode($todate);
                            if (!empty($status_filter)) {
                                $export_params .= "&status=" . urlencode($status_filter);
                            }
                            if (!empty($payment_status_filter)) {
                                $export_params .= "&payment_status=" . urlencode($payment_status_filter);
                            }
                            if (!empty($vehicle_category_filter)) {
                                $export_params .= "&vehicle_category=" . urlencode($vehicle_category_filter);
                            }
                            ?>
                            <a href="bwdates-report-ds.php?export=pdf&<?php echo $export_params; ?>"
                               class="btn btn-danger" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="bwdates-report-ds.php?export=excel&<?php echo $export_params; ?>"
                               class="btn btn-info" target="_blank">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-car"></i> Total Bookings</h5>
                        <div class="stat-value"><?php echo $totalBookings; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-money-bill-wave"></i> Total Revenue</h5>
                        <div class="stat-value">KES <?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-check-circle"></i> Paid Bookings</h5>
                        <div class="stat-value"><?php echo $totalPaidBookings; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-clock"></i> Pending</h5>
                        <div class="stat-value"><?php echo $totalPendingBookings; ?></div>
                    </div>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-table"></i> Detailed Booking Records</h4>
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
});
</script>

</body>
</html>
