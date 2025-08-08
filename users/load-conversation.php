<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Set content type header
header('Content-Type: text/html; charset=utf-8');

session_start();
include('includes/dbconnection.php');

// Check if user is logged in
if (!isset($_SESSION['vpmsuid']) || strlen($_SESSION['vpmsuid']) == 0) {
    echo "<div class='alert alert-danger'>You must be logged in to view this conversation.</div>";
    ob_end_flush();
    exit();
}

$uid = $_SESSION['vpmsuid'];
$feedback_id = intval($_POST['feedback_id']);

// Verify that this feedback belongs to the current user
$verifyQuery = "SELECT ID FROM tblfeedback WHERE ID='$feedback_id' AND UserID='$uid'";
$verifyResult = mysqli_query($con, $verifyQuery);

if (mysqli_num_rows($verifyResult) == 0) {
    echo "<div class='alert alert-danger'>Invalid feedback ID</div>";
    ob_end_flush();
    exit();
}

// Mark admin replies as read
mysqli_query($con, "UPDATE tblfeedback_replies SET IsRead=1 WHERE FeedbackID='$feedback_id' AND SenderType='Admin'");

// Start output buffering to catch any errors

try {
    // Get all replies for this feedback
    $repliesQuery = "SELECT fr.*, 
                            CASE 
                                WHEN fr.SenderType = 'User' THEN 'You'
                                WHEN fr.SenderType = 'Admin' THEN 'Administrator'
                                ELSE 'Unknown'
                            END as SenderName
                     FROM tblfeedback_replies fr
                     WHERE fr.FeedbackID = '$feedback_id'
                     ORDER BY fr.CreatedDate ASC";
    
    if (!$repliesResult = mysqli_query($con, $repliesQuery)) {
        throw new Exception("Database query failed: " . mysqli_error($con));
    }
} catch (Exception $e) {
    // Clear the output buffer and show error
    ob_end_clean();
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}

if (mysqli_num_rows($repliesResult) > 0) {
    echo '<div class="conversation-history">';
    echo '<h6 class="mb-3"><i class="fas fa-history me-2"></i>Conversation History</h6>';
    
    while ($reply = mysqli_fetch_array($repliesResult)) {
        $isAdmin = $reply['SenderType'] == 'Admin';
        $alignClass = $isAdmin ? 'text-left' : 'text-right';
        $bgClass = $isAdmin ? 'bg-light' : 'bg-primary text-white';
        $icon = $isAdmin ? 'fas fa-user-shield' : 'fas fa-user';
        
        echo '<div class="message-item mb-3 ' . $alignClass . '">';
        echo '<div class="message-bubble ' . $bgClass . ' p-3 rounded" style="display: inline-block; max-width: 80%;">';
        echo '<div class="message-header mb-2">';
        echo '<small class="font-weight-bold">';
        echo '<i class="' . $icon . ' me-1"></i>' . htmlspecialchars($reply['SenderName'] ?? ($isAdmin ? 'Administrator' : 'You'));
        echo '</small>';
        echo '<small class="ml-2 ' . ($isAdmin ? 'text-muted' : 'text-white-50') . '">';
        echo date('M d, Y H:i', strtotime($reply['CreatedDate']));
        echo '</small>';
        echo '</div>';
        echo '<div class="message-content">';
        echo nl2br(htmlspecialchars($reply['Message']));
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
} else {
    echo '<div class="text-center text-muted py-3">';
    echo '<i class="fas fa-comment-slash fa-2x mb-2"></i>';
    echo '<p>No conversation history yet.</p>';
    echo '<p class="small">When you or an administrator adds a reply, it will appear here.</p>';
    echo '</div>';
}

// Send a success status header before flushing output
header('Status: 200 OK');

// Flush the output buffer
ob_end_flush();
?>

<style>
.conversation-history {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background: #f8f9fa;
    margin-bottom: 15px;
    display: block !important;
    width: 100%;
}

.message-item {
    margin-bottom: 15px;
    clear: both;
    overflow: hidden; /* Ensure floated elements are contained */
    width: 100%;
    display: block;
}

.message-item.text-right .message-bubble {
    float: right;
}

.message-item.text-left .message-bubble {
    float: left;
}

.message-bubble {
    border-radius: 15px !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    max-width: 80%;
    word-wrap: break-word;
    display: inline-block;
}

.message-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    padding-bottom: 5px;
    margin-bottom: 8px;
}

.bg-primary .message-header {
    border-bottom-color: rgba(255,255,255,0.2);
}

.message-content {
    line-height: 1.5;
}

.conversation-history::-webkit-scrollbar {
    width: 6px;
}

.conversation-history::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.conversation-history::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.conversation-history::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Fix for floating elements */
.conversation-history::after {
    content: "";
    display: table;
    clear: both;
}
</style>
