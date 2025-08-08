<div id="right-panel" class="right-panel">
<header id="header" class="header">
            <div class="top-left">
                <div class="navbar-header" style="padding-top: 10px;">
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
                        if (isset($_SESSION['vpmsuid'])) {
                            $uid = $_SESSION['vpmsuid'];

                            // Get unread admin replies count
                            $unreadRepliesQuery = "SELECT COUNT(*) as unread_replies
                                                   FROM tblfeedback_replies fr
                                                   JOIN tblfeedback f ON fr.FeedbackID = f.ID
                                                   WHERE f.UserID = '$uid' AND fr.SenderType = 'Admin' AND fr.IsRead = 0";
                            $unreadRepliesResult = mysqli_query($con, $unreadRepliesQuery);

                            if ($unreadRepliesResult) {
                                $unreadReplies = mysqli_fetch_array($unreadRepliesResult);
                            } else {
                                // Query failed, log error and set default values
                                error_log("Failed to execute unread replies query: " . mysqli_error($con));
                                $unreadReplies = array('unread_replies' => 0);
                            }

                            // Get feedback with admin responses
                            $respondedFeedbackQuery = "SELECT COUNT(*) as responded_count
                                                       FROM tblfeedback
                                                       WHERE UserID = '$uid' AND AdminResponse IS NOT NULL AND Status = 'Resolved'";
                            $respondedFeedbackResult = mysqli_query($con, $respondedFeedbackQuery);

                            if ($respondedFeedbackResult) {
                                $respondedFeedback = mysqli_fetch_array($respondedFeedbackResult);
                            } else {
                                // Query failed, log error and set default values
                                error_log("Failed to execute responded feedback query: " . mysqli_error($con));
                                $respondedFeedback = array('responded_count' => 0);
                            }

                            $totalUnread = $unreadReplies['unread_replies'];
                        } else {
                            $totalUnread = 0;
                            $unreadReplies = array('unread_replies' => 0);
                            $respondedFeedback = array('responded_count' => 0);
                        }
                        ?>

                        <div class="dropdown notification-dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="notificationDropdown">
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

                                <?php if ($unreadReplies['unread_replies'] > 0): ?>
                                    <a class="dropdown-item" href="my-feedback.php">
                                        <i class="fas fa-reply text-info me-2"></i>
                                        <?php echo $unreadReplies['unread_replies']; ?> new admin replies
                                    </a>
                                <?php endif; ?>

                                <?php if ($totalUnread == 0): ?>
                                    <div class="dropdown-item text-muted text-center">
                                        <i class="fas fa-check-circle me-2"></i>No new notifications
                                    </div>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="my-feedback.php">
                                    <i class="fas fa-eye me-2"></i>View My Feedback
                                </a>
                            </div>
                        </div>
                        
                        <script>
                        // Mark notifications as read when dropdown is opened
                        document.addEventListener('DOMContentLoaded', function() {
                            const notificationDropdown = document.getElementById('notificationDropdown');
                            if (notificationDropdown) {
                                notificationDropdown.addEventListener('click', function() {
                                    // Only make the AJAX call if there are unread notifications
                                    <?php if ($totalUnread > 0): ?>
                                        const xhr = new XMLHttpRequest();
                                        xhr.open('POST', 'mark-notifications-read.php', true);
                                        xhr.onreadystatechange = function() {
                                            if (xhr.readyState === 4 && xhr.status === 200) {
                                                // Remove the notification badge
                                                const badge = document.querySelector('.notification-badge');
                                                if (badge) {
                                                    badge.style.display = 'none';
                                                }
                                            }
                                        };
                                        xhr.send();
                                    <?php endif; ?>
                                });
                            }
                        });
                        </script>

                        <div class="form-inline">

                        </div>


                    </div>

                    <div class="user-area dropdown float-right">
                        <a href="#" class="dropdown-toggle active" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="user-avatar rounded-circle" src="../admin/images/images.png" alt="User Avatar">
                        </a>

                        <div class="user-menu dropdown-menu">
                            <a class="nav-link" href="profile.php"><i class="fa fa-user"></i>My Profile</a>

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