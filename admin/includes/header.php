<div id="right-panel" class="right-panel">
<header id="header" class="header">
            <div class="top-left">
                <div class="navbar-header">
                    <a class="navbar-brand" href="dashboard.php">
                        <img src="../assets/img/images.png" alt="VPMS Logo" style="height: 40px; margin-right: 10px;">
                        <strong style="color: blue; vertical-align: middle;">VPMS</strong>
                    </a>
                </div>
            </div>
            <div class="top-right">
                <div class="header-menu">
                    <div class="header-left">
                        <!-- Feedback Notifications -->
                        <?php
                        // Get unread feedback count
                        $unreadFeedbackQuery = "SELECT COUNT(*) as unread_count FROM tblfeedback WHERE Status = 'Open'";
                        $unreadFeedbackResult = mysqli_query($con, $unreadFeedbackQuery);
                        $unreadFeedback = mysqli_fetch_array($unreadFeedbackResult);

                        // Get unread user replies count
                        $unreadRepliesQuery = "SELECT COUNT(*) as unread_replies FROM tblfeedback_replies WHERE SenderType = 'User' AND IsRead = 0";
                        $unreadRepliesResult = mysqli_query($con, $unreadRepliesQuery);
                        $unreadReplies = mysqli_fetch_array($unreadRepliesResult);

                        $totalUnread = $unreadFeedback['unread_count'] + $unreadReplies['unread_replies'];
                        ?>

                        <div class="dropdown notification-dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="adminNotificationDropdown">
                                <i class="fas fa-bell fa-lg"></i>
                                <?php if ($totalUnread > 0): ?>
                                    <span class="notification-badge"><?php echo $totalUnread; ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right notification-menu">
                                <div class="dropdown-header">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </div>
                                <div class="dropdown-divider"></div>

                                <?php if ($unreadFeedback['unread_count'] > 0): ?>
                                    <a class="dropdown-item" href="manage-feedback.php?status=Open">
                                        <i class="fas fa-comment-dots text-warning me-2"></i>
                                        <?php echo $unreadFeedback['unread_count']; ?> new feedback submissions
                                    </a>
                                <?php endif; ?>

                                <?php if ($unreadReplies['unread_replies'] > 0): ?>
                                    <a class="dropdown-item" href="manage-feedback.php">
                                        <i class="fas fa-reply text-info me-2"></i>
                                        <?php echo $unreadReplies['unread_replies']; ?> new user replies
                                    </a>
                                <?php endif; ?>

                                <?php if ($totalUnread == 0): ?>
                                    <div class="dropdown-item text-muted text-center">
                                        <i class="fas fa-check-circle me-2"></i>No new notifications
                                    </div>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="manage-feedback.php">
                                    <i class="fas fa-eye me-2"></i>View All Feedback
                                </a>
                            </div>
                        </div>
                        
                        <script>
                        // Mark notifications as read when dropdown is opened
                        $(document).ready(function() {
                            $('#adminNotificationDropdown').on('click', function() {
                                // Only make the AJAX call if there are unread notifications
                                <?php if ($totalUnread > 0): ?>
                                    $.ajax({
                                        url: 'mark-admin-notifications-read.php',
                                        type: 'POST',
                                        success: function(response) {
                                            // Remove the notification badge
                                            $('.notification-badge').fadeOut();
                                        }
                                    });
                                <?php endif; ?>
                            });
                        });
                        </script>

                        <div class="form-inline">

                        </div>


                    </div>

                    <div class="user-area dropdown float-right">
                        <a href="#" class="dropdown-toggle active" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="user-avatar rounded-circle" src="images/images.png" alt="User Avatar">
                            <span class="admin-name ml-2"><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></span>
                        </a>

                        <div class="user-menu dropdown-menu">
                            <a class="nav-link" href="admin-profile.php"><i class="fa fa-user"></i>My Profile</a>

                            <a class="nav-link" href="change-password.php"><i class="fa fa-cog"></i>Change Password</a>

                            <a class="nav-link" href="logout.php"><i class="fa fa-power-off"></i>Logout</a>
                        </div>
                    </div>

                </div>
            </div>
        </header>

<style>
.notification-dropdown {
    margin-right: 15px;
}

.notification-dropdown .dropdown-toggle {
    color: #333;
    text-decoration: none;
    position: relative;
    padding: 8px 12px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.notification-dropdown .dropdown-toggle:hover {
    background: #f8f9fa;
    color: #007bff;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 1px 5px;
    font-size: 10px;
    font-weight: bold;
    min-width: 16px;
    height: 16px;
    line-height: 14px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.5);
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.notification-menu {
    min-width: 300px;
    max-height: 400px;
    overflow-y: auto;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    border: none;
    border-radius: 10px;
}

.notification-menu .dropdown-header {
    background: #007bff;
    color: white;
    font-weight: 600;
    padding: 12px 20px;
    margin: 0;
    border-radius: 10px 10px 0 0;
}

.notification-menu .dropdown-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f8f9fa;
    transition: all 0.3s ease;
}

.notification-menu .dropdown-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.notification-menu .dropdown-item:last-child {
    border-bottom: none;
    background: #f8f9fa;
    font-weight: 600;
    color: #007bff;
}

.notification-menu .dropdown-item:last-child:hover {
    background: #e9ecef;
}
</style>