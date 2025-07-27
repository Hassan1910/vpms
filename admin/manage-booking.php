<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Handle booking actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $booking_id = intval($_GET['id']);
    
    switch ($action) {
        case 'delete':
            // Get parking number before deleting booking
            $booking_query = mysqli_query($con, "SELECT parking_number FROM bookings WHERE id = '$booking_id'");
            $booking_data = mysqli_fetch_assoc($booking_query);
            
            if ($booking_data) {
                $parking_number = $booking_data['parking_number'];
                
                // Delete booking
                $delete_query = mysqli_query($con, "DELETE FROM bookings WHERE id = '$booking_id'");
                
                // Update parking space status to available
                mysqli_query($con, "UPDATE parking_space SET status = 'available' WHERE parking_number = '$parking_number'");
                
                if ($delete_query) {
                    echo "<script>alert('Booking deleted successfully');</script>";
                } else {
                    echo "<script>alert('Error deleting booking');</script>";
                }
            }
            break;
            
        case 'complete':
            $end_time = date('Y-m-d H:i:s');
            
            // Get parking number
            $booking_query = mysqli_query($con, "SELECT parking_number FROM bookings WHERE id = '$booking_id'");
            $booking_data = mysqli_fetch_assoc($booking_query);
            
            if ($booking_data) {
                $parking_number = $booking_data['parking_number'];
                
                // Update booking status
                $update_query = mysqli_query($con, "UPDATE bookings SET status = 'completed', end_time = '$end_time' WHERE id = '$booking_id'");
                
                // Update parking space status
                mysqli_query($con, "UPDATE parking_space SET status = 'available' WHERE parking_number = '$parking_number'");
                
                if ($update_query) {
                    echo "<script>alert('Booking completed successfully');</script>";
                } else {
                    echo "<script>alert('Error completing booking');</script>";
                }
            }
            break;
            
        case 'cancel':
            // Get parking number
            $booking_query = mysqli_query($con, "SELECT parking_number FROM bookings WHERE id = '$booking_id'");
            $booking_data = mysqli_fetch_assoc($booking_query);
            
            if ($booking_data) {
                $parking_number = $booking_data['parking_number'];
                
                // Update booking status
                $update_query = mysqli_query($con, "UPDATE bookings SET status = 'cancelled' WHERE id = '$booking_id'");
                
                // Update parking space status
                mysqli_query($con, "UPDATE parking_space SET status = 'available' WHERE parking_number = '$parking_number'");
                
                if ($update_query) {
                    echo "<script>alert('Booking cancelled successfully');</script>";
                } else {
                    echo "<script>alert('Error cancelling booking');</script>";
                }
            }
            break;
    }
    
    echo "<script>window.location.href='manage-booking.php'</script>";
}

// Fetch all bookings with user, vehicle, and payment details
$query = mysqli_query($con, "
    SELECT 
        b.id,
        b.parking_number,
        b.start_time,
        b.end_time,
        b.status,
        b.created_at,
        u.FirstName,
        u.LastName,
        u.MobileNumber,
        u.Email,
        v.RegistrationNumber,
        v.VehicleCategory,
        v.VehicleCompanyname,
        ps.price_per_hour,
        p.amount as paid_amount,
        p.status as payment_status
    FROM bookings b
    LEFT JOIN tblregusers u ON b.user_id = u.ID
    LEFT JOIN tblvehicle v ON b.vehicle_id = v.ID
    LEFT JOIN parking_space ps ON b.parking_number = ps.parking_number
    LEFT JOIN payment p ON b.id = p.booking_id
    ORDER BY b.created_at DESC
");

if (!$query) {
    die("Query failed: " . mysqli_error($con));
}
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - Manage Bookings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pixeden-stroke-7-icon@1.2.3/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>
    
    <style>
        .status-active { color: #28a745; font-weight: bold; }
        .status-completed { color: #6c757d; }
        .status-cancelled { color: #dc3545; }
        .action-btn { margin: 2px; }
        
        /* Table alignment improvements */
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        
        .table th:first-child, .table td:first-child {
            width: 60px;
        }
        
        .table th:nth-child(2), .table td:nth-child(2) {
            width: 100px;
        }
        
        .table th:nth-child(3), .table td:nth-child(3),
        .table th:nth-child(4), .table td:nth-child(4) {
            width: 180px;
            text-align: left;
        }
        
        .table th:nth-child(5), .table td:nth-child(5) {
            width: 140px;
            text-align: left;
        }
        
        .table th:nth-child(6), .table td:nth-child(6) {
            width: 160px;
            text-align: left;
        }
        
        .table th:nth-child(7), .table td:nth-child(7) {
            width: 100px;
        }
        
        .table th:nth-child(8), .table td:nth-child(8) {
            width: 100px;
        }
        
        .table th:nth-child(9), .table td:nth-child(9) {
            width: 200px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        @media (max-width: 768px) {
            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.5rem 0.25rem;
            }
            
            .action-btn {
                margin: 1px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <!-- Left Panel -->
    <?php include_once('includes/sidebar.php'); ?>
    
    <!-- Right Panel -->
    <?php include_once('includes/header.php'); ?>

    <div class="breadcrumbs">
        <div class="breadcrumbs-inner">
            <div class="row m-0">
                <div class="col-sm-4">
                    <div class="page-header float-left">
                        <div class="page-title">
                            <h1>Dashboard</h1>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="page-header float-right">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li><a href="manage-booking.php">Bookings</a></li>
                                <li class="active">Manage Bookings</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Manage Bookings</strong>
                            <div class="float-right">
                                <span class="badge badge-success">Active: <?php echo mysqli_num_rows(mysqli_query($con, "SELECT * FROM bookings WHERE status = 'active'")); ?></span>
                                <span class="badge badge-secondary">Completed: <?php echo mysqli_num_rows(mysqli_query($con, "SELECT * FROM bookings WHERE status = 'completed'")); ?></span>
                                <span class="badge badge-danger">Cancelled: <?php echo mysqli_num_rows(mysqli_query($con, "SELECT * FROM bookings WHERE status = 'cancelled'")); ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>S.No</th>
                                            <th>Booking ID</th>
                                            <th>User Details</th>
                                            <th>Vehicle Details</th>
                                            <th>Parking Info</th>
                                            <th>Timing</th>
                                            <th>Cost</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $cnt = 1;
                                        while ($row = mysqli_fetch_assoc($query)) {
                                            // Calculate duration and cost
                                            $start_time = new DateTime($row['start_time']);
                                            $end_time = $row['end_time'] ? new DateTime($row['end_time']) : new DateTime();
                                            $duration = $start_time->diff($end_time);
                                            $hours = $duration->h + ($duration->days * 24) + ($duration->i / 60);
                                            
                                            // Use paid amount if available and payment is successful, otherwise calculate cost
                                            if (!empty($row['paid_amount']) && $row['payment_status'] == 'paid') {
                                                $cost = floatval($row['paid_amount']);
                                            } else {
                                                $cost = round($hours * floatval($row['price_per_hour']), 2);
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo $cnt; ?></td>
                                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                                            <td>
                                                <strong><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></strong><br>
                                                <small class="text-muted">
                                                    <?php echo $row['Email']; ?><br>
                                                    <?php echo $row['MobileNumber']; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?php echo $row['RegistrationNumber']; ?></strong><br>
                                                <small class="text-muted">
                                                    <?php echo $row['VehicleCompanyname']; ?><br>
                                                    Category: <?php echo $row['VehicleCategory']; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong>Space: <?php echo $row['parking_number']; ?></strong><br>
                                                <small class="text-muted">
                                                    Rate: KES <?php echo number_format($row['price_per_hour'], 2); ?>/hr
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>Start:</strong> <?php echo date('M j, Y g:i A', strtotime($row['start_time'])); ?><br>
                                                    <?php if ($row['end_time']) { ?>
                                                        <strong>End:</strong> <?php echo date('M j, Y g:i A', strtotime($row['end_time'])); ?><br>
                                                    <?php } ?>
                                                    <strong>Duration:</strong> <?php echo round($hours, 1); ?> hrs
                                                </small>
                                            </td>
                                            <td>
                                                <strong>KES <?php echo number_format($cost, 2); ?></strong>
                                                <?php if (!empty($row['paid_amount']) && $row['payment_status'] == 'paid') { ?>
                                                    <br><small class="text-success"><i class="fa fa-check-circle"></i> Paid</small>
                                                <?php } else { ?>
                                                    <br><small class="text-muted"><i class="fa fa-calculator"></i> Calculated</small>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo $row['status'] == 'active' ? 'success' : 
                                                        ($row['status'] == 'completed' ? 'secondary' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] == 'active') { ?>
                                                    <a href="manage-booking.php?action=complete&id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-success btn-sm action-btn" 
                                                       onclick="return confirm('Mark this booking as completed?');">
                                                        <i class="fa fa-check"></i> Complete
                                                    </a>
                                                    <a href="manage-booking.php?action=cancel&id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-warning btn-sm action-btn" 
                                                       onclick="return confirm('Cancel this booking?');">
                                                        <i class="fa fa-times"></i> Cancel
                                                    </a>
                                                <?php } ?>
                                                <a href="manage-booking.php?action=delete&id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-danger btn-sm action-btn" 
                                                   onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            $cnt++;
                                        } 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <?php include_once('includes/footer.php'); ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>