<?php
session_start();
error_reporting(E_ALL);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Handle receipt actions
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['booking_id'])) {
    $booking_id = (int)$_GET['booking_id'];
    // Redirect to receipt viewing with admin privileges
    header("location: admin-receipt.php?booking_id=$booking_id");
    exit();
}
?>

<!doctype html>
<html lang="">
<head>
    <title>Admin - View Receipts</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <style>
        .receipt-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
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
        .receipt-actions {
            white-space: nowrap;
        }
        .receipt-actions .btn {
            margin-right: 5px;
            padding: 4px 8px;
            font-size: 12px;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="content">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-receipt me-2"></i>Receipt Management
                        </h4>
                        <small>View and manage all user payment receipts</small>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter Section -->
                        <div class="search-box">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" id="searchInput" class="form-control" placeholder="Search by username, payment ID, or parking number...">
                                </div>
                                <div class="col-md-3">
                                    <select id="statusFilter" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="paid">Paid</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" id="dateFilter" class="form-control" placeholder="Filter by date">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary btn-block" onclick="filterReceipts()">
                                        <i class="fa fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="receiptsTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Payment ID</th>
                                        <th>Booking ID</th>
                                        <th>Parking Number</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php
$cnt = 1;
$query = "
    SELECT
        CONCAT(COALESCE(u.FirstName, ''), ' ', COALESCE(u.LastName, '')) AS username,
        p.id AS payment_id,
        p.booking_id,
        ps.parking_number,
        p.amount,
        p.status,
        p.receipt_url,
        p.remarks,
        p.created_at,
        u.ID as user_id
    FROM payment p
    JOIN bookings b ON p.booking_id = b.id
    JOIN parking_space ps ON b.parking_number = ps.parking_number
    JOIN tblregusers u ON b.user_id = u.ID
    ORDER BY p.created_at DESC
";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $receiptUrl = isset($row['receipt_url']) ? trim($row['receipt_url']) : '';
        $statusClass = '';
        switch(strtolower($row['status'])) {
            case 'paid':
                $statusClass = 'status-paid';
                break;
            case 'pending':
                $statusClass = 'status-pending';
                break;
            case 'failed':
                $statusClass = 'status-failed';
                break;
            default:
                $statusClass = 'status-pending';
        }
?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['parking_number']); ?></td>
                                        <td>KES <?php echo number_format($row['amount'], 2); ?></td>
                                        <td>
                                            <span class="receipt-status <?php echo $statusClass; ?>">
                                                <?php echo strtoupper($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                        <td class="receipt-actions">
                                            <?php if (strtolower($row['status']) == 'paid'): ?>
                                                <a href="admin-receipt.php?booking_id=<?php echo $row['booking_id']; ?>" 
                                                   class="btn btn-primary btn-sm" target="_blank" title="View Receipt">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="admin-receipt.php?booking_id=<?php echo $row['booking_id']; ?>&download=1" 
                                                   class="btn btn-success btn-sm" target="_blank" title="Download PDF">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No Receipt</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
<?php
    }
} else {
?>
                                    <tr>
                                        <td colspan="9" class="text-center">No payment records found</td>
                                    </tr>
<?php
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script>
function filterReceipts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const dateFilter = document.getElementById('dateFilter').value;
    const table = document.getElementById('receiptsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        
        if (cells.length > 1) {
            const username = cells[1].textContent.toLowerCase();
            const paymentId = cells[2].textContent.toLowerCase();
            const parkingNumber = cells[4].textContent.toLowerCase();
            const status = cells[6].textContent.toLowerCase();
            const date = cells[7].textContent;
            
            let showRow = true;
            
            // Search filter
            if (searchTerm && !username.includes(searchTerm) && 
                !paymentId.includes(searchTerm) && !parkingNumber.includes(searchTerm)) {
                showRow = false;
            }
            
            // Status filter
            if (statusFilter && !status.includes(statusFilter)) {
                showRow = false;
            }
            
            // Date filter
            if (dateFilter) {
                const filterDate = new Date(dateFilter);
                const rowDate = new Date(date);
                if (filterDate.toDateString() !== rowDate.toDateString()) {
                    showRow = false;
                }
            }
            
            row.style.display = showRow ? '' : 'none';
        }
    }
}

// Real-time search
document.getElementById('searchInput').addEventListener('keyup', filterReceipts);
document.getElementById('statusFilter').addEventListener('change', filterReceipts);
document.getElementById('dateFilter').addEventListener('change', filterReceipts);
</script>

</body>
</html>
