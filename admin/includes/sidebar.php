<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside id="left-panel" class="left-panel">
    <!-- VPMS Header -->
    <div class="vpms-header">
        <!-- Hamburger menu removed -->
    </div>
    
    <nav class="navbar navbar-expand-sm navbar-default">
        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                    <a href="dashboard.php"><i class="menu-icon fa fa-tachometer-alt"></i>Dashboard</a>
                </li>
                <li class="<?php echo ($currentPage == 'add-parking-space.php') ? 'active' : ''; ?>">
                    <a href="add-parking-space.php"><i class="menu-icon fa fa-plus-circle"></i>Add Parking</a>
                </li>
                <li class="<?php echo ($currentPage == 'manage-parking.php') ? 'active' : ''; ?>">
                    <a href="manage-parking.php"><i class="menu-icon fa fa-car"></i>Manage Parking</a>
                </li>
               
                <li class="<?php echo ($currentPage == 'bwdates-report-ds.php') ? 'active' : ''; ?>">
                    <a href="bwdates-report-ds.php"><i class="menu-icon fa fa-chart-bar"></i>Reports</a>
                </li>
                <li class="<?php echo ($currentPage == 'manage-booking.php') ? 'active' : ''; ?>">
                    <a href="manage-booking.php"><i class="menu-icon fa fa-calendar"></i>Manage Bookings</a>
                </li>
               
                <li class="<?php echo ($currentPage == 'reg-users.php') ? 'active' : ''; ?>">
                    <a href="reg-users.php"><i class="menu-icon fa fa-users"></i>Reg Users</a>
                </li>
                <li class="<?php echo ($currentPage == 'payment.php') ? 'active' : ''; ?>">
                    <a href="payment.php"><i class="menu-icon fa fa-credit-card"></i>Transactions</a>
                </li>
                <li class="<?php echo ($currentPage == 'view-receipts.php') ? 'active' : ''; ?>">
                    <a href="view-receipts.php"><i class="menu-icon fa fa-receipt"></i>View Receipts</a>
                </li>
                <li class="<?php echo ($currentPage == 'manage-feedback.php') ? 'active' : ''; ?>">
                    <a href="manage-feedback.php"><i class="menu-icon fa fa-comments"></i>Manage Feedback</a>
                </li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>
</aside>
