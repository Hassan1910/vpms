<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
} else {
    // Handle deletion
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $booking_id = $_GET['id'];
        $delete_query = mysqli_query($con, "DELETE FROM bookings WHERE id = '$booking_id'");
        if ($delete_query) {
            echo "<script>alert('Booking deleted successfully');</script>";
            echo "<script>window.location.href ='manage-booking.php'</script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again.');</script>";
        }
    }

    // Fetch all bookings
    $query = mysqli_query($con, "SELECT b.*, v.RegistrationNumber, v.VehicleCategory, v.VehicleCompanyname, ps.parking_number, u.FirstName, u.LastName
                                 FROM bookings b
                                 JOIN tblregusers u ON b.user_id = u.ID
                                 JOIN tblvehicle v ON b.vehicle_id = v.ID
                                 JOIN parking_space ps ON b.parking_number = ps.parking_number");

    if (!$query) {
        die("Query failed: " . mysqli_error($con));
    }
?>

<!doctype html>
<html lang="en">
<head>
    <title>Admin - Manage Bookings</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
</head>
<body>

<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="content">
    <div class="animated fadeIn">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header"><strong>Manage</strong> Bookings</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Vehicle Reg No</th>
                                    <th>User Name</th>
                                    <th>Vehicle Category</th>
                                    <th>Vehicle Company</th>
                                    <th>Parking Number</th>
                                    <th>Start Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['RegistrationNumber']; ?></td>
                                        <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
                                        <td><?php echo $row['VehicleCategory']; ?></td>
                                        <td><?php echo $row['VehicleCompanyname']; ?></td>
                                        <td><?php echo $row['parking_number']; ?></td>
                                        <td><?php echo $row['start_time']; ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                        <td>
                                            <a href="manage-booking.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this booking?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .animated -->
</div><!-- .content -->

<div class="clearfix"></div>

<?php include_once('includes/footer.php'); ?>

</div><!-- /#right-panel -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>

<?php } ?>
