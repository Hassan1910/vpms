<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include('includes/dbconnection.php');

use Dompdf\Dompdf;

// Check if user is logged in
if (strlen($_SESSION['vpmsuid']==0)) {
    header('location:logout.php');
    exit;
}

// 1. Get the booking ID
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : null;
if (!$booking_id) {
    header('location:dashboard.php');
    exit;
}

// Check if download parameter is set
$download = isset($_GET['download']) && $_GET['download'] == '1';

// 2. Fetch receipt data
$stmt = $con->prepare("
    SELECT 
        p.id AS payment_id,
        p.amount,
        p.status AS payment_status,
        p.created_at AS payment_time,
        b.parking_number,
        b.start_time,
        b.end_time,
        CONCAT(u.firstname, ' ', u.lastname) AS user_name,
        u.MobileNumber AS user_phone,
        v.RegistrationNumber AS car_plate,
        v.VehicleCompanyname AS car_model
    FROM payment p
    JOIN bookings b ON p.booking_id = b.id
    JOIN tblregusers u ON b.user_id = u.id
    JOIN tblvehicle v ON b.vehicle_id = v.id
    WHERE p.booking_id = ? AND p.status = 'paid' AND b.user_id = ?
    ORDER BY p.created_at DESC
    LIMIT 1
");

$stmt->bind_param("ii", $booking_id, $_SESSION['vpmsuid']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo "Receipt not found. Either the booking doesn't exist, payment is not completed, or you don't have permission to view this receipt.";
    exit;
}

$receipt = $result->fetch_assoc();
$stmt->close();
$con->close();

if ($download) {
    // 4. Set filename
    $fileName = "Parking_Receipt_#{$receipt['payment_id']}.pdf";

    // 5. Render HTML content
    ob_start();
    include 'parking-receipt-template.php'; // this should echo or use $receipt
    $html = ob_get_clean();

    // 6. Generate PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 7. Output PDF in browser
    $dompdf->stream($fileName, ['attachment' => 0]);
    exit;
} else {
    // Show receipt page with navigation
?>
<!DOCTYPE html>
<html class="no-js" lang="">
<head>
    <title>VPMS - Payment Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
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
                            <h4 class="card-title"><i class="fa fa-receipt"></i> Payment Receipt</h4>
                        </div>
                        <div class="card-body">
                            <div class="receipt-details">
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <h6>Receipt #<?= $receipt['payment_id'] ?></h6>
                                        <p class="text-muted">Payment Date: <?= date('M d, Y H:i', strtotime($receipt['payment_time'])) ?></p>
                                    </div>
                                    <div class="col-sm-6 text-right">
                                        <span class="badge badge-success">PAID</span>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h6>Customer Details</h6>
                                        <p><strong><?= htmlspecialchars($receipt['user_name']) ?></strong><br>
                                        Phone: <?= htmlspecialchars($receipt['user_phone']) ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <h6>Vehicle Details</h6>
                                        <p><strong><?= htmlspecialchars($receipt['car_plate']) ?></strong><br>
                                        Model: <?= htmlspecialchars($receipt['car_model']) ?></p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h6>Parking Details</h6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><strong>Parking Number:</strong></td>
                                                <td><?= htmlspecialchars($receipt['parking_number']) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Start Time:</strong></td>
                                                <td><?= date('M d, Y H:i', strtotime($receipt['start_time'])) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>End Time:</strong></td>
                                                <td><?= date('M d, Y H:i', strtotime($receipt['end_time'])) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Amount Paid:</strong></td>
                                                <td><strong>Ksh <?= number_format($receipt['amount'], 2) ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="receipt.php?booking_id=<?= $booking_id ?>&download=1" class="btn btn-primary" target="_blank">
                                    <i class="fa fa-download"></i> Download PDF Receipt
                                </a>
                                <a href="dashboard.php" class="btn btn-secondary ml-2">
                                    <i class="fa fa-arrow-left"></i> Back to Dashboard
                                </a>
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
</body>
</html>
<?php
}
?>