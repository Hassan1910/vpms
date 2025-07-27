<?php
session_start();
include('includes/dbconnection.php');

// Process payment submission first, before any HTML output
$paymentProcessed = false;
$paymentError = '';
$paystackRedirectUrl = '';

// Check if user is logged in
if (strlen($_SESSION['vpmsuid'])==0) {
    header('location:logout.php');
    exit;
} else { // Opening the main else block

require_once __DIR__ . '/../vendor/autoload.php';
include('paystack.php');

// Process payment submission
if (isset($_POST['pay_now'])) {
    $amount = floatval($_POST['amount']);
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bookingId = intval($_POST['booking_id']);
    $parkingNumber = $_POST['parking_number'];
    
    // Get parking_space.id from parking_number
    $psQuery = mysqli_query($con, "SELECT id FROM parking_space WHERE parking_number = '$parkingNumber'");
    $psRow = mysqli_fetch_assoc($psQuery);
    if (!$psRow) {
        $paymentError = "Parking space not found for number: " . htmlspecialchars($parkingNumber);
        $paymentProcessed = true;
    } else {
        $parkingSpaceId = intval($psRow['id']);

        // Insert payment as pending
        $insert = mysqli_prepare($con, "
            INSERT INTO payment (booking_id, parking_number, amount, status, created_at)
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        mysqli_stmt_bind_param($insert, 'iid', $bookingId, $parkingSpaceId, $amount);
        mysqli_stmt_execute($insert);
        $paymentId = mysqli_insert_id($con);

        // Generate receipt URL
        $receiptUrl = "http://localhost/vpms/users/receipt.php?pk=" . $paymentId;

        // Update the payment record with the receipt_url
        $update = mysqli_prepare($con, "
            UPDATE payment
            SET receipt_url = ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($update, 'si', $receiptUrl, $paymentId);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        
        // Generate a unique reference
        $reference = 'VPMS-' . $paymentId . '-' . time();
        
        // Prepare metadata for Paystack
        $metadata = [
            'booking_id' => $bookingId,
            'payment_id' => $paymentId,
            'user_id' => $_SESSION['vpmsuid'],
            'phone' => $phone
        ];
        
        // Initialize Paystack payment
        $paystackResponse = initiatePaystackPayment($email, $amount, $reference, $metadata);
        
        if ($paystackResponse['status']) {
            // Store the reference in the payment record
            $updateRef = mysqli_prepare($con, "UPDATE payment SET mpesa_checkout_id = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateRef, 'si', $reference, $paymentId);
            mysqli_stmt_execute($updateRef);
            mysqli_stmt_close($updateRef);
            
            // Redirect directly to Paystack
            header("Location: " . $paystackResponse['authorization_url']);
            exit;
        } else {
            $paymentError = "<strong>Payment Failed!</strong> " . htmlspecialchars($paystackResponse['message']);
            $paymentProcessed = true;
        }
    }
}

// Check for booking ID parameter
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    $bookingError = 'Error: Missing or invalid booking ID parameter. Please return to your bookings and try again.';
} else {
    $bookingId = intval($_GET['booking_id']);
    $userId = $_SESSION['vpmsuid'];

    // Fetch booking, user, and parking space information
    $sql = "
        SELECT 
            b.*,
            ps.*,
            u.*
        FROM bookings b
        JOIN parking_space ps ON b.parking_number = ps.parking_number
        JOIN tblregusers u ON b.user_id = u.ID
        WHERE b.id = ? AND b.user_id = ?
    ";

    // Use prepared statement for security
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $bookingId, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        $bookingError = 'No booking found. Invalid booking ID or permission denied.';
    } else {
        $row = mysqli_fetch_assoc($result);
        
        // Calculate duration and cost
        $start = strtotime($row['start_time']);
        // If end_time is set, use it; otherwise use current time
        if (!empty($row['end_time'])) {
            $end = strtotime($row['end_time']);
        } else {
            $end = time(); // Current time
        }
        $hours = max(1, ceil(($end - $start) / 3600)); // Minimum 1 hour

        // Handle potential different column names for price_per_hour
        $rate = isset($row['price_per_hour']) ? $row['price_per_hour'] : 
                (isset($row['hourly_rate']) ? $row['hourly_rate'] : 
                (isset($row['rate']) ? $row['rate'] : 100)); // Default to 100 if not found
                
        $amount = $rate * $hours;

        // Handle column names that might be different in the database
        $phone = isset($row['MobileNumber']) ? $row['MobileNumber'] : 
                 (isset($row['Mobile']) ? $row['Mobile'] : 'N/A');
                 
        $email = isset($row['Email']) ? $row['Email'] : 
                 (isset($row['EmailId']) ? $row['EmailId'] : 'N/A');
                 
        // Combine FirstName and LastName to create full name
        $fullName = '';
        if (isset($row['FirstName']) && isset($row['LastName'])) {
            $fullName = trim($row['FirstName'] . ' ' . $row['LastName']);
        } elseif (isset($row['FirstName'])) {
            $fullName = $row['FirstName'];
        } elseif (isset($row['LastName'])) {
            $fullName = $row['LastName'];
        } else {
            $fullName = 'N/A';
        }
                    
        $parkingNumberCode = isset($row['parking_number']) ? $row['parking_number'] : 'N/A';

        // Format end time for display
        $endTimeDisplay = !empty($row['end_time']) ? $row['end_time'] : date('Y-m-d H:i:s');
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - Complete Payment</title>
    
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pixeden-stroke-7-icon@1.2.3/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="../admin/assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.0/dist/chartist.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .content {
            background: transparent;
            padding: 20px 0;
            margin-left: 0;
        }
        
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 20px 15px;
            border: none;
        }
        
        /* Adjust the right panel to reduce left margin */
        #right-panel {
            margin-left: 250px !important;
            padding-left: 0 !important;
        }
        
        /* Ensure container takes full width */
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
            max-width: none;
        }
        
        /* Remove any default Bootstrap margins */
        .row {
            margin-right: 0;
            margin-left: 0;
        }
        
        .col-lg-12 {
            padding-left: 0;
            padding-right: 0;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            color: white;
            text-align: center;
            position: relative;
            opacity: 1 !important; /* Ensure visibility */
            visibility: visible !important; /* Ensure visibility */
        }
        
        .payment-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 20px solid #667eea;
        }
        
        .payment-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .payment-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
            visibility: visible !important; /* Ensure visibility */
            display: block !important; /* Ensure display */
            color: rgba(255, 255, 255, 0.9) !important; /* Ensure text color */
        }
        
        .payment-body {
            padding: 40px;
        }
        
        .stat-card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,.08);
            transition: all 0.3s ease;
            margin-bottom: 24px;
            border: none;
            overflow: hidden;
            background: white;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,.15);
        }
        
        .stat-card .card-body {
            padding: 25px 30px;
        }
        
        .payment-details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,.06);
            margin-bottom: 30px;
            padding: 30px;
            border: 1px solid #e8ecf4;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden;
        }
        
        .payment-details-card:hover {
            box-shadow: 0 12px 30px rgba(0,0,0,.1);
            transform: translateY(-2px);
        }
        
        .payment-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }
        
        .payment-summary::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .summary-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }
        
        .summary-item-label {
            font-weight: 500;
            opacity: 0.9;
        }
        
        .summary-item-value {
            font-weight: 600;
        }
        
        .total-amount {
            font-size: 28px;
            font-weight: 800;
            margin-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
            padding-top: 20px;
            position: relative;
            z-index: 1;
        }
        
        .btn-pay {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 20px;
            font-weight: 700;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,184,148,0.3);
            position: relative;
            z-index: 1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
            max-width: 100%;
            word-wrap: break-word;
            white-space: normal;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-pay:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,184,148,0.4);
            background: linear-gradient(135deg, #00a085 0%, #008f75 100%);
        }
        
        .btn-pay:active {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0,184,148,0.3);
        }
        
        .payment-card-header {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .payment-card-header i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 24px;
        }
        
        .paystack-logo {
            display: block;
            margin: 25px auto 0;
            max-width: 140px;
            opacity: 0.95;
            filter: brightness(1.1);
        }
        
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            font-size: 14px;
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            position: relative;
            z-index: 1;
        }
        
        .secure-badge i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .payment-method-icons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 15px;
            position: relative;
            z-index: 1;
        }
        
        .payment-method-icons i {
            font-size: 32px;
            color: rgba(255,255,255,0.9);
            transition: all 0.3s ease;
        }
        
        .payment-method-icons i:hover {
            transform: scale(1.1);
            color: white;
        }
        
        .steps-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
            max-width: 120px;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 3px;
            background: #e2e8f0;
            z-index: 0;
        }
        
        .step.active:not(:last-child)::after {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #f7fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            color: #a0aec0;
            font-size: 20px;
            border: 3px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .step.active .step-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .step-text {
            font-size: 14px;
            text-align: center;
            color: #718096;
            font-weight: 500;
        }
        
        .step.active .step-text {
            color: #667eea;
            font-weight: 700;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.95);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            backdrop-filter: blur(5px);
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(102, 126, 234, 0.2);
            border-radius: 50%;
            border-left-color: #667eea;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            margin-top: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #667eea;
        }
        
        .form-control-static {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-weight: 500;
            color: #2d3748;
            width: 100%;
            max-width: 100%;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
            white-space: normal;
        }
        
        .form-group label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            width: 100%;
            word-wrap: break-word;
        }
        
        .form-group {
            margin-bottom: 20px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 20px;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        
        .breadcrumbs {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .breadcrumbs .breadcrumb {
            background: transparent;
            margin-bottom: 0;
        }
        
        .breadcrumbs .breadcrumb-item a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumbs .breadcrumb-item.active {
            color: rgba(255,255,255,0.8);
        }
        
        .page-header h1 {
            color: white;
            font-weight: 700;
            font-size: 24px;
        }
        
        .trust-badge {
            padding: 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .trust-badge:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }
        
        .trust-badge h6 {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2d3748;
        }
        
        .help-section {
            padding: 10px 0;
        }
        
        .help-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        
        .help-item:hover {
            background: #f8fafc;
        }
        
        .help-item i {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            font-size: 18px;
            background: rgba(0,0,0,0.05);
        }
        
        .help-item div {
            flex: 1;
        }
        
        .help-item strong {
            display: block;
            color: #2d3748;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .help-item p {
            color: #718096;
            font-size: 13px;
            margin: 0;
        }
        
        /* Enhanced mobile responsiveness */
        @media (max-width: 768px) {
            #right-panel {
                margin-left: 0 !important;
            }
            
            .main-card {
                margin: 10px;
                border-radius: 15px;
            }
            
            .payment-body {
                padding: 20px;
            }
            
            .payment-header {
                padding: 20px;
            }
            
            .payment-header h1 {
                font-size: 24px;
            }
            
            .steps-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .step:not(:last-child)::after {
                display: none;
            }
            
            .payment-method-icons {
                flex-wrap: wrap;
            }
            
            .btn-pay {
                font-size: 14px;
                padding: 12px 15px;
                letter-spacing: 0.3px;
                min-height: 45px;
            }
            
            .summary-title {
                font-size: 18px;
            }
            
            .payment-card-header {
                font-size: 18px;
            }
            
            .trust-badge {
                margin-bottom: 20px;
            }
            
            .col-md-4:last-child {
                margin-top: 20px;
            }
            
            .payment-summary {
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            #right-panel {
                margin-left: 0 !important;
            }
            
            .main-card {
                margin: 10px;
                border-radius: 15px;
            }
            
            .payment-header {
                padding: 15px;
            }
            
            .payment-header h1 {
                font-size: 20px;
            }
            
            .payment-body {
                padding: 15px;
            }
            
            .payment-details-card {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .form-control-static {
                padding: 10px 12px;
                font-size: 14px;
                word-break: break-all;
            }
            
            .form-group label {
                font-size: 14px;
                margin-bottom: 6px;
            }
            
            .payment-summary {
                padding: 15px;
            }
            
            .btn-pay {
                font-size: 13px;
                padding: 10px 12px;
                letter-spacing: 0.2px;
                min-height: 40px;
            }
            
            .help-item {
                flex-direction: column;
                text-align: center;
            }
            
            .help-item i {
                margin-bottom: 10px;
                margin-right: 0;
            }
        }
        
        /* Desktop adjustments for better sidebar alignment */
        @media (min-width: 769px) {
            #right-panel {
                margin-left: 250px !important;
                padding-left: 0 !important;
            }
            
            .main-card {
                margin: 20px 0;
                max-width: none;
            }
            
            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
                max-width: none;
            }
            
            .breadcrumbs {
                margin-left: 0;
                margin-right: 0;
            }
            
            .content {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
        
        /* Specific override for payment header subtitle visibility */
        .payment-header h1 {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
            color: white !important;
        }
        
        .payment-header p {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 16px !important;
            margin: 10px 0 0 !important;
        }
    </style>
</head>
<body>

<!-- Left Panel -->
<?php include_once('includes/sidebar.php');?>

<!-- Right Panel -->
<div id="right-panel" class="right-panel">

    <!-- Header-->
    <?php include_once('includes/header.php');?>

    <div class="breadcrumbs">
        <div class="breadcrumbs-inner">
            <div class="row m-0">
                <div class="col-sm-4">
                    <div class="page-header float-left">
                        <div class="page-title">
                            <h1>Payment</h1>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="page-header float-right">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li><a href="manage-booking.php">Bookings</a></li>
                                <li class="active">Complete Payment</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="animated fadeIn">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-card">
                            <div class="payment-header">
                                <h1><i class="fas fa-credit-card mr-3"></i>Complete Your Payment</h1>
                                <p>Secure and fast payment processing for your parking session</p>
                            </div>
                            <div class="payment-body">
                                <?php if (isset($bookingError)): ?>
                                <div class="alert alert-danger text-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i><?= $bookingError ?>
                                </div>
                                <?php elseif ($paymentError): ?>
                                <div class="alert alert-danger text-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i><?= $paymentError ?>
                                </div>
                                <?php else: ?>
                            <!-- Payment Progress Steps -->
                            <div class="steps-container">
                                <div class="step active">
                                    <div class="step-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="step-text">Review Details</div>
                                </div>
                                <div class="step">
                                    <div class="step-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="step-text">Payment</div>
                                </div>
                                <div class="step">
                                    <div class="step-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="step-text">Confirmation</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="payment-details-card">
                                        <h5 class="payment-card-header">
                                            <i class="fas fa-car"></i>
                                            Parking Session Details
                                        </h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-map-marker-alt mr-2"></i>Parking Spot</label>
                                                    <div class="form-control-static"><?= htmlspecialchars($row['parking_number']) ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-clock mr-2"></i>Duration</label>
                                                    <div class="form-control-static"><?= $hours ?> hour(s)</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-play mr-2"></i>Start Time</label>
                                                    <div class="form-control-static"><?= date('M j, Y g:i A', strtotime($row['start_time'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-stop mr-2"></i>End Time</label>
                                                    <div class="form-control-static">
                                                        <?= !empty($row['end_time']) ? date('M j, Y g:i A', strtotime($row['end_time'])) : '<span class="text-warning">Active Session</span>' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="payment-details-card">
                                        <h5 class="payment-card-header">
                                            <i class="fas fa-user"></i>
                                            Customer Information
                                        </h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-user-circle mr-2"></i>Full Name</label>
                                                    <div class="form-control-static"><?= htmlspecialchars($fullName) ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-phone mr-2"></i>Phone Number</label>
                                                    <div class="form-control-static"><?= htmlspecialchars($phone) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-envelope mr-2"></i>Email Address</label>
                                                    <div class="form-control-static"><?= htmlspecialchars($email) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Trust & Security Section -->
                                    <div class="payment-details-card">
                                        <h5 class="payment-card-header">
                                            <i class="fas fa-shield-alt"></i>
                                            Security & Trust
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4 text-center mb-3">
                                                <div class="trust-badge">
                                                    <i class="fas fa-lock fa-2x text-success mb-2"></i>
                                                    <h6>SSL Encrypted</h6>
                                                    <small class="text-muted">Your data is protected with 256-bit SSL encryption</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-center mb-3">
                                                <div class="trust-badge">
                                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                                    <h6>PCI Compliant</h6>
                                                    <small class="text-muted">Industry standard payment security</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-center mb-3">
                                                <div class="trust-badge">
                                                    <i class="fas fa-user-shield fa-2x text-info mb-2"></i>
                                                    <h6>Privacy Protected</h6>
                                                    <small class="text-muted">Your personal information is never shared</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="payment-summary">
                                        <h5 class="summary-title">
                                            <i class="fas fa-receipt mr-2"></i>Payment Summary
                                        </h5>
                                        <div class="summary-item">
                                            <span class="summary-item-label">Rate per hour:</span>
                                            <span class="summary-item-value">KSh <?= number_format($rate, 2) ?></span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-item-label">Duration:</span>
                                            <span class="summary-item-value"><?= $hours ?> hour(s)</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-item-label">Subtotal:</span>
                                            <span class="summary-item-value">KSh <?= number_format($amount, 2) ?></span>
                                        </div>
                                        <div class="total-amount">
                                            <div class="summary-item">
                                                <span class="summary-item-label">Total Amount:</span>
                                                <span class="summary-item-value">KSh <?= number_format($amount, 2) ?></span>
                                            </div>
                                        </div>
                                        <form method="post" id="paymentForm">
                                            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                                            <input type="hidden" name="amount" value="<?= $amount ?>">
                                            <input type="hidden" name="email" value="<?= $email ?>">
                                            <input type="hidden" name="phone" value="<?= $phone ?>">
                                            <input type="hidden" name="parking_number" value="<?= $parkingNumberCode ?>">
                                            <button type="submit" name="pay_now" class="btn btn-success btn-block btn-pay">
                                                <i class="fas fa-lock mr-2"></i>Pay Securely Now
                                            </button>
                                        </form>
                                        
                                        <div class="payment-method-icons">
                                            <i class="fab fa-cc-visa" title="Visa"></i>
                                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                                            <i class="fab fa-cc-amex" title="American Express"></i>
                                            <i class="fab fa-paypal" title="PayPal"></i>
                                        </div>
                                        
                                        <div class="secure-badge">
                                            <i class="fas fa-shield-alt"></i>
                                            256-bit SSL Encrypted Payment
                                        </div>
                                        
                                        <img src="https://website-v3-assets.s3.amazonaws.com/assets/img/hero/Paystack-mark-white-twitter.png" alt="Paystack - Secure Payment Gateway" class="paystack-logo">
                                    </div>
                                    
                                    <!-- Help Section -->
                                    <div class="payment-details-card">
                                        <h5 class="payment-card-header">
                                            <i class="fas fa-question-circle"></i>
                                            Need Help?
                                        </h5>
                                        <div class="help-section">
                                            <div class="help-item">
                                                <i class="fas fa-phone text-success"></i>
                                                <div>
                                                    <strong>Call Support</strong>
                                                    <p class="mb-0">+254 700 000 000</p>
                                                </div>
                                            </div>
                                            <div class="help-item">
                                                <i class="fas fa-envelope text-primary"></i>
                                                <div>
                                                    <strong>Email Support</strong>
                                                    <p class="mb-0">support@vpms.com</p>
                                                </div>
                                            </div>
                                            <div class="help-item">
                                                <i class="fas fa-clock text-info"></i>
                                                <div>
                                                    <strong>Support Hours</strong>
                                                    <p class="mb-0">24/7 Available</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="clearfix"></div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p class="loading-text">Processing your payment...</p>
        <p style="font-size: 14px; color: #718096; margin-top: 10px;">Please do not close this window</p>
    </div>

    <footer class="site-footer">
        <div class="footer-inner bg-white">
            <div class="row">
                <div class="col-sm-6">
                    Copyright &copy; <?php echo date('Y')?> Vehicle Parking Management System
                </div>
                <div class="col-sm-6 text-right">
                    Designed by Rawlz Mwenda
                </div>
            </div>
        </div>
    </footer>

</div><!-- /#right-panel -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
<script src="../admin/assets/js/main.js"></script>
<script>
    // Initialize page animations and interactions
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Animate cards on page load
        $('.payment-details-card, .payment-summary').addClass('animated fadeInUp');
        
        // Auto dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Enhanced payment form submission handling
        $("#paymentForm").submit(function(e) {
            // Prevent double submission
            if ($(this).data('submitted')) {
                e.preventDefault();
                return false;
            }
            
            $(this).data('submitted', true);
            
            // Disable the submit button
            $('.btn-pay').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...');
            
            // Show the loading overlay with animation
            $("#loadingOverlay").fadeIn(300);
            
            // Animate step progression
            setTimeout(function() {
                $(".step:first-child").removeClass("active");
                $(".step:nth-child(2)").addClass("active");
            }, 500);
            
            // If there's an error, re-enable the form
            setTimeout(function() {
                if (!window.location.href.includes('paystack')) {
                    $('#paymentForm').data('submitted', false);
                    $('.btn-pay').prop('disabled', false).html('<i class="fas fa-lock mr-2"></i>Pay Securely Now');
                    $("#loadingOverlay").fadeOut(300);
                }
            }, 10000);
            
            return true;
        });
        
        // Enhanced button hover effects
        $(".btn-pay").hover(
            function() {
                $(this).find("i").addClass("fa-pulse");
                $(this).css('transform', 'translateY(-2px)');
            }, 
            function() {
                $(this).find("i").removeClass("fa-pulse");
                $(this).css('transform', 'translateY(0)');
            }
        );
        
        // Card hover effects with better animations
        $(".payment-details-card").hover(
            function() {
                $(this).css({
                    'transform': 'translateY(-5px)',
                    'box-shadow': '0 15px 35px rgba(0,0,0,0.12)'
                });
            },
            function() {
                $(this).css({
                    'transform': 'translateY(0)',
                    'box-shadow': '0 8px 25px rgba(0,0,0,.06)'
                });
            }
        );
        
        // Payment summary hover effect
        $(".payment-summary").hover(
            function() {
                $(this).css('transform', 'scale(1.02)');
            },
            function() {
                $(this).css('transform', 'scale(1)');
            }
        );
        
        // Add floating animation to payment icons
        setInterval(function() {
            $('.payment-method-icons i').each(function(index) {
                $(this).delay(index * 100).animate({
                    'margin-top': '-5px'
                }, 500).animate({
                    'margin-top': '0px'
                }, 500);
            });
        }, 3000);
        
        // Add success animation for form validation
        $('input[type="hidden"]').each(function() {
            if ($(this).val() !== '') {
                $(this).closest('.form-group').addClass('has-success');
            }
        });
        
        // Add ripple effect to payment button
        $('.btn-pay').on('click', function(e) {
            var ripple = $('<span class="ripple"></span>');
            $(this).append(ripple);
            
            var x = e.pageX - $(this).offset().left;
            var y = e.pageY - $(this).offset().top;
            
            ripple.css({
                'left': x,
                'top': y,
                'position': 'absolute',
                'width': '0',
                'height': '0',
                'border-radius': '50%',
                'background': 'rgba(255,255,255,0.5)',
                'transform': 'scale(0)',
                'animation': 'ripple 0.6s linear',
                'pointer-events': 'none'
            });
            
            setTimeout(function() {
                ripple.remove();
            }, 600);
        });
        
        // Add CSS animation for ripple effect
        $('<style>').text(`
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            .btn-pay {
                position: relative;
                overflow: hidden;
            }
        `).appendTo('head');
        
        // Add smooth scrolling for mobile
        $('html').css('scroll-behavior', 'smooth');
        
        // Add loading animation to page elements
        $('.payment-details-card, .payment-summary').css('opacity', '0');
        
        setTimeout(function() {
            $('.payment-details-card').first().animate({opacity: 1}, 300);
            setTimeout(function() {
                $('.payment-details-card').eq(1).animate({opacity: 1}, 300);
                setTimeout(function() {
                    $('.payment-summary').animate({opacity: 1}, 300);
                }, 200);
            }, 200);
        }, 100);
        
        // Add real-time form validation feedback
        $('form').on('submit', function() {
            var isValid = true;
            $(this).find('input[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });
            return isValid;
        });
        
        // Add countdown timer for session timeout (optional)
        var sessionTimeout = 15 * 60; // 15 minutes
        var warningShown = false;
        
        setInterval(function() {
            sessionTimeout--;
            if (sessionTimeout <= 300 && !warningShown) { // 5 minutes warning
                warningShown = true;
                $('<div class="alert alert-warning session-warning" style="position: fixed; top: 20px; right: 20px; z-index: 1000; border-radius: 10px;">')
                    .html('<i class="fas fa-clock mr-2"></i>Your session will expire in 5 minutes. Please complete your payment soon.')
                    .appendTo('body')
                    .delay(5000)
                    .fadeOut();
            }
        }, 1000);
    });
</script>
</body>
</html>
<?php 
        } // Close the else block from "if (mysqli_num_rows($result) == 0)"
    } // Close the else block from "if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id']))"
} // Close the main else block from user login check
?>