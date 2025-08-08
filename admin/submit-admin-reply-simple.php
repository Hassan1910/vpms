<?php
session_start();
include('includes/dbconnection.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check admin session
if (!isset($_SESSION['vpmsaid']) || strlen($_SESSION['vpmsaid']) == 0) {
    echo json_encode(['success' => false, 'message' => 'Admin session not found']);
    exit();
}

// Check database connection
if (!$con) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$adminid = $_SESSION['vpmsaid'];

// Handle reply submission
if (isset($_POST['submit_reply'])) {
    $feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);
    $reply_message = mysqli_real_escape_string($con, $_POST['reply_message']);
    
    if (!empty($reply_message)) {
        $query = "INSERT INTO tblfeedback_replies (FeedbackID, SenderType, SenderID, Message) VALUES ('$feedback_id', 'Admin', '$adminid', '$reply_message')";
        
        if (mysqli_query($con, $query)) {
            // Update feedback status and timestamp
            $updateQuery = "UPDATE tblfeedback SET Status='In Progress', UpdatedDate=NOW() WHERE ID='$feedback_id'";
            mysqli_query($con, $updateQuery);
            
            echo json_encode(['success' => true, 'message' => 'Reply sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please enter a reply message']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
