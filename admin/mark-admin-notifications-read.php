<?php
session_start();
include('includes/dbconnection.php');

if (strlen($_SESSION['vpmsaid']) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Mark all user replies as read
$updateRepliesQuery = "UPDATE tblfeedback_replies SET IsRead = 1 WHERE SenderType = 'User' AND IsRead = 0";
$repliesResult = mysqli_query($con, $updateRepliesQuery);

// Update feedback status for open feedback (optional, depending on business logic)
// This could be removed if viewing notifications shouldn't change feedback status
// $updateFeedbackQuery = "UPDATE tblfeedback SET Status = 'In Progress' WHERE Status = 'Open'";
// $feedbackResult = mysqli_query($con, $updateFeedbackQuery);

if ($repliesResult) {
    echo json_encode(['status' => 'success', 'message' => 'Notifications marked as read']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to mark notifications as read: ' . mysqli_error($con)]);
}
?>