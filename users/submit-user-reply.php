<?php
session_start();
include('includes/dbconnection.php');

// Set content type to JSON
header('Content-Type: application/json');

if (strlen($_SESSION['vpmsuid'])==0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$uid = $_SESSION['vpmsuid'];

// Handle reply submission
if (isset($_POST['submit_reply'])) {
    $feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);
    $reply_message = mysqli_real_escape_string($con, $_POST['reply_message']);
    
    // Verify that this feedback belongs to the current user
    $verifyQuery = "SELECT ID FROM tblfeedback WHERE ID='$feedback_id' AND UserID='$uid'";
    $verifyResult = mysqli_query($con, $verifyQuery);
    
    if (mysqli_num_rows($verifyResult) == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid feedback ID']);
        exit();
    }
    
    if (!empty($reply_message)) {
        // Insert reply
        $query = "INSERT INTO tblfeedback_replies (FeedbackID, SenderType, SenderID, Message) 
                  VALUES ('$feedback_id', 'User', '$uid', '$reply_message')";
        
        if (mysqli_query($con, $query)) {
            // Update feedback status to 'In Progress' if it was 'Resolved'
            mysqli_query($con, "UPDATE tblfeedback SET Status='In Progress', UpdatedDate=NOW() WHERE ID='$feedback_id' AND UserID='$uid'");
            echo json_encode(['success' => true, 'message' => 'Reply sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send reply.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please enter a reply message.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
