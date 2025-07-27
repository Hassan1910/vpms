<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vpmsaid']) == 0) {
    header('location:logout.php');
} else {
    // For deleting
    if (isset($_GET['del'])) {
        $catid = $_GET['del'];
        mysqli_query($con, "DELETE FROM tblregusers WHERE ID = '$catid'");
        echo "<script>alert('Data Deleted');</script>";
        echo "<script>window.location.href='reg-users.php'</script>";
    }

    // For editing
    if (isset($_POST['update'])) {
        $catid = $_POST['id'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $mobileNumber = $_POST['mobileNumber'];
        $email = $_POST['email'];

        $query = "UPDATE tblregusers SET FirstName = ?, LastName = ?, MobileNumber = ?, Email = ? WHERE ID = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $firstName, $lastName, $mobileNumber, $email, $catid);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo "<script>alert('Data Updated');</script>";
            echo "<script>window.location.href='reg-users.php'</script>";
        } else {
            echo "<script>alert('Error updating data');</script>";
        }

        mysqli_stmt_close($stmt);
    }

    // Fetch user details for editing
    $editId = isset($_GET['editid']) ? $_GET['editid'] : null;
    $userDetails = null;
    if ($editId) {
        $query = "SELECT * FROM tblregusers WHERE ID = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $editId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userDetails = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - Manage Category</title>
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
    <!-- Left Panel -->
    <?php include_once('includes/sidebar.php');?>
    <!-- Left Panel -->

    <!-- Right Panel -->
    <?php include_once('includes/header.php');?>

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
                                <li><a href="reg-users.php">Registered Users</a></li>
                                <li class="active">Registered Users</li>
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
                            <strong class="card-title">Registered Users</strong>
                        </div>
                        <div class="card-body">
                            <?php if ($editId && $userDetails): ?>
                                <form method="post" action="reg-users.php">
                                    <input type="hidden" name="id" value="<?php echo $userDetails['ID']; ?>">
                                    <div class="form-group">
                                        <label for="firstName">First Name</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo $userDetails['FirstName']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo $userDetails['LastName']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="mobileNumber">Mobile Number</label>
                                        <input type="text" class="form-control" id="mobileNumber" name="mobileNumber" value="<?php echo $userDetails['MobileNumber']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $userDetails['Email']; ?>" required>
                                    </div>
                                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                                </form>
                            <?php endif; ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>S.NO</th>
                                        <th>Name</th>
                                        <th>Contact Number</th>
                                        <th>Email</th>
                                        <th>Registration Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <?php
                                $ret = mysqli_query($con, "SELECT * FROM tblregusers");
                                $cnt = 1;
                                while ($row = mysqli_fetch_array($ret)) {
                                ?>
                                <tr>
                                    <td><?php echo $cnt; ?></td>
                                    <td><?php echo $row['FirstName']; ?> <?php echo $row['LastName']; ?></td>
                                    <td><?php echo $row['MobileNumber']; ?></td>
                                    <td><?php echo $row['Email']; ?></td>
                                    <td><?php echo $row['RegDate']; ?></td>
                                    <td>
                                        <a href="reg-users.php?editid=<?php echo $row['ID']; ?>" class="btn btn-primary">Edit</a>
                                        <a href="reg-users.php?del=<?php echo $row['ID']; ?>" class="btn btn-danger" onClick="return confirm('Are you sure you want to delete?')">Delete</a>
                                    </td>
                                </tr>
                                <?php
                                $cnt = $cnt + 1;
                                }
                                ?>
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

<!-- Right Panel -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
<?php } ?>
