<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Yabacon\Paystack;

function initializePaystack() {
    // Replace with your actual Paystack secret key
    // Secret key must start with 'sk_' (not 'pk_')
    // You provided a public key (pk_test_...) but we need a secret key (sk_test_...)
    // Get your secret key from your Paystack dashboard: Settings -> API Keys & Webhooks -> API Configuration - Test Mode -> Test Secret Key
    $secretKey = "sk_test_36c2a669d1feb76b51dd0bff57eccdfebea18350"; // Replace with your secret key that starts with sk_test_
    return new Paystack($secretKey);
}

function initiatePaystackPayment($email, $amount, $reference, $metadata = []) {
    try {
        $paystack = initializePaystack();
        
        // Amount needs to be in cents (multiply by 100 for KES)
        $amountInCents = $amount * 100;
        
        $transactionData = [
            'amount' => $amountInCents, 
            'email' => $email,
            'reference' => $reference,
            'currency' => 'KES',
            'callback_url' => "http://localhost/vpms/users/verify-payment.php?reference=" . $reference,
            'metadata' => json_encode($metadata)
        ];
        
        $response = $paystack->transaction->initialize($transactionData);
        
        if ($response->status) {
            return [
                'status' => true,
                'authorization_url' => $response->data->authorization_url,
                'access_code' => $response->data->access_code,
                'reference' => $response->data->reference
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to initialize transaction'
            ];
        }
    } catch (\Exception $e) {
        return [
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

function verifyPaystackPayment($reference) {
    try {
        $paystack = initializePaystack();
        $response = $paystack->transaction->verify([
            'reference' => $reference
        ]);
        
        if ($response->status && $response->data->status === 'success') {
            return [
                'status' => true,
                'data' => $response->data,
                'amount' => $response->data->amount / 100, // Convert back from cents
                'reference' => $response->data->reference,
                'transaction_date' => $response->data->transaction_date,
                'customer' => [
                    'email' => $response->data->customer->email
                ]
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Payment verification failed'
            ];
        }
    } catch (\Exception $e) {
        return [
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// If this file is accessed directly, return JSON response
if (php_sapi_name() !== 'cli' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'initialize') {
        $email = $_POST['email'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        $reference = $_POST['reference'] ?? uniqid('VPMS-');
        $metadata = $_POST['metadata'] ?? [];
        
        echo json_encode(initiatePaystackPayment($email, $amount, $reference, $metadata));
    } else {
        echo json_encode(['status' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>