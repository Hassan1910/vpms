<?php
session_start();
error_reporting(E_ALL);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}
?>

<!doctype html>
<html lang="">
<head>
    <title>Transaction Management</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
</head>

<body>
<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="container mt-5">
    <h3 class="mb-4">Transaction Records</h3>

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Transaction ID</th>
                <th>Booking ID</th>
                <th>Parking Number</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Receipt</th>
                <th>Remarks</th>
                <th>Date</th>
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
        p.created_at
    FROM payment p
    JOIN bookings b ON p.booking_id = b.id
    JOIN parking_space ps ON b.parking_number = ps.parking_number
    JOIN tblregusers u ON b.user_id = u.ID
    ORDER BY p.created_at DESC
";

$stmt = $con->prepare($query);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $receiptUrl = isset($row['receipt_url']) ? trim($row['receipt_url']) : '';
?>
            <tr>
                <td><?php echo $cnt++; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['payment_id']); ?></td>
                <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                <td><?php echo htmlspecialchars($row['parking_number']); ?></td>
                <td>KES <?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <?php if (!empty($receiptUrl)): ?>
                        <a href="<?php echo htmlspecialchars($receiptUrl); ?>" target="_blank">View</a>
                    <?php else: ?>
                        <span class="text-muted">N/A</span>
                    <?php endif; ?>
                </td>
                <td><?php echo !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : ''; ?></td>
                <td><?php echo !empty($row['created_at']) ? htmlspecialchars($row['created_at']) : ''; ?></td>
            </tr>
<?php
    }
} else {
    echo "Error preparing statement: " . $con->error;
}
?>
        </tbody>
    </table>
</div>

<?php include_once('includes/footer.php'); ?>
</body>
</html>
