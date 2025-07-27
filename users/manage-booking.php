<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsuid']) == 0) {
    header('location:logout.php');
    exit();
}

// Handle checkout logic here
$checkoutMessage = '';
if (isset($_GET['checkout'])) {
    $bookingId = intval($_GET['checkout']);
    $endTime = date('Y-m-d H:i:s');

    // Fetch the parking number linked to this booking
    $bookingFetch = mysqli_query($con, "SELECT parking_number FROM bookings WHERE id = $bookingId AND user_id = " . $_SESSION['vpmsuid']);
    $row = mysqli_fetch_assoc($bookingFetch);

    if ($row) {
        $parkingNumber = $row['parking_number'];

        // Update booking to checked_out
        $updateBooking = mysqli_query($con, "
            UPDATE bookings 
            SET end_time = '$endTime', status = 'completed' 
            WHERE id = $bookingId
        ");

        // Update parking_space status to available
        $updateSpace = mysqli_query($con, "
            UPDATE parking_space 
            SET status = 'available' 
            WHERE parking_number = '$parkingNumber'
        ");

    if ($updateBooking && $updateSpace) {
        // Redirect to payment page with booking ID
        header("Location: payment.php?booking_id=" . $bookingId);
        exit();
    } else {
        $checkoutMessage = '<div class="alert alert-danger">Failed to complete checkout. Please try again.</div>';
    }
    } else {
        $checkoutMessage = '<div class="alert alert-warning">Invalid booking ID or permission denied.</div>';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | VPMS</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <style>
        .booking-card {
            transition: all 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .booking-header {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .booking-body {
            padding: 20px;
        }
        .booking-footer {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 30px;
        }
        .status-active {
            background-color: #28a745;
            color: white;
        }
        .status-completed {
            background-color: #6c757d;
            color: white;
        }
        .action-btn {
            border-radius: 5px;
            padding: 8px 15px;
            font-weight: 500;
            margin-right: 5px;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .checkout-btn {
            background-color: #28a745;
            border-color: #28a745;
        }
        .payment-btn {
            background-color: #007bff;
            border-color: #007bff;
        }
        .receipt-btn {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .page-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            padding-left: 15px;
        }
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .booking-count {
            padding: 10px 15px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .highlight-row {
            animation: highlight 2s ease-in-out;
        }
        @keyframes highlight {
            0% { background-color: rgba(40, 167, 69, 0.2); }
            100% { background-color: transparent; }
        }
        .vehicle-info {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .vehicle-info i {
            color: #6c757d;
            margin-right: 5px;
        }
        .table th {
            border-top: none;
            background: #f1f2f6;
            color: #333;
            font-weight: 600;
        }
        .loader {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }
        .empty-state i {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 15px;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title"><i class="fas fa-calendar-check mr-2"></i>Manage Your Bookings</h2>
                    <a href="book-space.php" class="btn btn-primary"><i class="fas fa-plus mr-2"></i> New Booking</a>
                </div>
                
                <!-- Notification Messages -->
                <div id="notifications">
                    <?php echo $checkoutMessage; ?>
                    
                    <!-- New Booking Success Message -->
                    <?php if(isset($_GET['new_booking'])): ?>
                    <?php $new_booking_id = intval($_GET['new_booking']); ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Success!</strong> Your booking #<?php echo $new_booking_id; ?> has been created successfully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Payment Success Message -->
                    <?php if(isset($_GET['payment_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Payment Successful!</strong> Your payment has been processed successfully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Booking Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card booking-count border-left-success">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Bookings</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-count">Loading...</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-car-side fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card booking-count border-left-primary">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bookings</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-count">Loading...</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card booking-count border-left-info">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Completed</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="completed-count">Loading...</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card booking-count border-left-warning">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Payments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-count">Loading...</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card filters mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="statusFilter"><i class="fas fa-filter mr-1"></i> Filter by Status:</label>
                                <select id="statusFilter" class="form-control">
                                    <option value="">All Bookings</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="dateFilter"><i class="far fa-calendar-alt mr-1"></i> Filter by Date:</label>
                                <select id="dateFilter" class="form-control">
                                    <option value="">All Dates</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="searchBooking"><i class="fas fa-search mr-1"></i> Search:</label>
                                <input type="text" id="searchBooking" class="form-control" placeholder="Booking ID, Vehicle...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading indicator -->
                <div id="loader" class="loader">
                    <i class="fas fa-circle-notch fa-spin fa-3x"></i>
                    <p class="mt-3">Loading your bookings...</p>
                </div>

                <!-- Table with bookings -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5><i class="fas fa-list mr-2"></i>Your Bookings</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="bookingsTable">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag mr-1"></i> ID</th>
                                        <th><i class="fas fa-parking mr-1"></i> Space</th>
                                        <th><i class="fas fa-car mr-1"></i> Vehicle</th>
                                        <th><i class="fas fa-calendar-plus mr-1"></i> Start Time</th>
                                        <th><i class="fas fa-calendar-minus mr-1"></i> End Time</th>
                                        <th><i class="fas fa-info-circle mr-1"></i> Status</th>
                                        <th><i class="fas fa-tools mr-1"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $user_id = $_SESSION['vpmsuid'];
                                $query = "
                                    SELECT 
                                        b.id AS booking_id,
                                        ps.parking_number,
                                        v.RegistrationNumber,
                                        v.VehicleCompanyname,
                                        v.VehicleCategory,
                                        b.start_time,
                                        b.end_time,
                                        b.status
                                    FROM bookings b
                                    JOIN parking_space ps ON b.parking_number = ps.parking_number
                                    JOIN tblvehicle v ON b.vehicle_id = v.ID
                                    WHERE b.user_id = '$user_id'
                                    ORDER BY 
                                        CASE WHEN b.status = 'active' THEN 1 
                                             WHEN b.status = 'completed' THEN 2
                                             ELSE 3 END,
                                        b.start_time DESC
                                ";

                                $result = mysqli_query($con, $query);
                                
                                // Initialize counters
                                $active_count = 0;
                                $completed_count = 0;
                                $pending_payment_count = 0;
                                $total_count = 0;
                                
                                if (!$result) {
                                    echo "<tr><td colspan='7'><div class='alert alert-danger'>Database error: " . mysqli_error($con) . "</div></td></tr>";
                                    error_log("Database query failed: " . mysqli_error($con));
                                } else if (mysqli_num_rows($result) == 0) {
                                    // No bookings found
                                    echo '
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times"></i>
                                                <h5>No Bookings Found</h5>
                                                <p class="text-muted">You haven\'t made any bookings yet.</p>
                                                <a href="book-space.php" class="btn btn-primary mt-3">Book a Space Now</a>
                                            </div>
                                        </td>
                                    </tr>';
                                } else {
                                    // Count booking statistics
                                    $total_count = mysqli_num_rows($result);
                                    
                                    // Process each booking
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        // Update counts
                                        if ($row['status'] == 'active') {
                                            $active_count++;
                                        } else if ($row['status'] == 'completed') {
                                            $completed_count++;
                                            
                                            // Check payment status
                                            $paymentCheck = mysqli_query($con, "SELECT status FROM payment WHERE booking_id = " . $row['booking_id'] . " ORDER BY created_at DESC LIMIT 1");
                                            $isPaid = false;
                                            if ($paymentCheck && mysqli_num_rows($paymentCheck) > 0) {
                                                $paymentRow = mysqli_fetch_assoc($paymentCheck);
                                                $isPaid = ($paymentRow['status'] == 'paid');
                                            }
                                            if (!$isPaid) {
                                                $pending_payment_count++;
                                            }
                                        }
                                        
                                        // Format the date and time
                                        $start_datetime = new DateTime($row['start_time']);
                                        $formatted_start = $start_datetime->format('M d, Y g:i A');
                                        
                                        $formatted_end = 'N/A';
                                        if ($row['end_time'] && $row['end_time'] != '0000-00-00 00:00:00') {
                                            $end_datetime = new DateTime($row['end_time']);
                                            $formatted_end = $end_datetime->format('M d, Y g:i A');
                                        }
                                        
                                        // Highlight row if it's the new booking
                                        $rowClass = (isset($_GET['new_booking']) && $_GET['new_booking'] == $row['booking_id']) ? 'highlight-row' : '';
                                ?>
                                    <tr class="<?php echo $rowClass; ?>" data-status="<?php echo htmlspecialchars($row['status']); ?>" data-id="<?php echo htmlspecialchars($row['booking_id']); ?>">
                                        <td>
                                            <strong>#<?php echo htmlspecialchars($row['booking_id']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-light">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?php echo htmlspecialchars($row['parking_number']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($row['VehicleCompanyname'] . ' (' . $row['VehicleCategory'] . ')'); ?></div>
                                            <small class="text-muted">
                                                <i class="fas fa-car-alt mr-1"></i>
                                                <?php echo htmlspecialchars($row['RegistrationNumber']); ?>
                                            </small>
                                        </td>
                                        <td><?php echo $formatted_start; ?></td>
                                        <td><?php echo $formatted_end; ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'active'): ?>
                                                <span class="badge status-badge status-active">
                                                    <i class="fas fa-clock mr-1"></i> Active
                                                </span>
                                            <?php elseif ($row['status'] == 'completed'): ?>
                                                <span class="badge status-badge status-completed">
                                                    <i class="fas fa-check mr-1"></i> Completed
                                                </span>
                                                <?php 
                                                // Check payment status by looking up the latest payment record
                                                $paymentCheck = mysqli_query($con, "SELECT status FROM payment WHERE booking_id = " . $row['booking_id'] . " ORDER BY created_at DESC LIMIT 1");
                                                $isPaid = false;
                                                if ($paymentCheck && mysqli_num_rows($paymentCheck) > 0) {
                                                    $paymentRow = mysqli_fetch_assoc($paymentCheck);
                                                    $isPaid = ($paymentRow['status'] == 'paid');
                                                }
                                                if ($isPaid): 
                                                ?>
                                                    <span class="badge badge-success mt-1">
                                                        <i class="fas fa-check-circle mr-1"></i> Paid
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning mt-1">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> Payment Pending
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-info">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if ($row['status'] == 'active'): ?>
                                                    <a href="?checkout=<?php echo $row['booking_id']; ?>" class="btn action-btn checkout-btn" 
                                                       onclick="return confirm('Are you sure you want to checkout from this parking space?')">
                                                        <i class="fas fa-sign-out-alt mr-1"></i> Checkout
                                                    </a>
                                                <?php elseif ($row['status'] == 'completed'): ?>
                                                    <?php 
                                                    // Check payment status for actions
                                                    $paymentCheck = mysqli_query($con, "SELECT status FROM payment WHERE booking_id = " . $row['booking_id'] . " ORDER BY created_at DESC LIMIT 1");
                                                    $isPaid = false;
                                                    if ($paymentCheck && mysqli_num_rows($paymentCheck) > 0) {
                                                        $paymentRow = mysqli_fetch_assoc($paymentCheck);
                                                        $isPaid = ($paymentRow['status'] == 'paid');
                                                    }
                                                    if (!$isPaid): 
                                                    ?>
                                                    <a href="payment.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn action-btn payment-btn">
                                                        <i class="fas fa-credit-card mr-1"></i> Pay Now
                                                    </a>
                                                    <?php else: ?>
                                                    <a href="receipt.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn action-btn receipt-btn">
                                                        <i class="fas fa-file-invoice mr-1"></i> Receipt
                                                    </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-outline-secondary btn-sm view-details" data-id="<?php echo $row['booking_id']; ?>">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    } // end while loop
                                } // end if-else for query result
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Details Modal -->
                <div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="bookingDetailsContent">
                                <div class="text-center py-4">
                                    <i class="fas fa-circle-notch fa-spin fa-2x"></i>
                                    <p class="mt-2">Loading booking details...</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <div id="modalActionButtons"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update the booking counters
    document.getElementById('active-count').textContent = '<?php echo $active_count; ?>';
    document.getElementById('total-count').textContent = '<?php echo $total_count; ?>';
    document.getElementById('completed-count').textContent = '<?php echo $completed_count; ?>';
    document.getElementById('pending-count').textContent = '<?php echo $pending_payment_count; ?>';

    // Filter functions
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const searchFilter = document.getElementById('searchBooking');
    
    const applyFilters = () => {
        const status = statusFilter.value.toLowerCase();
        const dateOption = dateFilter.value;
        const searchText = searchFilter.value.toLowerCase();
        
        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        
        rows.forEach(row => {
            let showRow = true;
            
            // Status filtering
            if (status && row.getAttribute('data-status') !== status) {
                showRow = false;
            }
            
            // Search filtering
            if (searchText && !row.textContent.toLowerCase().includes(searchText)) {
                showRow = false;
            }
            
            // Apply visibility
            row.style.display = showRow ? '' : 'none';
        });
    };
    
    // Add event listeners to filters
    statusFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);
    searchFilter.addEventListener('input', applyFilters);
    
    // Details modal functionality
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-id');
            const modal = document.getElementById('bookingDetailsModal');
            const modalContent = document.getElementById('bookingDetailsContent');
            const actionButtons = document.getElementById('modalActionButtons');
            
            // Show loading state
            modalContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-circle-notch fa-spin fa-2x"></i>
                    <p class="mt-2">Loading booking details...</p>
                </div>
            `;
            
            // In a real application, you would fetch booking details via AJAX
            // For now, we'll simulate it with the data we already have
            const row = document.querySelector(`tr[data-id="${bookingId}"]`);
            const status = row.getAttribute('data-status');
            
            // Simulate loading time
            setTimeout(() => {
                const cells = row.querySelectorAll('td');
                
                // Generate HTML for the modal content
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Booking Information</h6>
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Booking ID
                                    <span class="badge badge-primary badge-pill">${cells[0].textContent.trim()}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Parking Space
                                    <span>${cells[1].textContent.trim()}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Status
                                    <span>${cells[5].innerHTML}</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Vehicle Information</h6>
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item">
                                    <strong>Vehicle:</strong> ${cells[2].textContent.trim()}
                                </li>
                            </ul>
                            
                            <h6 class="font-weight-bold">Time Information</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>Check-in:</strong> ${cells[3].textContent.trim()}
                                </li>
                                <li class="list-group-item">
                                    <strong>Check-out:</strong> ${cells[4].textContent.trim()}
                                </li>
                            </ul>
                        </div>
                    </div>
                `;
                
                // Update action buttons based on status
                actionButtons.innerHTML = '';
                
                if (status === 'active') {
                    actionButtons.innerHTML = `
                        <a href="?checkout=${bookingId}" class="btn btn-success">
                            <i class="fas fa-sign-out-alt mr-1"></i> Checkout
                        </a>
                    `;
                } else if (status === 'completed') {
                    // Check if payment is completed or pending based on what's showing in the table
                    if (cells[5].textContent.includes('Paid') || cells[5].innerHTML.includes('badge-success')) {
                        actionButtons.innerHTML = `
                            <a href="receipt.php?booking_id=${bookingId}" class="btn btn-info">
                                <i class="fas fa-file-invoice mr-1"></i> View Receipt
                            </a>
                        `;
                    } else {
                        actionButtons.innerHTML = `
                            <a href="payment.php?booking_id=${bookingId}" class="btn btn-primary">
                                <i class="fas fa-credit-card mr-1"></i> Make Payment
                            </a>
                        `;
                    }
                }
            }, 500);
            
            // Show the modal
            $(modal).modal('show');
        });
    });
    
    // Auto-close alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            $(alert).alert('close');
        });
    }, 5000);
    
    <?php if(isset($_GET['new_booking'])): ?>
    // Highlight the new booking row
    const newBookingId = '<?php echo $new_booking_id; ?>';
    const newRow = document.querySelector(`tr[data-id="${newBookingId}"]`);
    if (newRow) {
        setTimeout(() => {
            newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
    <?php endif; ?>
});
</script>

<?php include_once('includes/footer.php'); ?>

<!-- Required JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
    // Initialize DataTables for better table functionality if we have more than 10 rows
    $(document).ready(function() {
        if ($('#bookingsTable tbody tr').length > 10) {
            $('#bookingsTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true,
                "searching": true,
                "responsive": true,
                "pageLength": 10,
                "language": {
                    "emptyTable": "No bookings found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ bookings",
                    "infoEmpty": "Showing 0 to 0 of 0 bookings",
                    "infoFiltered": "(filtered from _MAX_ total bookings)"
                }
            });
        }
    });
</script>
</body>
</html>