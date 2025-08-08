<?php
session_start();
include('includes/dbconnection.php');

if (!isset($_SESSION['vpmsuid']) || strlen($_SESSION['vpmsuid']) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$uid = $_SESSION['vpmsuid'];

// Mark all admin replies as read for this user
$updateQuery = "UPDATE tblfeedback_replies fr 
               JOIN tblfeedback f ON fr.FeedbackID = f.ID 
               SET fr.IsRead = 1 
               WHERE f.UserID = '$uid' AND fr.SenderType = 'Admin' AND fr.IsRead = 0";

$result = mysqli_query($con, $updateQuery);

if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'Notifications marked as read']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to mark notifications as read: ' . mysqli_error($con)]);
}
?>