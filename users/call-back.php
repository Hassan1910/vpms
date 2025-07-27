<?php
require_once __DIR__ . '/../vendor/autoload.php';
include('paystack.php');

// DB connection
$con = mysqli_connect("localhost", "root", "", "vpmsdb");
if (mysqli_connect_errno()) {
    http_response_code(500);
    echo "DB connection failed.";
    exit;
}

// Get Paystack event
$input = @file_get_contents("php://input");

// Verify webhook signature if you have set up Paystack webhook secret
// $signature = (isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '');
// if (!$signature || !hash_equals($signature, hash_hmac('sha512', $input, 'your_secret_key'))) {
//     http_response_code(400);
//     exit();
// }

$event = json_decode($input, true);

// Verify the event is from Paystack
if (!isset($event['event'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid webhook payload']);
    exit;
}

// Handle the event
if ($event['event'] === 'charge.success') {
    $data = $event['data'];
    $reference = $data['reference'];
    $status = $data['status'];
    
    // Only process successful payments
    if ($status === 'success') {
        // Update payment record in database
        $stmt = $con->prepare("
            UPDATE payment
            SET
                status = 'paid',
                remarks = ?
            WHERE mpesa_checkout_id = ?
        ");
        
        $remarks = "Payment successful via Paystack. Reference: {$reference}";
        $stmt->bind_param("ss", $remarks, $reference);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Payment updated successfully"
            ]);
        } else {
            http_response_code(200); // Still 200 to avoid retries
            echo json_encode([
                "status" => "success",
                "message" => "Callback received but no matching payment found"
            ]);
        }
        
        $stmt->close();
    }
} else {
    // For other events, just acknowledge receipt
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Webhook received']);
}

$con->close();
?>
