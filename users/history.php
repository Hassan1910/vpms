<?php
session_start();
error_reporting(1);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsuid']) == 0) {
    header('location:logout.php');
    exit();
}
?>

<!doctype html>
<html lang="">
<head>
    <title>VPMS - Transaction History</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <style>
        .content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .transaction-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #28a745;
        }
        
        .transaction-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .transaction-card.pending {
            border-left-color: #ffc107;
        }
        
        .transaction-card.failed {
            border-left-color: #dc3545;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .amount-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: #28a745;
        }
        
        .transaction-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .transaction-info {
            flex: 1;
            min-width: 200px;
        }
        
        .transaction-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-custom {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .search-filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .no-transactions {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .no-transactions i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .transaction-details {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .transaction-actions {
                margin-top: 1rem;
                width: 100%;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>
<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="content">
    <div class="animated fadeIn">
        <!-- Page Header -->
        <div class="page-header text-center">
            <div class="container">
                <h1 class="display-4 mb-0">
                    <i class="fa fa-credit-card mr-3"></i>
                    Transaction History
                </h1>
                <p class="lead">View and manage your parking payment transactions</p>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <?php
                // Get transaction statistics
                $stats_query = "
                    SELECT 
                        COUNT(*) as total_transactions,
                        SUM(CASE WHEN p.status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                        SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN p.status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                        SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END) as total_paid
                    FROM payment p
                    JOIN bookings b ON p.booking_id = b.id
                    WHERE b.user_id = ?
                ";
                
                $stats_stmt = $con->prepare($stats_query);
                $stats_stmt->bind_param("i", $_SESSION['vpmsuid']);
                $stats_stmt->execute();
                $stats_result = $stats_stmt->get_result();
                $stats = $stats_result->fetch_assoc();
                $stats_stmt->close();
                ?>
                
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $stats['total_transactions']; ?></div>
                        <div class="stats-label">Total Transactions</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stats-card text-center">
                        <div class="stats-number text-success"><?php echo $stats['paid_count']; ?></div>
                        <div class="stats-label">Successful Payments</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stats-card text-center">
                        <div class="stats-number text-warning"><?php echo $stats['pending_count']; ?></div>
                        <div class="stats-label">Pending Payments</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stats-card text-center">
                        <div class="stats-number text-primary">KES <?php echo number_format($stats['total_paid'], 2); ?></div>
                        <div class="stats-label">Total Amount Paid</div>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="searchTransaction">Search Transactions</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchTransaction" 
                                       placeholder="Search by transaction ID, parking number, or amount...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="statusFilter">Filter by Status</label>
                            <select class="form-control" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="dateFilter">Filter by Date</label>
                            <select class="form-control" id="dateFilter">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Loading Spinner -->
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading transactions...</p>
            </div>
            
            <!-- Transactions List -->
            <div class="transactions-container">
                <?php
                $cnt = 1;
                $query = "
                    SELECT 
                        p.id AS payment_id,
                        p.booking_id,
                        ps.parking_number,
                        p.amount,
                        p.status,
                        p.receipt_url,
                        p.remarks,
                        p.created_at,
                        b.start_time,
                        b.end_time,
                        v.RegistrationNumber as vehicle_reg,
                        v.VehicleCompanyname as vehicle_model
                    FROM payment p
                    JOIN bookings b ON p.booking_id = b.id
                    JOIN parking_space ps ON b.parking_number = ps.parking_number
                    LEFT JOIN tblvehicle v ON b.vehicle_id = v.id
                    WHERE b.user_id = ?
                    ORDER BY p.created_at DESC
                ";

                $stmt = $con->prepare($query);
                $stmt->bind_param("i", $_SESSION['vpmsuid']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_class = '';
                        $status_badge_class = '';
                        
                        switch($row['status']) {
                            case 'paid':
                                $status_class = 'paid';
                                $status_badge_class = 'status-paid';
                                break;
                            case 'pending':
                                $status_class = 'pending';
                                $status_badge_class = 'status-pending';
                                break;
                            case 'failed':
                                $status_class = 'failed';
                                $status_badge_class = 'status-failed';
                                break;
                        }
                ?>
                        <div class="transaction-card <?php echo $status_class; ?>" data-transaction-id="<?php echo $row['payment_id']; ?>">
                            <div class="transaction-details">
                                <div class="transaction-info">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                <i class="fa fa-credit-card mr-2"></i>
                                                Transaction #<?php echo htmlspecialchars($row['payment_id']); ?>
                                            </h5>
                                            <small class="text-muted">
                                                Booking ID: <?php echo htmlspecialchars($row['booking_id']); ?>
                                            </small>
                                        </div>
                                        <span class="status-badge <?php echo $status_badge_class; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <i class="fa fa-map-marker mr-2"></i>
                                                <strong>Parking:</strong> <?php echo htmlspecialchars($row['parking_number']); ?>
                                            </p>
                                            <?php if (!empty($row['vehicle_reg'])) { ?>
                                            <p class="mb-1">
                                                <i class="fa fa-car mr-2"></i>
                                                <strong>Vehicle:</strong> <?php echo htmlspecialchars($row['vehicle_reg']); ?>
                                                <?php if (!empty($row['vehicle_model'])) { ?>
                                                    (<?php echo htmlspecialchars($row['vehicle_model']); ?>)
                                                <?php } ?>
                                            </p>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <i class="fa fa-calendar mr-2"></i>
                                                <strong>Date:</strong> 
                                                <?php 
                                                if (!empty($row['created_at'])) {
                                                    echo date('M d, Y', strtotime($row['created_at']));
                                                } else {
                                                    echo '<span class="text-muted">Not recorded</span>';
                                                }
                                                ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="fa fa-clock-o mr-2"></i>
                                                <strong>Time:</strong> 
                                                <?php 
                                                if (!empty($row['created_at'])) {
                                                    echo date('h:i A', strtotime($row['created_at']));
                                                } else {
                                                    echo '<span class="text-muted">Not recorded</span>';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($row['remarks'])) { ?>
                                    <p class="mb-2">
                                        <i class="fa fa-comment mr-2"></i>
                                        <strong>Remarks:</strong> <?php echo htmlspecialchars($row['remarks']); ?>
                                    </p>
                                    <?php } ?>
                                    
                                    <div class="amount-text">
                                        KES <?php echo number_format($row['amount'], 2); ?>
                                    </div>
                                </div>
                                
                                <div class="transaction-actions">
                                    <?php if ($row['status'] == 'paid') { ?>
                                        <a href="receipt.php?booking_id=<?php echo $row['booking_id']; ?>" 
                                           class="btn btn-primary btn-custom">
                                            <i class="fa fa-receipt mr-1"></i>
                                            View Receipt
                                        </a>
                                    <?php } ?>
                                    
                                    <?php if ($row['status'] == 'pending') { ?>
                                        <a href="payment.php?booking_id=<?php echo $row['booking_id']; ?>" 
                                           class="btn btn-warning btn-custom">
                                            <i class="fa fa-credit-card mr-1"></i>
                                            Complete Payment
                                        </a>
                                    <?php } ?>
                                    
                                    <button class="btn btn-outline-info btn-custom" 
                                            onclick="showTransactionDetails(<?php echo $row['payment_id']; ?>)">
                                        <i class="fa fa-info-circle mr-1"></i>
                                        Details
                                    </button>
                                </div>
                            </div>
                        </div>
                <?php 
                    }
                } else {
                ?>
                    <div class="no-transactions">
                        <i class="fa fa-credit-card"></i>
                        <h4>No Transactions Found</h4>
                        <p class="text-muted">You haven't made any parking transactions yet.</p>
                        <a href="book-space.php" class="btn btn-primary btn-custom">
                            <i class="fa fa-plus mr-2"></i>
                            Book Your First Parking Space
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php include_once('includes/footer.php'); ?>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailsModalLabel">
                    <i class="fa fa-info-circle mr-2"></i>
                    Transaction Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="transactionDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchTransaction').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        filterTransactions();
    });
    
    // Status filter
    $('#statusFilter').on('change', function() {
        filterTransactions();
    });
    
    // Date filter
    $('#dateFilter').on('change', function() {
        filterTransactions();
    });
    
    function filterTransactions() {
        var searchTerm = $('#searchTransaction').val().toLowerCase();
        var statusFilter = $('#statusFilter').val();
        var dateFilter = $('#dateFilter').val();
        
        $('.transaction-card').each(function() {
            var card = $(this);
            var visible = true;
            
            // Search filter
            if (searchTerm) {
                var cardText = card.text().toLowerCase();
                if (cardText.indexOf(searchTerm) === -1) {
                    visible = false;
                }
            }
            
            // Status filter
            if (statusFilter && visible) {
                var hasStatusClass = card.hasClass(statusFilter);
                if (!hasStatusClass) {
                    visible = false;
                }
            }
            
            // Date filter (simplified - you can enhance this)
            if (dateFilter && visible) {
                // Add date filtering logic here
                // For now, we'll just show all
            }
            
            // Show/hide the card
            if (visible) {
                card.show().addClass('animate__animated animate__fadeIn');
            } else {
                card.hide().removeClass('animate__animated animate__fadeIn');
            }
        });
        
        // Check if no results
        var visibleCards = $('.transaction-card:visible').length;
        if (visibleCards === 0) {
            if ($('.no-results-message').length === 0) {
                $('.transactions-container').append(
                    '<div class="no-results-message text-center p-4">' +
                    '<i class="fa fa-search fa-3x text-muted mb-3"></i>' +
                    '<h4 class="text-muted">No transactions found</h4>' +
                    '<p class="text-muted">Try adjusting your search or filter criteria.</p>' +
                    '</div>'
                );
            }
        } else {
            $('.no-results-message').remove();
        }
    }
    
    // Animate cards on page load
    $('.transaction-card').each(function(index) {
        $(this).delay(index * 100).queue(function(next) {
            $(this).addClass('animate__animated animate__fadeInUp');
            next();
        });
    });
});

function showTransactionDetails(transactionId) {
    // Show loading state
    $('#transactionDetailsContent').html(
        '<div class="text-center p-4">' +
        '<div class="spinner-border text-primary" role="status">' +
        '<span class="sr-only">Loading...</span>' +
        '</div>' +
        '<p class="mt-2">Loading transaction details...</p>' +
        '</div>'
    );
    
    // Show modal
    $('#transactionDetailsModal').modal('show');
    
    // In a real application, you would fetch details via AJAX
    // For now, we'll show a placeholder
    setTimeout(function() {
        $('#transactionDetailsContent').html(
            '<div class="row">' +
            '<div class="col-md-6">' +
            '<h6>Transaction Information</h6>' +
            '<p><strong>Transaction ID:</strong> ' + transactionId + '</p>' +
            '<p><strong>Status:</strong> <span class="badge badge-success">Paid</span></p>' +
            '<p><strong>Amount:</strong> KES 200.00</p>' +
            '</div>' +
            '<div class="col-md-6">' +
            '<h6>Booking Information</h6>' +
            '<p><strong>Parking Number:</strong> A-101</p>' +
            '<p><strong>Duration:</strong> 2 hours</p>' +
            '<p><strong>Date:</strong> ' + new Date().toLocaleDateString() + '</p>' +
            '</div>' +
            '</div>' +
            '<hr>' +
            '<div class="text-center">' +
            '<button class="btn btn-primary" onclick="window.open(\'receipt.php?booking_id=1\', \'_blank\')">' +
            '<i class="fa fa-receipt mr-2"></i>View Receipt' +
            '</button>' +
            '</div>'
        );
    }, 1000);
}
</script>
</body>
</html>