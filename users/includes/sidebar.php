<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">
        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                    <a href="dashboard.php"><i class="menu-icon fa fa-tachometer-alt"></i>Dashboard</a>
                </li>

                <li class="<?php echo ($currentPage == 'book-space.php') ? 'active' : ''; ?>">
                    <a href="book-space.php"><i class="menu-icon fa fa-plus-circle"></i>Book a Space</a>
                </li>
                <li class="<?php echo ($currentPage == 'manage-booking.php') ? 'active' : ''; ?>">
                    <a href="manage-booking.php"><i class="menu-icon fa fa-calendar"></i>My Bookings</a>
                </li>
                <li class="<?php echo ($currentPage == 'history.php') ? 'active' : ''; ?>">
                    <a href="history.php"><i class="menu-icon fa fa-credit-card"></i>My Transactions</a>
                </li>
                <li class="<?php echo ($currentPage == 'feedback.php') ? 'active' : ''; ?>">
                    <a href="feedback.php"><i class="menu-icon fa fa-comment-dots"></i>Submit Feedback</a>
                </li>
                <li class="<?php echo ($currentPage == 'my-feedback.php') ? 'active' : ''; ?>">
                    <a href="my-feedback.php"><i class="menu-icon fa fa-comments"></i>My Feedback</a>
                </li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>
</aside>