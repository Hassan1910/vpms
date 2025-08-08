<?php
session_start();
include('includes/dbconnection.php');

if (!isset($_SESSION['vpmsaid']) || strlen($_SESSION['vpmsaid'])==0) {
    echo "Unauthorized access";
    exit();
}

$feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);

// Mark user replies as read
mysqli_query($con, "UPDATE tblfeedback_replies SET IsRead=1 WHERE FeedbackID='$feedback_id' AND SenderType='User'");

// Get all replies for this feedback
$repliesQuery = "SELECT fr.*, 
                        CASE 
                            WHEN fr.SenderType = 'User' THEN CONCAT(u.FirstName, ' ', u.LastName)
                            WHEN fr.SenderType = 'Admin' THEN a.AdminName
                        END as SenderName
                 FROM tblfeedback_replies fr
                 LEFT JOIN tblregusers u ON fr.SenderID = u.ID AND fr.SenderType = 'User'
                 LEFT JOIN tbladmin a ON fr.SenderID = a.ID AND fr.SenderType = 'Admin'
                 WHERE fr.FeedbackID = '$feedback_id'
                 ORDER BY fr.CreatedDate ASC";

$repliesResult = mysqli_query($con, $repliesQuery);

if (mysqli_num_rows($repliesResult) > 0) {
    echo '<div class="conversation-history">';
    echo '<h6 class="mb-3"><i class="fas fa-history me-2"></i>Conversation History</h6>';
    
    while ($reply = mysqli_fetch_array($repliesResult)) {
        $isAdmin = $reply['SenderType'] == 'Admin';
        $alignClass = $isAdmin ? 'text-right' : 'text-left';
        $bgClass = $isAdmin ? 'bg-primary text-white' : 'bg-light';
        $icon = $isAdmin ? 'fas fa-user-shield' : 'fas fa-user';
        
        echo '<div class="message-item mb-3 ' . $alignClass . '">';
        echo '<div class="message-bubble ' . $bgClass . ' p-3 rounded" style="display: inline-block; max-width: 80%;">';
        echo '<div class="message-header mb-2">';
        echo '<small class="font-weight-bold">';
        echo '<i class="' . $icon . ' me-1"></i>' . htmlspecialchars($reply['SenderName'] ?? ($isAdmin ? 'Administrator' : 'User'));
        echo '</small>';
        echo '<small class="ml-2 ' . ($isAdmin ? 'text-white-50' : 'text-muted') . '">';
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
    echo '</div>';
}
?>

<style>
.conversation-history {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background: #f8f9fa;
}

.message-item {
    margin-bottom: 15px;
}

.message-bubble {
    border-radius: 15px !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.message-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    padding-bottom: 5px;
    margin-bottom: 8px;
}

.bg-primary .message-header {
    border-bottom-color: rgba(255,255,255,0.2);
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
</style>
