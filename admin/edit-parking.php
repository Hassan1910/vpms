<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid'] == 0)) {
    header('location:logout.php');
    exit();
}

$id = intval($_GET['editid']);

// Delete logic
if (isset($_POST['delete'])) {
    $del = mysqli_query($con, "DELETE FROM parking_space WHERE id='$id'");
    if ($del) {
        echo "<script>alert('Deleted successfully');</script>";
        echo "<script>window.location.href='manage-parking.php';</script>";
        exit();
    } else {
        echo "<script>alert('Delete failed');</script>";
    }
}

// Update logic
if (isset($_POST['update'])) {
    $parking_number = $_POST['parking_number'];
    $status = $_POST['status'];
    $price = $_POST['price'];

    // Build the SQL query
    $sql = "UPDATE parking_space SET parking_number='$parking_number', status='$status', `price_per_hour`='$price' WHERE id='$id' ;";


    // Run the query
    $query = mysqli_query($con, $sql);

    // Check for success/failure
    if ($query) {
        echo "<script>alert('Updated successfully');</script>";
        echo "<script>window.location.href='manage-parking.php';</script>";
        exit();
    } else {
        // Log the MySQL error
        echo "<pre>MySQL Error: " . mysqli_error($con) . "</pre>";
    }
}

// Fetch record
$ret = mysqli_query($con, "SELECT * FROM parking_space WHERE id='$id'");
$row = mysqli_fetch_array($ret);
?>

<!doctype html>
<html>
<head>

    <title>Edit Parking spaces</title>


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

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/html5shiv/3.7.3/html5shiv.min.js"></script> -->

</head>
<body>

<div class="container mt-5">
    <h3>Edit Parking Space</h3>
    <form method="post">
        <div class="form-group">
            <label>Parking Number</label>
            <input type="text" name="parking_number" class="form-control" value="<?php echo $row['parking_number']; ?>" required>
            <small class="form-text text-muted">For consistent numbering, use simple numbers like 1, 2, 3, etc.</small>
        </div>
        <div class="form-group">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="<?php echo $row['status']; ?>" required>
        </div>
        <div class="form-group">
            <label>Price per Hour (KES)</label>
            <input type="text" name="price" class="form-control" value="<?php echo $row['price_per_hour']; ?>" required>
        </div>
        <button type="submit" name="update" class="btn btn-primary">Update</button>
        <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?');">Delete</button>
        <a href="manage-parking.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>