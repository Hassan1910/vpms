<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

// Debug output
$debug = [];
$debug['session_started'] = true;
$debug['post_data'] = $_POST;
$debug['session_data'] = $_SESSION;

include('includes/dbconnection.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check database connection
if (!$con) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'debug' => $debug]);
    exit();
}
$debug['database_connected'] = true;

// Log the request for debugging
error_log("Admin reply request received: " . print_r($_POST, true));

if (!isset($_SESSION['vpmsaid']) || strlen($_SESSION['vpmsaid'])==0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - no admin session', 'debug' => $debug]);
    exit();
}
$debug['admin_session_valid'] = true;

$adminid = $_SESSION['vpmsaid'];
$debug['admin_id'] = $adminid;

// Handle reply submission
if (isset($_POST['submit_reply'])) {
    $debug['submit_reply_set'] = true;

    $feedback_id = mysqli_real_escape_string($con, $_POST['feedback_id']);
    $reply_message = mysqli_real_escape_string($con, $_POST['reply_message']);

    $debug['feedback_id'] = $feedback_id;
    $debug['reply_message_length'] = strlen($reply_message);

    if (!empty($reply_message)) {
        $debug['reply_message_not_empty'] = true;

        $query = "INSERT INTO tblfeedback_replies (FeedbackID, SenderType, SenderID, Message) VALUES ('$feedback_id', 'Admin', '$adminid', '$reply_message')";
        $debug['query'] = $query;

        if (mysqli_query($con, $query)) {
            $debug['insert_successful'] = true;

            // Update feedback status and timestamp
            $updateQuery = "UPDATE tblfeedback SET Status='In Progress', UpdatedDate=NOW() WHERE ID='$feedback_id'";
            $updateResult = mysqli_query($con, $updateQuery);
            $debug['update_result'] = $updateResult;

            error_log("Admin reply inserted successfully for feedback ID: $feedback_id");
            echo json_encode(['success' => true, 'message' => 'Reply sent successfully!', 'debug' => $debug]);
        } else {
            $error = mysqli_error($con);
            $debug['insert_error'] = $error;
            error_log("Database error inserting admin reply: " . $error);
            echo json_encode(['success' => false, 'message' => 'Failed to send reply: ' . $error, 'debug' => $debug]);
        }
    } else {
        $debug['reply_message_empty'] = true;
        echo json_encode(['success' => false, 'message' => 'Please enter a reply message.', 'debug' => $debug]);
    }
} else {
    $debug['submit_reply_not_set'] = true;
    echo json_encode(['success' => false, 'message' => 'Invalid request - submit_reply not set.', 'debug' => $debug]);
}
?>
