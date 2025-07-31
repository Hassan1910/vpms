<?php
session_start();
error_reporting(E_ALL);
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $cid = $_GET['viewid'];
    $ret = mysqli_query($con, "SELECT * FROM tblvehicle WHERE ID='$cid'");
    $row = mysqli_fetch_array($ret);
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - View Vehicle Detail</title>
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
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
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
                                <li><a href="manage-incomingvehicle.php">View Vehicle</a></li>
                                <li class="active">Incoming Vehicle</li>
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
                            <strong class="card-title">View Vehicle</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mg-b-0">
                                <tr>
                                    <th>Parking Number</th>
                                    <td><?php echo htmlspecialchars($row['ParkingNumber']); ?></td>
                                </tr>
                                <tr>
                                    <th>Vehicle Category</th>
                                    <td><?php echo htmlspecialchars($row['VehicleCategory']); ?></td>
                                </tr>
                                <tr>
                                    <th>Vehicle Company Name</th>
                                    <td><?php echo htmlspecialchars($row['VehicleCompanyname']); ?></td>
                                </tr>
                                <tr>
                                    <th>Registration Number</th>
                                    <td><?php echo htmlspecialchars($row['RegistrationNumber']); ?></td>
                                </tr>
                                <tr>
                                    <th>Owner Name</th>
                                    <td><?php echo isset($row['OwnerName']) ? htmlspecialchars($row['OwnerName']) : ''; ?></td>
                                </tr>
                                <tr>
                                    <th>Owner Contact Number</th>
                                    <td><?php echo isset($row['OwnerContactNumber']) ? htmlspecialchars($row['OwnerContactNumber']) : ''; ?></td>
                                </tr>
            
                                  
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
