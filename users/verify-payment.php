<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include('includes/dbconnection.php');
include('paystack.php');

if (!isset($_GET['reference'])) {
    die("Missing reference parameter");
}

$reference = $_GET['reference'];
$verification = verifyPaystackPayment($reference);

if ($verification['status']) {
    // Payment was successful
    $data = $verification['data'];
    // Check if metadata is already an object or a JSON string
    if (is_string($data->metadata)) {
        $metadata = json_decode($data->metadata, true);
    } else {
        // If it's already an object, convert it to array
        $metadata = json_decode(json_encode($data->metadata), true);
    }
    
    // Extract booking ID and payment ID from metadata
    $bookingId = $metadata['booking_id'] ?? null;
    $paymentId = $metadata['payment_id'] ?? null;
    $userId = $metadata['user_id'] ?? null;
    
    if ($paymentId) {
        // Update payment record
        $remarks = "Payment verified via Paystack. Reference: {$reference}";
        $stmt = $con->prepare("UPDATE payment SET status = 'paid', mpesa_checkout_id = ?, remarks = ?, created_at = NOW() WHERE id = ?");
        
        // Check if prepare statement was successful
        if ($stmt === false) {
            // Log the error and use a direct query as fallback
            error_log("Prepare statement failed: " . $con->error);
            $updateResult = mysqli_query($con, "UPDATE payment SET status = 'paid', mpesa_checkout_id = '$reference', remarks = '$remarks', created_at = NOW() WHERE id = $paymentId");
            $updateSuccess = $updateResult ? true : false;
        } else {
            $stmt->bind_param("ssi", $reference, $remarks, $paymentId);
            $stmt->execute();
            $updateSuccess = ($stmt->affected_rows > 0 || $stmt->affected_rows === 0);
            $stmt->close();
        }
        
        // Update booking status if it exists
        if ($bookingId) {
            $updateBooking = $con->prepare("UPDATE bookings SET payment_status = 'Paid' WHERE id = ?");
            if ($updateBooking === false) {
                // Log the error and use a direct query as fallback
                error_log("Prepare statement failed for booking update: " . $con->error);
                $updateBookingResult = mysqli_query($con, "UPDATE bookings SET payment_status = 'Paid' WHERE id = $bookingId");
            } else {
                $updateBooking->bind_param("i", $bookingId);
                $updateBooking->execute();
                $updateBooking->close();
            }
        }
        
        if ($updateSuccess) {
            $_SESSION['payment_success'] = true;
            $_SESSION['payment_message'] = "Payment successful! Your transaction reference is {$reference}";
            $_SESSION['payment_reference'] = $reference;
            $_SESSION['last_payment_id'] = $paymentId;
        } else {
            $_SESSION['payment_error'] = true;
            $_SESSION['payment_message'] = "Payment verified but database update failed.";
        }
    } else {
        $_SESSION['payment_error'] = true;
        $_SESSION['payment_message'] = "Payment verified but missing payment ID in metadata.";
    }
} else {
    // Payment verification failed
    $_SESSION['payment_error'] = true;
    $_SESSION['payment_message'] = $verification['message'] ?? "Payment verification failed.";
}

// Redirect to appropriate page with message
if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']) {
    // Redirect to receipt page
    header("Location: receipt.php?pk=" . $paymentId . "&success=1");
} else {
    // Redirect back to dashboard with error
    header("Location: dashboard.php?payment_error=1");
}
exit;
?>