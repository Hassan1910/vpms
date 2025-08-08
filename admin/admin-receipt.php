<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include('includes/dbconnection.php');

use Dompdf\Dompdf;

// Check if admin is logged in
if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// 1. Get the booking ID
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : null;
if (!$booking_id) {
    header('location:dashboard.php');
    exit();
}

// Check if download parameter is set
$download = isset($_GET['download']) && $_GET['download'] == '1';

// 2. Fetch receipt data (admin can view any receipt)
$stmt = $con->prepare("
    SELECT
        p.id AS payment_id,
        p.amount,
        p.status AS payment_status,
        p.created_at AS payment_time,
        b.parking_number,
        b.start_time,
        b.end_time,
        CONCAT(COALESCE(u.FirstName, ''), ' ', COALESCE(u.LastName, '')) AS user_name,
        u.MobileNumber AS user_phone,
        u.Email AS user_email,
        v.RegistrationNumber AS car_plate,
        v.VehicleCompanyname AS car_model,
        v.VehicleCategory AS car_category
    FROM payment p
    JOIN bookings b ON p.booking_id = b.id
    JOIN tblregusers u ON b.user_id = u.ID
    JOIN tblvehicle v ON b.vehicle_id = v.ID
    WHERE p.booking_id = ? AND p.status = 'paid'
    ORDER BY p.created_at DESC
    LIMIT 1
");

if (!$stmt) {
    die("SQL Error: " . mysqli_error($con));
}

$stmt->bind_param("i", $booking_id);
if (!$stmt->execute()) {
    die("Execute Error: " . $stmt->error);
}
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo "Receipt not found. Either the booking doesn't exist or payment is not completed.";
    exit();
}

$receipt = $result->fetch_assoc();
$stmt->close();

if ($download) {
    // 4. Set filename
    $fileName = "Admin_Parking_Receipt_#{$receipt['payment_id']}.pdf";

    // 5. Render HTML content
    ob_start();
    include 'admin-receipt-template1.php'; // this should echo or use $receipt
    $html = ob_get_clean();

    // 6. Generate PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 7. Output PDF in browser
    $dompdf->stream($fileName, ['attachment' => 0]);
    exit();
} else {
    // Show receipt page with admin navigation
?>
<!DOCTYPE html>
<html class="no-js" lang="">
<head>
    <title>VPMS Admin - Payment Receipt</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
</head>
<body>
    <?php include_once('includes/sidebar.php');?>
    <?php include_once('includes/header.php');?>
    
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><i class="fa fa-receipt"></i> Payment Receipt - Admin View</h4>
                            <div class="card-header-actions">
                                <a href="view-receipts.php" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Receipts
                                </a>
                                <a href="admin-receipt.php?booking_id=<?= $booking_id ?>&download=1" 
                                   class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fa fa-download"></i> Download PDF
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="receipt-details">
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <h6>Receipt #<?= $receipt['payment_id'] ?></h6>
                                        <p class="text-muted">Booking ID: <?= $booking_id ?></p>
                                        <p class="text-muted">Payment Date: <?= date('M d, Y H:i', strtotime($receipt['payment_time'])) ?></p>
                                    </div>
                                    <div class="col-sm-6 text-right">
                                        <span class="badge badge-success">PAID</span>
                                    </div>
                                </div>
                                
                                <!-- Customer Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6><i class="fa fa-user"></i> Customer Information</h6>
                                        <p><strong>Name:</strong> <?= htmlspecialchars($receipt['user_name']) ?></p>
                                        <p><strong>Phone:</strong> <?= htmlspecialchars($receipt['user_phone']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($receipt['user_email']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fa fa-car"></i> Vehicle Information</h6>
                                        <p><strong>Registration:</strong> <?= htmlspecialchars($receipt['car_plate']) ?></p>
                                        <p><strong>Model:</strong> <?= htmlspecialchars($receipt['car_model']) ?></p>
                                        <p><strong>Category:</strong> <?= htmlspecialchars($receipt['car_category']) ?></p>
                                    </div>
                                </div>
                                
                                <!-- Parking Information -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <h6><i class="fa fa-parking"></i> Parking Information</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p><strong>Space Number:</strong> <?= htmlspecialchars($receipt['parking_number']) ?></p>
                                            </div>
                                            <div class="col-md-4">
                                                <p><strong>Entry Time:</strong> <?= date('M d, Y H:i', strtotime($receipt['start_time'])) ?></p>
                                            </div>
                                            <div class="col-md-4">
                                                <p><strong>Exit Time:</strong> 
                                                    <?= $receipt['end_time'] ? date('M d, Y H:i', strtotime($receipt['end_time'])) : 'Ongoing' ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Information -->
                                <div class="alert alert-success text-center">
                                    <h4><i class="fa fa-money-bill"></i> Total Amount Paid</h4>
                                    <h2>KES <?= number_format($receipt['amount'], 2) ?></h2>
                                    <p>Payment processed successfully on <?= date('F j, Y \a\t g:i A', strtotime($receipt['payment_time'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once('includes/footer.php');?>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
}
// Close the database connection at the very end
mysqli_close($con);
?>
