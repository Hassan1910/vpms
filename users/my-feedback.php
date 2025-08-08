<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['vpmsuid']) || strlen($_SESSION['vpmsuid']) == 0) {
    header('location:logout.php');
    exit();
}

$uid = $_SESSION['vpmsuid'];
$msg = "";
$error = "";

// Handle reply submission (for non-AJAX requests - fallback)
if (isset($_POST['submit_reply']) && !isset($_POST['ajax_request'])) {
    $feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);
    $reply_message = mysqli_real_escape_string($con, $_POST['reply_message']);

    // Verify that this feedback belongs to the current user
    $verifyQuery = "SELECT ID FROM tblfeedback WHERE ID='$feedback_id' AND UserID='$uid'";
    $verifyResult = mysqli_query($con, $verifyQuery);

    if (mysqli_num_rows($verifyResult) > 0) {
        if (!empty($reply_message)) {
            // Insert reply
            $query = "INSERT INTO tblfeedback_replies (FeedbackID, SenderType, SenderID, Message)
                      VALUES ('$feedback_id', 'User', '$uid', '$reply_message')";

            if (mysqli_query($con, $query)) {
                // Update feedback status to 'In Progress' if it was 'Resolved'
                mysqli_query($con, "UPDATE tblfeedback SET Status='In Progress', UpdatedDate=NOW() WHERE ID='$feedback_id' AND UserID='$uid'");
                $msg = "Reply sent successfully!";
                // Store the feedback ID to auto-open conversation
                $auto_open_conversation = $feedback_id;
            } else {
                $error = "Failed to send reply. Please try again.";
            }
        } else {
            $error = "Please enter a reply message.";
        }
    } else {
        $error = "Invalid feedback ID.";
    }
}

// Get user feedback with reply counts
$feedbackQuery = "SELECT f.*, 
                         (SELECT COUNT(*) FROM tblfeedback_replies WHERE FeedbackID = f.ID) as reply_count,
                         (SELECT COUNT(*) FROM tblfeedback_replies WHERE FeedbackID = f.ID AND SenderType = 'Admin' AND IsRead = 0) as unread_admin_replies
                  FROM tblfeedback f 
                  WHERE f.UserID = '$uid' 
                  ORDER BY f.UpdatedDate DESC";
$feedbackResult = mysqli_query($con, $feedbackQuery);
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - My Feedback</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <style>
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
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
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
        .reply-section {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 15px 20px;
            display: none; /* Ensure initially hidden */
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
                            <h1>My Feedback</h1>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="page-header float-right">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li class="active">My Feedback</li>
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

            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="fas fa-comments me-2"></i>My Feedback History</h3>
                        <a href="feedback.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Submit New Feedback
                        </a>
                    </div>

                    <?php if (mysqli_num_rows($feedbackResult) > 0): ?>
                        <?php while ($feedback = mysqli_fetch_array($feedbackResult)): ?>
                            <div class="feedback-card">
                                <div class="feedback-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($feedback['Subject']); ?></h5>
                                            <small>
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
                                    
                                    <?php if ($feedback['AdminResponse']): ?>
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-user-shield me-2"></i>Admin Response:</h6>
                                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($feedback['AdminResponse'])); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('M d, Y H:i', strtotime($feedback['AdminResponseDate'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-comments me-1"></i>
                                            <?php echo $feedback['reply_count']; ?> replies
                                            <?php if ($feedback['unread_admin_replies'] > 0): ?>
                                                <span class="unread-indicator ml-2">
                                                    <?php echo $feedback['unread_admin_replies']; ?> new
                                                </span>
                                            <?php endif; ?>
                                        </small>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="toggleReplies(<?php echo $feedback['ID']; ?>)">
                                                <i class="fas fa-comments me-1"></i>View Conversation
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="replies-<?php echo $feedback['ID']; ?>" class="reply-section">
                                    <div id="conversation-<?php echo $feedback['ID']; ?>" class="mb-3">
                                        <!-- Conversation will be loaded here -->
                                    </div>
                                    
                                    <?php if ($feedback['Status'] != 'Closed'): ?>
                                        <form method="post" class="mt-3" id="reply-form-<?php echo $feedback['ID']; ?>" onsubmit="return submitUserReply(<?php echo $feedback['ID']; ?>, event)">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['ID']; ?>">
                                            <div class="form-group">
                                                <textarea class="form-control" name="reply_message" rows="3"
                                                          placeholder="Type your reply here..." required id="user-reply-textarea-<?php echo $feedback['ID']; ?>"></textarea>
                                            </div>
                                            <div class="form-group mt-2">
                                                <button type="submit" name="submit_reply" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-reply me-1"></i>Send Reply
                                                </button>
                                                <button type="button" class="btn btn-sm btn-secondary ml-2" onclick="refreshConversation(<?php echo $feedback['ID']; ?>)">
                                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                                </button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No feedback submitted yet</h4>
                            <p class="text-muted">Share your thoughts and help us improve!</p>
                            <a href="feedback.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Submit Your First Feedback
                            </a>
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
                toggleReplies(<?php echo $auto_open_conversation; ?>);
            }, 500);
        });
        <?php endif; ?>
        
        function toggleReplies(feedbackId) {
            const repliesDiv = document.getElementById('replies-' + feedbackId);
            const conversationDiv = document.getElementById('conversation-' + feedbackId);
            
            // Check if the element is currently visible
            const isVisible = window.getComputedStyle(repliesDiv).display !== 'none';
            
            // Close all other open conversations first
            document.querySelectorAll('.reply-section').forEach(function(section) {
                if (section.id !== 'replies-' + feedbackId) {
                    section.style.display = 'none';
                }
            });
            
            // Toggle the current conversation
            if (isVisible) {
                repliesDiv.style.display = 'none';
            } else {
                repliesDiv.style.display = 'block';
                loadConversation(feedbackId);
            }
        }

        function refreshConversation(feedbackId) {
            loadConversation(feedbackId);
        }

        function submitUserReply(feedbackId, event) {
            event.preventDefault();

            const form = document.getElementById('reply-form-' + feedbackId);
            const formData = new FormData(form);
            const textarea = document.getElementById('user-reply-textarea-' + feedbackId);

            if (!textarea.value.trim()) {
                alert('Please enter a reply message.');
                return false;
            }

            // Manually add the submit_reply parameter since FormData doesn't include submit button values
            formData.append('submit_reply', '1');

            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

            // Use vanilla JavaScript XMLHttpRequest
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit-user-reply.php', true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (response.success) {
                                // Clear the textarea
                                textarea.value = '';

                                // Show success message
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-2';
                                alertDiv.innerHTML = '<i class="fas fa-check me-1"></i>' + response.message + ' <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
                                form.appendChild(alertDiv);

                                // Auto-dismiss alert after 3 seconds
                                setTimeout(() => {
                                    if (alertDiv.parentNode) {
                                        alertDiv.remove();
                                    }
                                }, 3000);

                                // Refresh the conversation to show the new reply
                                setTimeout(() => {
                                    loadConversation(feedbackId);
                                }, 500);
                            } else {
                                // Show error message from server
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
                                alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>' + response.message + ' <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
                                form.appendChild(alertDiv);
                            }
                        } catch (e) {
                            console.error('Parse error:', e, xhr.responseText);
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
                            alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error parsing response. Please try again. <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
                            form.appendChild(alertDiv);
                        }
                    } else {
                        console.error('XHR Error:', xhr.status, xhr.statusText);
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error sending reply: ' + xhr.status + ' - ' + xhr.statusText + ' <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
                        form.appendChild(alertDiv);
                    }
                }
            };

            xhr.send(formData);
            return false;
        }

        function loadConversation(feedbackId) {
            const conversationDiv = document.getElementById('conversation-' + feedbackId);
            conversationDiv.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading conversation...</div>';

            // Add a timestamp to prevent caching
            const timestamp = new Date().getTime();

            // Use vanilla JavaScript XMLHttpRequest
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            formData.append('feedback_id', feedbackId);

            xhr.open('POST', 'load-conversation.php?t=' + timestamp, true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        console.log('Conversation loaded successfully');
                        conversationDiv.innerHTML = xhr.responseText;
                    } else {
                        console.error('Error loading conversation:', xhr.status, xhr.statusText);
                        conversationDiv.innerHTML = '<div class="alert alert-danger">Error loading conversation: ' + xhr.status + '. Please try again or refresh the page.</div>';
                    }
                }
            };

            xhr.send(formData);
        }
    </script>
</body>
</html>
