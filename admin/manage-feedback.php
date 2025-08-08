<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['vpmsaid']) || strlen($_SESSION['vpmsaid'])==0) {
    header('location:logout.php');
    exit();
}

$adminid = $_SESSION['vpmsaid'];
$msg = "";
$error = "";

// Handle status update
if (isset($_POST['update_status'])) {
    $feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);
    $new_status = mysqli_real_escape_string($con, $_POST['status']);
    
    $query = "UPDATE tblfeedback SET Status='$new_status', UpdatedDate=NOW() WHERE ID='$feedback_id'";
    if (mysqli_query($con, $query)) {
        $msg = "Status updated successfully!";
    } else {
        $error = "Failed to update status.";
    }
}

// Handle admin response
if (isset($_POST['submit_response'])) {
    $feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);
    $response = mysqli_real_escape_string($con, $_POST['admin_response']);
    
    if (!empty($response)) {
        // Update feedback with admin response
        $query = "UPDATE tblfeedback SET AdminResponse='$response', AdminID='$adminid', AdminResponseDate=NOW(), Status='Resolved', UpdatedDate=NOW() WHERE ID='$feedback_id'";
        
        if (mysqli_query($con, $query)) {
            // Also add to replies table
            $replyQuery = "INSERT INTO tblfeedback_replies (FeedbackID, SenderType, SenderID, Message) VALUES ('$feedback_id', 'Admin', '$adminid', '$response')";
            mysqli_query($con, $replyQuery);
            
            $msg = "Response sent successfully!";
        } else {
            $error = "Failed to send response.";
        }
    } else {
        $error = "Please enter a response.";
    }
}

// Handle reply submission (for non-AJAX requests - fallback)
if (isset($_POST['submit_reply']) && !isset($_POST['ajax_request'])) {
    $feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);
    $reply_message = mysqli_real_escape_string($con, $_POST['reply_message']);

    if (!empty($reply_message)) {
        $query = "INSERT INTO tblfeedback_replies (FeedbackID, SenderType, SenderID, Message) VALUES ('$feedback_id', 'Admin', '$adminid', '$reply_message')";

        if (mysqli_query($con, $query)) {
            // Update feedback status and timestamp
            mysqli_query($con, "UPDATE tblfeedback SET Status='In Progress', UpdatedDate=NOW() WHERE ID='$feedback_id'");
            $msg = "Reply sent successfully!";
            // Store the feedback ID to auto-open conversation
            $auto_open_conversation = $feedback_id;
        } else {
            $error = "Failed to send reply.";
        }
    } else {
        $error = "Please enter a reply message.";
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build query with filters
$whereClause = "WHERE 1=1";
if (!empty($status_filter)) {
    $whereClause .= " AND f.Status = '" . mysqli_real_escape_string($con, $status_filter) . "'";
}
if (!empty($priority_filter)) {
    $whereClause .= " AND f.Priority = '" . mysqli_real_escape_string($con, $priority_filter) . "'";
}
if (!empty($category_filter)) {
    $whereClause .= " AND f.Category = '" . mysqli_real_escape_string($con, $category_filter) . "'";
}

// Get feedback with user information
$feedbackQuery = "SELECT f.*, 
                         CONCAT(u.FirstName, ' ', u.LastName) as UserName,
                         u.Email as UserEmail,
                         (SELECT COUNT(*) FROM tblfeedback_replies WHERE FeedbackID = f.ID) as reply_count,
                         (SELECT COUNT(*) FROM tblfeedback_replies WHERE FeedbackID = f.ID AND SenderType = 'User' AND IsRead = 0) as unread_user_replies
                  FROM tblfeedback f 
                  JOIN tblregusers u ON f.UserID = u.ID 
                  $whereClause
                  ORDER BY f.UpdatedDate DESC";
$feedbackResult = mysqli_query($con, $feedbackQuery);

// Get statistics
$statsQuery = "SELECT 
                   COUNT(*) as total,
                   SUM(CASE WHEN Status = 'Open' THEN 1 ELSE 0 END) as open_count,
                   SUM(CASE WHEN Status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_count,
                   SUM(CASE WHEN Status = 'Resolved' THEN 1 ELSE 0 END) as resolved_count,
                   SUM(CASE WHEN Status = 'Closed' THEN 1 ELSE 0 END) as closed_count
               FROM tblfeedback";
$statsResult = mysqli_query($con, $statsQuery);
$stats = mysqli_fetch_array($statsResult);
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - Manage Feedback</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <style>
        .stats-card {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-shadow: 0 2px 3px rgba(0,0,0,0.4);
        }
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 0 2px 3px rgba(0,0,0,0.4);
        }
        .stats-card p {
            font-size: 1.1rem;
            font-weight: 600;
            text-shadow: 0 2px 3px rgba(0,0,0,0.4);
        }
        .feedback-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .feedback-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .feedback-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 20px;
        }
        .feedback-body {
            padding: 20px;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-open { background: #ffc107; color: #000; }
        .status-in-progress { background: #17a2b8; color: #fff; }
        .status-resolved { background: #28a745; color: #fff; }
        .status-closed { background: #6c757d; color: #fff; }
        .priority-badge {
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
        }
        .priority-low { background: #d4edda; color: #155724; }
        .priority-medium { background: #fff3cd; color: #856404; }
        .priority-high { background: #f8d7da; color: #721c24; }
        .priority-critical { background: #721c24; color: #fff; }
        .filter-section {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .unread-indicator {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
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
                            <h1>Manage Feedback</h1>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="page-header float-right">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li class="active">Manage Feedback</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="animated fadeIn">
            <?php if($msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $msg; ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #3a3a3a, #1a1a1a); color: #fff;">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p class="mb-0">Total Feedback</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #ffc107, #d39e00); color: #000;">
                        <h3 style="color: #000; text-shadow: 0 1px 2px rgba(255,255,255,0.5);"><?php echo $stats['open_count']; ?></h3>
                        <p class="mb-0" style="color: #000; text-shadow: 0 1px 2px rgba(255,255,255,0.5);">Open</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #0062cc, #004085); color: #fff;">
                        <h3><?php echo $stats['in_progress_count']; ?></h3>
                        <p class="mb-0">In Progress</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #218838, #1e7e34); color: #fff;">
                        <h3><?php echo $stats['resolved_count']; ?></h3>
                        <p class="mb-0">Resolved</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <h5><i class="fas fa-filter me-2"></i>Filter Feedback</h5>
                <form method="GET" class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Open" <?php echo $status_filter == 'Open' ? 'selected' : ''; ?>>Open</option>
                            <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Resolved" <?php echo $status_filter == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="Closed" <?php echo $status_filter == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="priority" class="form-control">
                            <option value="">All Priority</option>
                            <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>Low</option>
                            <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>High</option>
                            <option value="Critical" <?php echo $priority_filter == 'Critical' ? 'selected' : ''; ?>>Critical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <option value="General" <?php echo $category_filter == 'General' ? 'selected' : ''; ?>>General</option>
                            <option value="Bug Report" <?php echo $category_filter == 'Bug Report' ? 'selected' : ''; ?>>Bug Report</option>
                            <option value="Feature Request" <?php echo $category_filter == 'Feature Request' ? 'selected' : ''; ?>>Feature Request</option>
                            <option value="Payment Issue" <?php echo $category_filter == 'Payment Issue' ? 'selected' : ''; ?>>Payment Issue</option>
                            <option value="Booking Problem" <?php echo $category_filter == 'Booking Problem' ? 'selected' : ''; ?>>Booking Problem</option>
                            <option value="User Interface" <?php echo $category_filter == 'User Interface' ? 'selected' : ''; ?>>User Interface</option>
                            <option value="Other" <?php echo $category_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="manage-feedback.php" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Feedback List -->
            <div class="row">
                <div class="col-12">
                    <?php if (mysqli_num_rows($feedbackResult) > 0): ?>
                        <?php while ($feedback = mysqli_fetch_array($feedbackResult)): ?>
                            <div class="feedback-card">
                                <div class="feedback-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($feedback['Subject']); ?></h5>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($feedback['UserName']); ?> 
                                                (<?php echo htmlspecialchars($feedback['UserEmail']); ?>) | 
                                                <i class="fas fa-tag me-1"></i><?php echo $feedback['Category']; ?> | 
                                                <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y H:i', strtotime($feedback['CreatedDate'])); ?>
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $feedback['Status'])); ?>">
                                                <?php echo $feedback['Status']; ?>
                                            </span>
                                            <br>
                                            <span class="priority-badge priority-<?php echo strtolower($feedback['Priority']); ?> mt-1">
                                                <?php echo $feedback['Priority']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="feedback-body">
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($feedback['Message'])); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-comments me-1"></i>
                                            <?php echo $feedback['reply_count']; ?> replies
                                            <?php if ($feedback['unread_user_replies'] > 0): ?>
                                                <span class="unread-indicator ml-2">
                                                    <?php echo $feedback['unread_user_replies']; ?> new
                                                </span>
                                            <?php endif; ?>
                                        </small>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="toggleActions(<?php echo $feedback['ID']; ?>)">
                                                <i class="fas fa-cog me-1"></i>Actions
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="toggleConversation(<?php echo $feedback['ID']; ?>)">
                                                <i class="fas fa-comments me-1"></i>View Conversation
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Actions Section -->
                                    <div id="actions-<?php echo $feedback['ID']; ?>" class="border-top pt-3" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <form method="post" class="mb-3">
                                                    <input type="hidden" name="feedback_id" value="<?php echo $feedback['ID']; ?>">
                                                    <div class="form-group">
                                                        <label>Update Status:</label>
                                                        <div class="input-group">
                                                            <select name="status" class="form-control">
                                                                <option value="Open" <?php echo $feedback['Status'] == 'Open' ? 'selected' : ''; ?>>Open</option>
                                                                <option value="In Progress" <?php echo $feedback['Status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                                <option value="Resolved" <?php echo $feedback['Status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                                <option value="Closed" <?php echo $feedback['Status'] == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                                            </select>
                                                            <div class="input-group-append">
                                                                <button type="submit" name="update_status" class="btn btn-outline-primary">Update</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if (!$feedback['AdminResponse']): ?>
                                                    <form method="post">
                                                        <input type="hidden" name="feedback_id" value="<?php echo $feedback['ID']; ?>">
                                                        <div class="form-group">
                                                            <label>Admin Response:</label>
                                                            <textarea name="admin_response" class="form-control" rows="3" placeholder="Type your response here..." required></textarea>
                                                        </div>
                                                        <button type="submit" name="submit_response" class="btn btn-success btn-sm">
                                                            <i class="fas fa-reply me-1"></i>Send Response
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Conversation Section -->
                                    <div id="conversation-<?php echo $feedback['ID']; ?>" class="border-top pt-3" style="display: none;">
                                        <div id="conversation-content-<?php echo $feedback['ID']; ?>">
                                            <!-- Conversation will be loaded here -->
                                        </div>
                                        
                                        <form method="post" class="mt-3" id="admin-reply-form-<?php echo $feedback['ID']; ?>" onsubmit="return submitAdminReply(<?php echo $feedback['ID']; ?>, event)">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['ID']; ?>">
                                            <div class="form-group">
                                                <textarea class="form-control" name="reply_message" rows="3"
                                                          placeholder="Type your reply here..." required id="reply-textarea-<?php echo $feedback['ID']; ?>"></textarea>
                                            </div>
                                            <button type="submit" name="submit_reply" class="btn btn-sm btn-primary">
                                                <i class="fas fa-reply me-1"></i>Send Reply
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary ml-2" onclick="refreshAdminConversation(<?php echo $feedback['ID']; ?>)">
                                                <i class="fas fa-sync-alt me-1"></i>Refresh
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No feedback found</h4>
                            <p class="text-muted">No feedback matches your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <!-- jQuery stub to prevent errors from external libraries -->
    <script>
        // Enhanced jQuery stub to prevent errors
        if (typeof $ === 'undefined') {
            window.$ = window.jQuery = function(selector) {
                return {
                    ready: function(fn) {
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', fn);
                        } else {
                            fn();
                        }
                        return this;
                    },
                    on: function() { return this; },
                    fadeOut: function() { return this; },
                    html: function() { return this; },
                    ajax: function() { return this; },
                    each: function() { return this; },
                    addClass: function() { return this; },
                    removeClass: function() { return this; },
                    css: function() { return this; },
                    attr: function() { return this; },
                    val: function() { return this; },
                    text: function() { return this; },
                    hide: function() { return this; },
                    show: function() { return this; },
                    length: 0
                };
            };

            // jQuery prototype and static methods
            $.fn = $.prototype = {
                ready: function(fn) { return $(document).ready(fn); },
                on: function() { return this; },
                fadeOut: function() { return this; },
                html: function() { return this; },
                each: function() { return this; },
                addClass: function() { return this; },
                removeClass: function() { return this; },
                css: function() { return this; },
                attr: function() { return this; },
                val: function() { return this; },
                text: function() { return this; },
                hide: function() { return this; },
                show: function() { return this; },
                length: 0
            };

            // Static jQuery methods
            $.ajax = function() {};
            $.each = function() {};
            $.extend = function() { return arguments[0]; };
            $.isFunction = function() { return false; };
            $.isArray = function() { return Array.isArray; };
        }
    </script>
    
    <script>

        // Auto-open conversation if reply was just submitted
        <?php if (isset($auto_open_conversation)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                toggleConversation(<?php echo $auto_open_conversation; ?>);
            }, 500);
        });
        <?php endif; ?>

        function toggleActions(feedbackId) {
            const actionsDiv = document.getElementById('actions-' + feedbackId);
            actionsDiv.style.display = actionsDiv.style.display === 'none' ? 'block' : 'none';
        }

        function toggleConversation(feedbackId) {
            const conversationDiv = document.getElementById('conversation-' + feedbackId);
            const contentDiv = document.getElementById('conversation-content-' + feedbackId);
            
            if (conversationDiv.style.display === 'none') {
                conversationDiv.style.display = 'block';
                loadAdminConversation(feedbackId);
            } else {
                conversationDiv.style.display = 'none';
            }
        }

        function loadAdminConversation(feedbackId) {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            formData.append('feedback_id', feedbackId);

            xhr.open('POST', 'load-admin-conversation.php', true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    const contentDiv = document.getElementById('conversation-content-' + feedbackId);
                    if (xhr.status === 200) {
                        contentDiv.innerHTML = xhr.responseText;
                    } else {
                        console.error('Error loading conversation:', xhr.status, xhr.statusText);
                        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading conversation. Please try again.</div>';
                    }
                }
            };

            xhr.send(formData);
        }

        function refreshAdminConversation(feedbackId) {
            loadAdminConversation(feedbackId);
        }

        function submitAdminReply(feedbackId, event) {
            event.preventDefault();

            const form = document.getElementById('admin-reply-form-' + feedbackId);
            const textarea = document.getElementById('reply-textarea-' + feedbackId);

            if (!textarea.value.trim()) {
                alert('Please enter a reply message.');
                return false;
            }

            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

            // Prepare form data
            const formData = new FormData();
            formData.append('feedback_id', form.querySelector('input[name="feedback_id"]').value);
            formData.append('reply_message', textarea.value);
            formData.append('submit_reply', '1');

            console.log('Submitting admin reply for feedback ID:', feedbackId);

            // Use vanilla JavaScript XMLHttpRequest
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit-admin-reply-simple.php', true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('Success response:', response);

                            if (response && response.success) {
                                textarea.value = '';
                                alert('Reply sent successfully!');
                                loadAdminConversation(feedbackId);
                            } else {
                                alert('Error: ' + (response ? response.message : 'Unknown error'));
                                console.log('Error response:', response);
                            }
                        } catch (e) {
                            console.error('Parse error:', e, xhr.responseText);
                            alert('Error parsing response from server');
                        }
                    } else {
                        console.error('XHR Error:', xhr.status, xhr.statusText);
                        alert('Error sending reply: ' + xhr.status + ' - ' + xhr.statusText);
                    }
                }
            };

            xhr.send(formData);
            return false;
        }
    </script>
</body>
</html>
