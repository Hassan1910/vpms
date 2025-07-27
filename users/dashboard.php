<?php
session_start();
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');

include('includes/dbconnection.php');

// Ensure timezone is set (should already be set in dbconnection.php)
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Africa/Nairobi'); // Change this to your timezone
}

// Debug session information
error_log("Session UID: " . (isset($_SESSION['vpmsuid']) ? $_SESSION['vpmsuid'] : 'NOT SET'));
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['vpmsuid']) || strlen($_SESSION['vpmsuid']) == 0) {
    error_log("No valid session found, redirecting to logout");
    header('location:logout.php');
    exit();
} else{ 
    $uid = $_SESSION['vpmsuid'];
    error_log("Valid session found for user ID: " . $uid);
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - User Dashboard</title>

    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.1/normalize.min.css">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .content {
            padding: 20px;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="50" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="30" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .dashboard-header .row {
            position: relative;
            z-index: 1;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            border: none;
            overflow: hidden;
            position: relative;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        .stat-card .card-body {
            padding: 25px;
        }
        
        .stat-widget {
            padding: 25px;
            text-align: center;
            color: #fff;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        
        .stat-widget::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }
        
        .stat-widget h4 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .stat-widget p {
            font-size: 0.9rem;
            margin-bottom: 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .stat-widget i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            position: relative;
            z-index: 1;
        }
        
        .stat-bg-one {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-bg-two {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-bg-three {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-bg-four {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stat-bg-five {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .stat-bg-six {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
        
        .welcome-user {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .welcome-text {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 1;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .dashboard-meta {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            opacity: 0.9;
            color: rgba(255, 255, 255, 0.9);
        }

        .meta-item i {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .dashboard-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .dashboard-actions .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dashboard-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .action-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: none;
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100%;
            text-align: center;
            color: #5a5c69;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            min-height: 120px;
        }
        
        .action-button:hover {
            text-decoration: none;
            color: #667eea;
            transform: scale(1.05);
        }
        
        .action-button i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #667eea;
            transition: all 0.3s ease;
        }
        
        .action-button:hover i {
            color: #764ba2;
            transform: scale(1.1);
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .recent-booking {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: none;
        }
        
        .table-head {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            color: #667eea;
        }
        
        .badge-status {
            padding: 8px 15px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-active {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        
        .badge-completed {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .badge-pending {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }
        
        .card-header {
            border-bottom: 1px solid #f1f1f1;
            background: white;
            border-radius: 20px 20px 0 0 !important;
        }
        
        .btn-custom {
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .progress {
            height: 12px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .progress-bar {
            border-radius: 10px;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .coming-soon {
            opacity: 0.6;
            position: relative;
        }
        
        .coming-soon::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
        }
        
        .weather-widget {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .weather-temp {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .activity-feed {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-left: 3px solid #667eea;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .quick-stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        
        .quick-stat {
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            color: white;
            min-width: 100px;
        }
        
        .quick-stat h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .quick-stat p {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        .notifications-panel {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .notification-item {
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .notification-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .notification-item.warning {
            border-left-color: #ffc107;
        }
        
        .notification-item.success {
            border-left-color: #28a745;
        }
        
        .notification-item.info {
            border-left-color: #17a2b8;
        }
        
        .parking-map {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .parking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        
        .parking-spot {
            aspect-ratio: 1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .parking-spot.available {
            background: #d4edda;
            color: #155724;
        }
        
        .parking-spot.occupied {
            background: #f8d7da;
            color: #721c24;
        }
        
        .parking-spot.reserved {
            background: #fff3cd;
            color: #856404;
        }
        
        .parking-spot:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .time-widget {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .time-display {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .date-display {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 20px;
            }
            
            .dashboard-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .content {
                padding: 15px;
            }
            
            .action-button {
                min-height: 100px;
            }
            
            .stat-widget h4 {
                font-size: 2rem;
            }
            
            .stat-widget i {
                font-size: 2.5rem;
            }
            
            .dashboard-meta {
                flex-direction: column;
                gap: 15px;
                margin-top: 15px;
            }
            
            .dashboard-actions {
                justify-content: center;
                margin-top: 20px;
            }
            
            .dashboard-actions .btn {
                width: 100%;
                text-align: center;
            }
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .animate-on-scroll.animate {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <?php include_once('includes/sidebar.php');?>
    <?php include_once('includes/header.php');?>
      
    <!-- Content -->
    <div class="content">
        <!-- Animated -->
        <div class="animated fadeIn">
            <?php
            // Display payment success/error messages
            if (isset($_GET['payment_success']) && $_GET['payment_success'] == 1) {
                if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                    echo '<strong><i class="fa fa-check-circle"></i> Payment Successful!</strong> ' . htmlspecialchars($_SESSION['payment_message']);
                    if (isset($_SESSION['last_payment_id'])) {
                        echo ' <a href="receipt.php?pk=' . $_SESSION['last_payment_id'] . '" class="btn btn-sm btn-primary ml-2" target="_blank">Download Receipt</a>';
                    }
                    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    echo '<span aria-hidden="true">&times;</span>';
                    echo '</button>';
                    echo '</div>';
                    // Clear the session messages
                    unset($_SESSION['payment_success']);
                    unset($_SESSION['payment_message']);
                }
            }
            if (isset($_GET['payment_error']) && $_GET['payment_error'] == 1) {
                if (isset($_SESSION['payment_error']) && $_SESSION['payment_error']) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                    echo '<strong><i class="fa fa-exclamation-circle"></i> Payment Failed!</strong> ' . htmlspecialchars($_SESSION['payment_message']);
                    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    echo '<span aria-hidden="true">&times;</span>';
                    echo '</button>';
                    echo '</div>';
                    // Clear the session messages
                    unset($_SESSION['payment_error']);
                    unset($_SESSION['payment_message']);
                }
            }
            ?>

            <!-- Enhanced Dashboard Header -->
            <div class="dashboard-header animate-on-scroll">
                <?php
                $ret = mysqli_query($con,"SELECT * FROM tblregusers WHERE ID='$uid'");
                if (!$ret) {
                    error_log("User query failed: " . mysqli_error($con));
                    echo '<div class="alert alert-danger">Error loading user data</div>';
                } else if ($row = mysqli_fetch_array($ret)) {
                ?>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">
                            <i class="fas fa-user me-3"></i>
                            Welcome back, <?php echo $row['FirstName']; ?>!
                        </h1>
                        <p class="welcome-text mb-3">Here's what's happening with your parking today</p>
                        <div class="dashboard-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo date('l, F j, Y'); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span id="current-time"><?php echo date('g:i A'); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="dashboard-actions">
                            <a href="book-space.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                New Booking
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Enhanced Stats Cards -->
            <div class="row animate-on-scroll">
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-widget stat-bg-one">
                            <i class="fas fa-car"></i>
                            <?php
                            // Count current active bookings with error handling
                            $active_query = mysqli_query($con,"SELECT COUNT(*) as total FROM bookings WHERE user_id = '$uid' AND status = 'active'");
                            if (!$active_query) {
                                error_log("Active bookings query failed: " . mysqli_error($con));
                                $active_result = array('total' => 0);
                            } else {
                                $active_result = mysqli_fetch_array($active_query);
                                if (!$active_result) {
                                    $active_result = array('total' => 0);
                                }
                            }
                            ?>
                            <h4><?php echo $active_result['total']; ?></h4>
                            <p>Active Bookings</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-widget stat-bg-two">
                            <i class="fas fa-history"></i>
                            <?php
                            // Count total bookings with error handling
                            $total_query = mysqli_query($con,"SELECT COUNT(*) as total FROM bookings WHERE user_id = '$uid'");
                            if (!$total_query) {
                                error_log("Total bookings query failed: " . mysqli_error($con));
                                $total_result = array('total' => 0);
                            } else {
                                $total_result = mysqli_fetch_array($total_query);
                                if (!$total_result) {
                                    $total_result = array('total' => 0);
                                }
                            }
                            ?>
                            <h4><?php echo $total_result['total']; ?></h4>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-widget stat-bg-three">
                            <i class="fas fa-credit-card"></i>
                            <?php
                            // Sum total payments with error handling
                            $payment_query = mysqli_query($con,"SELECT SUM(p.amount) as total FROM payment p JOIN bookings b ON p.booking_id = b.id WHERE b.user_id = '$uid' AND p.status = 'paid'");
                            if (!$payment_query) {
                                error_log("Payment query failed: " . mysqli_error($con));
                                $payment_result = array('total' => 0);
                            } else {
                                $payment_result = mysqli_fetch_array($payment_query);
                                if (!$payment_result) {
                                    $payment_result = array('total' => 0);
                                }
                            }
                            $total_paid = $payment_result['total'] ? $payment_result['total'] : 0;
                            ?>
                            <h4>KES <?php echo number_format($total_paid, 0); ?></h4>
                            <p>Total Payments</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-widget stat-bg-four">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php
                            // Count available parking spaces with error handling
                            $space_query = mysqli_query($con,"SELECT COUNT(*) as total FROM tblparkingspaces WHERE Status = 'Available'");
                            if (!$space_query) {
                                error_log("Parking spaces query failed: " . mysqli_error($con));
                                $space_result = array('total' => 0);
                            } else {
                                $space_result = mysqli_fetch_array($space_query);
                                if (!$space_result) {
                                    $space_result = array('total' => 0);
                                }
                            }
                            ?>
                            <h4><?php echo $space_result['total']; ?></h4>
                            <p>Available Spaces</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-widget stat-bg-five">
                            <i class="fas fa-clock"></i>
                            <?php
                            // Calculate average parking duration with error handling
                            $duration_query = mysqli_query($con,"SELECT AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_duration FROM bookings WHERE user_id = '$uid' AND status = 'completed' AND end_time IS NOT NULL");
                            if (!$duration_query) {
                                error_log("Duration query failed: " . mysqli_error($con));
                                $duration_result = array('avg_duration' => 0);
                            } else {
                                $duration_result = mysqli_fetch_array($duration_query);
                                if (!$duration_result) {
                                    $duration_result = array('avg_duration' => 0);
                                }
                            }
                            $avg_duration = $duration_result['avg_duration'] ? round($duration_result['avg_duration'], 1) : 0;
                            ?>
                            <h4><?php echo $avg_duration; ?>h</h4>
                            <p>Avg. Duration</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-widget stat-bg-six">
                            <i class="fas fa-star"></i>
                            <h4>4.8</h4>
                            <p>Rating</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weather Widget -->
            <div class="row animate-on-scroll">
                <div class="col-md-4">
                    <div class="weather-widget">
                        <i class="fas fa-sun fa-2x mb-3"></i>
                        <h3>Nairobi</h3>
                        <div class="weather-temp">24°C</div>
                        <p>Partly Cloudy</p>
                        <small>Perfect weather for parking!</small>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="notifications-panel">
                        <h5 class="card-title">
                            <i class="fas fa-bell mr-2"></i>
                            Recent Notifications
                        </h5>
                        <div class="notification-item success">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Payment Successful</strong>
                                    <p class="mb-0 text-muted">Your parking payment of KES 200 has been processed successfully.</p>
                                </div>
                                <small class="text-muted">2 min ago</small>
                            </div>
                        </div>
                        <div class="notification-item warning">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Parking Reminder</strong>
                                    <p class="mb-0 text-muted">Your parking session will expire in 30 minutes.</p>
                                </div>
                                <small class="text-muted">15 min ago</small>
                            </div>
                        </div>
                        <div class="notification-item info">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>New Feature Available</strong>
                                    <p class="mb-0 text-muted">You can now extend your parking time through the app.</p>
                                </div>
                                <small class="text-muted">1 hour ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Quick Actions -->
            <div class="card mb-4 animate-on-scroll">
                <div class="card-header py-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="action-card">
                                <a href="book-space.php" class="action-button">
                                    <i class="fas fa-parking"></i>
                                    Book a Space
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="action-card">
                                <a href="manage-booking.php" class="action-button">
                                    <i class="fas fa-calendar-check"></i>
                                    My Bookings
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="action-card">
                                <a href="history.php" class="action-button">
                                    <i class="fas fa-history"></i>
                                    Payment History
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="action-card">
                                <a href="profile.php" class="action-button">
                                    <i class="fas fa-user-circle"></i>
                                    My Profile
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="action-card">
                                <a href="payment.php" class="action-button">
                                    <i class="fas fa-credit-card"></i>
                                    Make Payment
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="action-card">
                                <a href="receipt.php" class="action-button">
                                    <i class="fas fa-receipt"></i>
                                    Download Receipt
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Feed and Parking Map -->
            <div class="row animate-on-scroll">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-activity mr-2"></i>
                                Recent Activity
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="activity-feed">
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><strong>Vehicle Parked</strong></p>
                                        <p class="mb-0 text-muted">You parked at Space A-101</p>
                                        <small class="text-muted">2 hours ago</small>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><strong>Payment Made</strong></p>
                                        <p class="mb-0 text-muted">KES 200 for 4 hours parking</p>
                                        <small class="text-muted">1 day ago</small>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><strong>Booking Confirmed</strong></p>
                                        <p class="mb-0 text-muted">Space B-205 reserved successfully</p>
                                        <small class="text-muted">2 days ago</small>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-user-edit"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><strong>Profile Updated</strong></p>
                                        <p class="mb-0 text-muted">Contact information updated</p>
                                        <small class="text-muted">1 week ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="parking-map">
                        <h6 class="card-title">
                            <i class="fas fa-map mr-2"></i>
                            Parking Space Overview
                        </h6>
                        <div class="row mb-3">
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="parking-spot available mb-2">A</div>
                                    <small>Available</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="parking-spot occupied mb-2">O</div>
                                    <small>Occupied</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="parking-spot reserved mb-2">R</div>
                                    <small>Reserved</small>
                                </div>
                            </div>
                        </div>
                        <div class="parking-grid">
                            <div class="parking-spot available">A1</div>
                            <div class="parking-spot occupied">A2</div>
                            <div class="parking-spot available">A3</div>
                            <div class="parking-spot reserved">A4</div>
                            <div class="parking-spot available">A5</div>
                            <div class="parking-spot occupied">A6</div>
                            <div class="parking-spot available">B1</div>
                            <div class="parking-spot occupied">B2</div>
                            <div class="parking-spot available">B3</div>
                            <div class="parking-spot available">B4</div>
                            <div class="parking-spot occupied">B5</div>
                            <div class="parking-spot available">B6</div>
                            <div class="parking-spot reserved">C1</div>
                            <div class="parking-spot available">C2</div>
                            <div class="parking-spot occupied">C3</div>
                            <div class="parking-spot available">C4</div>
                            <div class="parking-spot available">C5</div>
                            <div class="parking-spot occupied">C6</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Recent Bookings -->
            <div class="card recent-booking mb-4 animate-on-scroll">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>
                        Recent Bookings
                    </h6>
                    <a href="manage-booking.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-head">
                                <tr>
                                    <th>#</th>
                                    <th>Parking Number</th>
                                    <th>Vehicle</th>
                                    <th>Entry Date</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            $query = mysqli_query($con, "SELECT b.*, v.VehicleCategory, v.VehicleCompanyname, v.RegistrationNumber, v.ParkingCharge, v.Status as VehicleStatus 
                                                          FROM bookings b 
                                                          JOIN tblvehicle v ON b.vehicle_id = v.ID 
                                                          WHERE b.user_id='$uid' 
                                                          ORDER BY b.id DESC LIMIT 5");
                            
                            if (!$query) {
                                error_log("Recent bookings query failed: " . mysqli_error($con));
                                echo '<tr><td colspan="8" class="text-center text-danger">Error loading bookings data</td></tr>';
                            } else {
                                $cnt = 1;
                                if(mysqli_num_rows($query) > 0) {
                                    while($row = mysqli_fetch_array($query)) {
                                    $status = $row['status'];
                                    $statusClass = '';
                                    if($status == 'active') {
                                        $statusClass = 'badge-active';
                                        $status = 'Active';
                                    } else if($status == 'completed') {
                                        $statusClass = 'badge-completed';
                                        $status = 'Completed';
                                    } else {
                                        $statusClass = 'badge-pending';
                                        $status = 'Cancelled';
                                    }
                                    
                                    // Calculate duration
                                    $duration = 'N/A';
                                    if($row['end_time']) {
                                        $start_time = strtotime($row['start_time']);
                                        $end_time = strtotime($row['end_time']);
                                        $duration_hours = ($end_time - $start_time) / 3600;
                                        $duration = round($duration_hours, 1) . 'h';
                                    }
                            ?>
                                <tr>
                                    <td><?php echo $cnt; ?></td>
                                    <td><strong><?php echo htmlentities($row['parking_number']); ?></strong></td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlentities($row['VehicleCategory']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlentities($row['VehicleCompanyname']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php echo date('M d, Y', strtotime($row['start_time'])); ?>
                                            <br>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($row['start_time'])); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo $duration; ?></td>
                                    <td>
                                        <strong>KES <?php echo number_format(floatval($row['ParkingCharge'] ?? 0), 2); ?></strong>
                                    </td>
                                    <td><span class="badge badge-status <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view-vehicle.php?viewid=<?php echo htmlentities($row['vehicle_id']); ?>" 
                                               class="btn btn-sm btn-info" data-toggle="tooltip" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($row['status'] == 'completed' && $row['ParkingCharge'] > 0) { ?>
                                            <a href="receipt.php?booking_id=<?php echo htmlentities($row['id']); ?>" 
                                               class="btn btn-sm btn-success" data-toggle="tooltip" title="Download Receipt">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                    $cnt++;
                                }
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-car fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No bookings found</h5>
                                        <p class="text-muted">Start by booking your first parking space!</p>
                                        <a href="book-space.php" class="btn btn-primary">Book Now</a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } 
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Section -->
            <div class="row animate-on-scroll">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-line mr-2"></i>
                                Parking Analytics
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="parkingChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-pie mr-2"></i>
                                Usage Distribution
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="usageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced System Status -->
            <div class="row animate-on-scroll">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Parking Space Utilization
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Calculate parking space utilization with error handling
                            $total_spaces_query = mysqli_query($con, "SELECT COUNT(*) as total FROM tblparkingspaces");
                            if (!$total_spaces_query) {
                                error_log("Total spaces query failed: " . mysqli_error($con));
                                $total_spaces = 0;
                            } else {
                                $total_spaces_result = mysqli_fetch_array($total_spaces_query);
                                $total_spaces = $total_spaces_result ? $total_spaces_result['total'] : 0;
                            }
                            
                            $occupied_spaces_query = mysqli_query($con, "SELECT COUNT(*) as total FROM tblparkingspaces WHERE Status = 'Occupied'");
                            if (!$occupied_spaces_query) {
                                error_log("Occupied spaces query failed: " . mysqli_error($con));
                                $occupied_spaces = 0;
                            } else {
                                $occupied_spaces_result = mysqli_fetch_array($occupied_spaces_query);
                                $occupied_spaces = $occupied_spaces_result ? $occupied_spaces_result['total'] : 0;
                            }
                            
                            $available_spaces = $total_spaces - $occupied_spaces;
                            $utilization_percentage = $total_spaces > 0 ? ($occupied_spaces / $total_spaces) * 100 : 0;
                            ?>
                            <div class="text-center mb-4">
                                <h2 class="text-primary"><?php echo round($utilization_percentage, 1); ?>%</h2>
                                <p class="text-muted">Current Occupancy Rate</p>
                            </div>
                            <div class="progress mb-4" style="height: 20px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $utilization_percentage; ?>%" 
                                    aria-valuenow="<?php echo $utilization_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-right">
                                        <h4 class="text-success mb-1"><?php echo $available_spaces; ?></h4>
                                        <p class="text-muted mb-0">Available</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-danger mb-1"><?php echo $occupied_spaces; ?></h4>
                                    <p class="text-muted mb-0">Occupied</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-tags mr-2"></i>
                                My Parking Categories
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $categories_query = mysqli_query($con, "SELECT v.VehicleCategory, COUNT(*) as count 
                                                                   FROM bookings b 
                                                                   JOIN tblvehicle v ON b.vehicle_id = v.ID 
                                                                   WHERE b.user_id='$uid' 
                                                                   GROUP BY v.VehicleCategory 
                                                                   ORDER BY count DESC LIMIT 4");
                            
                            if (!$categories_query) {
                                error_log("Categories query failed: " . mysqli_error($con));
                                echo '<div class="text-center py-4">';
                                echo '<i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>';
                                echo '<h5 class="text-warning">Error loading category data</h5>';
                                echo '</div>';
                            } else if(mysqli_num_rows($categories_query) > 0) {
                                $colors = ['primary', 'success', 'warning', 'info'];
                                $color_index = 0;
                                while($cat_row = mysqli_fetch_array($categories_query)) {
                                    $category = $cat_row['VehicleCategory'];
                                    $count = $cat_row['count'];
                                    $cat_percentage = $total_result['total'] > 0 ? ($count / $total_result['total']) * 100 : 0;
                                    $color = $colors[$color_index % 4];
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-<?php echo $color; ?> rounded-circle mr-3" style="width: 12px; height: 12px;"></div>
                                        <span class="font-weight-medium"><?php echo $category; ?></span>
                                    </div>
                                    <span class="badge badge-light"><?php echo $count; ?> bookings</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?php echo $color; ?>" role="progressbar" style="width: <?php echo $cat_percentage; ?>%" 
                                        aria-valuenow="<?php echo $cat_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <?php 
                                    $color_index++;
                                }
                            } else {
                                echo '<div class="text-center py-4">';
                                echo '<i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>';
                                echo '<h5 class="text-muted">No booking data available</h5>';
                                echo '<p class="text-muted">Start parking to see your usage patterns!</p>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Coming Soon Features -->
            <div class="card mb-4 animate-on-scroll">
                <div class="card-header py-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-rocket mr-2"></i>
                        Coming Soon Features
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="action-card coming-soon">
                                <div class="action-button">
                                    <i class="fas fa-bell"></i>
                                    <span>Push Notifications</span>
                                    <span class="badge badge-warning mt-2">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="action-card coming-soon">
                                <div class="action-button">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>Mobile App</span>
                                    <span class="badge badge-warning mt-2">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="action-card coming-soon">
                                <div class="action-button">
                                    <i class="fas fa-qrcode"></i>
                                    <span>QR Code Access</span>
                                    <span class="badge badge-warning mt-2">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="action-card coming-soon">
                                <div class="action-button">
                                    <i class="fas fa-car-side"></i>
                                    <span>Valet Service</span>
                                    <span class="badge badge-warning mt-2">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.animated -->
    </div>
    <!-- /.content -->

    <div class="clearfix"></div>
    
    <!-- Footer -->
    <?php include_once('includes/footer.php');?>

    <!-- Enhanced Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
    <script src="../admin/assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <script>
        jQuery(document).ready(function($) {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Auto dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);

            // Real-time clock
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                const dateString = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                jQuery('#current-time').text(now.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }));
            }
            
            updateTime();
            setInterval(updateTime, 1000);

            // Scroll-triggered animations
            function animateOnScroll() {
                $('.animate-on-scroll').each(function() {
                    const elementTop = $(this).offset().top;
                    const elementBottom = elementTop + $(this).outerHeight();
                    const viewportTop = $(window).scrollTop();
                    const viewportBottom = viewportTop + $(window).height();
                    
                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).addClass('animate');
                    }
                });
            }
            
            animateOnScroll();
            $(window).on('scroll', animateOnScroll);

            // Parking Chart
            const parkingCtx = document.getElementById('parkingChart');
            if (parkingCtx) {
                new Chart(parkingCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Bookings',
                            data: [12, 19, 8, 15, 22, 18, 25, 20, 16, 28, 15, 10],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Payments',
                            data: [8, 15, 6, 12, 18, 14, 20, 16, 12, 22, 12, 8],
                            borderColor: '#764ba2',
                            backgroundColor: 'rgba(118, 75, 162, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            }
                        }
                    }
                });
            }

            // Usage Chart
            const usageCtx = document.getElementById('usageChart');
            if (usageCtx) {
                new Chart(usageCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Car', 'Motorcycle', 'Bicycle', 'Truck'],
                        datasets: [{
                            data: [60, 25, 10, 5],
                            backgroundColor: [
                                '#667eea',
                                '#f093fb',
                                '#4facfe',
                                '#43e97b'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Parking spot hover effects
            $('.parking-spot').hover(function() {
                $(this).attr('data-original-title', 'Space: ' + $(this).text() + ' - ' + $(this).hasClass('available') ? 'Available' : 'Occupied');
            });

            // Action card hover effects
            $('.action-card').hover(function() {
                $(this).find('i').addClass('fa-bounce');
            }, function() {
                $(this).find('i').removeClass('fa-bounce');
            });

            // Enhanced stat card animations
            $('.stat-card').each(function(index) {
                $(this).delay(index * 150).queue(function(next) {
                    $(this).addClass('animate');
                    next();
                });
            });

            // Weather widget (mock data)
            function updateWeather() {
                const temperatures = [20, 22, 24, 26, 28, 25, 23];
                const conditions = ['Sunny', 'Partly Cloudy', 'Cloudy', 'Rainy'];
                const icons = ['fa-sun', 'fa-cloud-sun', 'fa-cloud', 'fa-cloud-rain'];
                
                const randomTemp = temperatures[Math.floor(Math.random() * temperatures.length)];
                const randomCondition = Math.floor(Math.random() * conditions.length);
                
                $('.weather-temp').text(randomTemp + '°C');
                $('.weather-widget p').text(conditions[randomCondition]);
                $('.weather-widget i').removeClass().addClass('fas ' + icons[randomCondition] + ' fa-2x mb-3');
            }

            // Refresh weather every 5 minutes
            setInterval(updateWeather, 300000);

            // Activity feed auto-refresh
            function refreshActivityFeed() {
                // In a real application, this would fetch new activities via AJAX
                console.log('Refreshing activity feed...');
            }

            // Refresh activity feed every 2 minutes
            setInterval(refreshActivityFeed, 120000);

            // Enhanced button interactions
            $('.btn-primary').hover(function() {
                $(this).addClass('shadow-lg');
            }, function() {
                $(this).removeClass('shadow-lg');
            });

            // Parking space click handler
            $('.parking-spot').click(function() {
                const spaceId = $(this).text();
                const status = $(this).hasClass('available') ? 'Available' : 
                              $(this).hasClass('occupied') ? 'Occupied' : 'Reserved';
                
                alert('Space ' + spaceId + ' is ' + status);
            });

            // Auto-refresh dashboard data every 30 seconds
            setInterval(function() {
                // In a real application, this would update stats via AJAX
                console.log('Auto-refreshing dashboard data...');
            }, 30000);

            // Add loading states to buttons
            $('.btn').click(function() {
                const $this = $(this);
                const loadingText = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                
                if ($this.html() !== loadingText) {
                    $this.data('original-text', $this.html());
                    $this.html(loadingText);
                    
                    setTimeout(function() {
                        $this.html($this.data('original-text'));
                    }, 2000);
                }
            });

            // Enhanced notification system
            function showNotification(message, type = 'info') {
                const notification = `
                    <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                         style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                
                $('body').append(notification);
                
                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }


        });
    </script>
</body>
</html>
<?php } ?>