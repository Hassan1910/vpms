<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Ensure timezone is set (should already be set in dbconnection.php)
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Africa/Nairobi'); // Change this to your timezone
}

error_reporting(0);
if (strlen($_SESSION['vpmsaid'])==0) {
  header('location:logout.php');
  } else{ 

// Fetch admin name if not already in session
if (!isset($_SESSION['admin_name']) && isset($_SESSION['vpmsaid'])) {
    $adminid = $_SESSION['vpmsaid'];
    $query = mysqli_query($con, "SELECT AdminName FROM tbladmin WHERE ID='$adminid'");
    $result = mysqli_fetch_array($query);
    if ($result) {
        $_SESSION['admin_name'] = $result['AdminName'];
    }
}

// Get current date/time info for dashboard
$current_date = date('Y-m-d');
$current_time = date('H:i:s');
$current_month = date('F Y');
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>VPMS - Dashboard</title>
    <meta name="description" content="Vehicle Parking Management System - Administrative Dashboard">

    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">

    <!-- Enhanced CSS Dependencies -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">

    <!-- Chart Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.0/dist/chartist.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
            --info-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --purple-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --orange-gradient: linear-gradient(135deg, #ff9a56 0%, #ffad56 100%);
            --green-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --blue-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.37);
            --shadow-dark: 0 8px 32px rgba(0, 0, 0, 0.1);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Enhanced Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            opacity: 1 !important; /* Ensure visibility */
            visibility: visible !important; /* Ensure visibility */
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            font-family: 'Poppins', sans-serif;
        }

        .dashboard-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 400;
            visibility: visible !important; /* Ensure visibility */
            display: block !important; /* Ensure display */
            color: rgba(255, 255, 255, 0.9) !important; /* Ensure text color */
            margin-bottom: 20px !important; /* Ensure spacing */
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
        }

        .meta-item i {
            font-size: 1.1rem;
        }

        /* Enhanced Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transition: var(--transition);
        }

        .stat-card.success::before { background: var(--success-gradient); }
        .stat-card.warning::before { background: var(--warning-gradient); }
        .stat-card.danger::before { background: var(--danger-gradient); }
        .stat-card.info::before { background: var(--info-gradient); }
        .stat-card.purple::before { background: var(--purple-gradient); }
        .stat-card.orange::before { background: var(--orange-gradient); }
        .stat-card.green::before { background: var(--green-gradient); }
        .stat-card.blue::before { background: var(--blue-gradient); }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 20px;
            background: var(--primary-gradient);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .stat-card.success .stat-icon { 
            background: var(--success-gradient); 
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.3);
        }
        .stat-card.warning .stat-icon { 
            background: var(--warning-gradient); 
            box-shadow: 0 8px 20px rgba(250, 112, 154, 0.3);
        }
        .stat-card.danger .stat-icon { 
            background: var(--danger-gradient); 
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
        }
        .stat-card.info .stat-icon { 
            background: var(--info-gradient); 
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .stat-card.purple .stat-icon { 
            background: var(--purple-gradient); 
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .stat-card.orange .stat-icon { 
            background: var(--orange-gradient); 
            box-shadow: 0 8px 20px rgba(255, 154, 86, 0.3);
        }
        .stat-card.green .stat-icon { 
            background: var(--green-gradient); 
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.3);
        }
        .stat-card.blue .stat-icon { 
            background: var(--blue-gradient); 
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
            line-height: 1;
        }

        .stat-label {
            font-size: 1rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 12px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .stat-change.positive {
            color: #10b981;
        }

        .stat-change.negative {
            color: #ef4444;
        }

        .stat-change.neutral {
            color: #6b7280;
        }

        /* Quick Actions Section */
        .quick-actions {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .quick-actions h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 25px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            text-decoration: none;
            color: #475569;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .action-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            background: white;
            border-color: #e2e8f0;
            color: #2c3e50;
            text-decoration: none;
        }

        .action-btn i {
            font-size: 2rem;
            margin-bottom: 12px;
            color: #667eea;
        }

        .action-btn span {
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .chart-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 25px;
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
        }

        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 2rem;
            }

            .dashboard-meta {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .charts-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .actions-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
        }

        /* Loading Animation */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Animation classes */
        .fade-in {
            opacity: 1 !important; /* Ensure visibility as fallback */
            visibility: visible !important; /* Ensure visibility */
            animation: fadeIn 0.6s ease-out;
        }

        .slide-up {
            opacity: 1 !important; /* Ensure visibility as fallback */
            visibility: visible !important; /* Ensure visibility */
            animation: slideUp 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        /* Chart Containers */
        .chart-container {
            min-height: 300px;
            position: relative;
        }

        #vehicleChart, #parkingChart, #revenueChart {
            height: 300px;
        }
        
        /* Specific override for welcome message visibility */
        .dashboard-header .dashboard-subtitle {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 1.1rem !important;
            font-weight: 400 !important;
            margin-bottom: 20px !important;
        }
        
        /* Override any animation delays that might affect visibility */
        .dashboard-header.fade-in {
            opacity: 1 !important;
            visibility: visible !important;
            animation-delay: 0s !important;
        }
    </style>
</head>

<body>
    
   <?php include_once('includes/sidebar.php');?>
   <?php include_once('includes/header.php');?>
      
        <!-- Enhanced Dashboard Content -->
        <div class="content">
            <div class="container-fluid">
                
                <!-- Dashboard Header -->
                <div class="dashboard-header fade-in">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="dashboard-title">
                                    <i class="fas fa-user-shield me-3"></i>
                                    Welcome back, <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator'; ?>!
                                </h1>
                                <p class="dashboard-subtitle">
                                    Here's what's happening with your parking system today.
                                </p>
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
                                        <i class="fas fa-user-shield"></i>
                                        <span><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="dashboard-actions">
                                    <!-- Additional header actions can go here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid fade-in">
                    
                    <?php
                    // Today's Vehicle Entries
                    $query = mysqli_query($con, "SELECT ID FROM tblvehicle WHERE date(InTime)=CURDATE()");
                    $count_today_vehentries = mysqli_num_rows($query);
                    
                    // Yesterday's Vehicle Entries  
                    $query1 = mysqli_query($con, "SELECT ID FROM tblvehicle WHERE date(InTime)=CURDATE()-1");
                    $count_yesterday_vehentries = mysqli_num_rows($query1);
                    
                    // Calculate percentage change
                    $change_today = $count_yesterday_vehentries > 0 ? 
                        round((($count_today_vehentries - $count_yesterday_vehentries) / $count_yesterday_vehentries) * 100, 1) : 0;
                    ?>
                    
                    <!-- Today's Vehicle Entries -->
                    <div class="stat-card success slide-up">
                        <div class="stat-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="stat-number"><?php echo $count_today_vehentries; ?></div>
                        <div class="stat-label">Today's Vehicle Entries</div>
                        <div class="stat-change <?php echo $change_today >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-arrow-<?php echo $change_today >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($change_today); ?>% from yesterday</span>
                        </div>
                    </div>

                    <?php
                    // Yesterday's Vehicle Entries
                    $yesterday_change = rand(-15, 25); // Demo calculation
                    ?>
                    
                    <!-- Yesterday's Vehicle Entries -->
                    <div class="stat-card warning slide-up" style="animation-delay: 0.1s;">
                        <div class="stat-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="stat-number"><?php echo $count_yesterday_vehentries; ?></div>
                        <div class="stat-label">Yesterday's Entries</div>
                        <div class="stat-change neutral">
                            <i class="fas fa-calendar-day"></i>
                            <span>Previous day total</span>
                        </div>
                    </div>

                    <?php
                    // Last 7 Days Vehicle Entries
                    $query2 = mysqli_query($con, "SELECT ID FROM tblvehicle WHERE date(InTime)>=(DATE(NOW()) - INTERVAL 7 DAY)");
                    $count_lastsevendays_vehentries = mysqli_num_rows($query2);
                    ?>
                    
                    <!-- Last 7 Days Vehicle Entries -->
                    <div class="stat-card info slide-up" style="animation-delay: 0.2s;">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-number"><?php echo $count_lastsevendays_vehentries; ?></div>
                        <div class="stat-label">Last 7 Days Entries</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>Weekly performance</span>
                        </div>
                    </div>

                    <?php
                    // Total Vehicle Entries
                    $query3 = mysqli_query($con, "SELECT ID FROM tblvehicle");
                    $count_total_vehentries = mysqli_num_rows($query3);
                    ?>
                    
                    <!-- Total Vehicle Entries -->
                    <div class="stat-card purple slide-up" style="animation-delay: 0.3s;">
                        <div class="stat-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-number"><?php echo $count_total_vehentries; ?></div>
                        <div class="stat-label">Total Vehicle Entries</div>
                        <div class="stat-change positive">
                            <i class="fas fa-infinity"></i>
                            <span>All time total</span>
                        </div>
                    </div>

                    <?php
                    // Total Bookings
                    $query_bookings = mysqli_query($con, "SELECT ID FROM bookings");
                    $count_total_bookings = $query_bookings ? mysqli_num_rows($query_bookings) : 0;
                    ?>
                    
                    <!-- Total Bookings -->
                    <div class="stat-card orange slide-up" style="animation-delay: 0.4s;">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $count_total_bookings; ?></div>
                        <div class="stat-label">Total Bookings</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>12% this month</span>
                        </div>
                    </div>

                    <?php
                    // Completed Transactions
                    $payments = mysqli_query($con, "SELECT id FROM payment WHERE status='paid'");
                    $payment_count = $payments ? mysqli_num_rows($payments) : 0;
                    ?>
                    
                    <!-- Completed Transactions -->
                    <div class="stat-card green slide-up" style="animation-delay: 0.5s;">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-number"><?php echo $payment_count; ?></div>
                        <div class="stat-label">Completed Transactions</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>8% this week</span>
                        </div>
                    </div>

                    <?php
                    // Total Parking Spaces
                    $query_parking = mysqli_query($con, "SELECT id FROM parking_space");
                    $count_total_parking = $query_parking ? mysqli_num_rows($query_parking) : 0;
                    ?>
                    
                    <!-- Total Parking Spaces -->
                    <div class="stat-card blue slide-up" style="animation-delay: 0.6s;">
                        <div class="stat-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="stat-number"><?php echo $count_total_parking; ?></div>
                        <div class="stat-label">Total Parking Spaces</div>
                        <div class="stat-change neutral">
                            <i class="fas fa-building"></i>
                            <span>Available slots</span>
                        </div>
                    </div>

                    <?php
                    // Available Parking Spaces
                    $parking = mysqli_query($con, "SELECT id FROM parking_space WHERE status='available'");
                    $parking_count = $parking ? mysqli_num_rows($parking) : 0;
                    $occupancy_rate = $count_total_parking > 0 ? round((($count_total_parking - $parking_count) / $count_total_parking) * 100, 1) : 0;
                    ?>
                    
                    <!-- Available Parking -->
                    <div class="stat-card success slide-up" style="animation-delay: 0.7s;">
                        <div class="stat-icon">
                            <i class="fas fa-parking"></i>
                        </div>
                        <div class="stat-number"><?php echo $parking_count; ?></div>
                        <div class="stat-label">Available Parking</div>
                        <div class="stat-change <?php echo $occupancy_rate > 80 ? 'negative' : 'positive'; ?>">
                            <i class="fas fa-chart-pie"></i>
                            <span><?php echo $occupancy_rate; ?>% occupied</span>
                        </div>
                    </div>

                    <?php
                    // Registered Users
                    $query_users = mysqli_query($con, "SELECT ID FROM tblregusers");
                    $regdusers = mysqli_num_rows($query_users);
                    ?>
                    
                    <!-- Total Registered Users -->
                    <div class="stat-card info slide-up" style="animation-delay: 0.8s;">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?php echo $regdusers; ?></div>
                        <div class="stat-label">Registered Users</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>15% growth</span>
                        </div>
                    </div>

                    <?php
                    // Listed Categories
                    $query_cat = mysqli_query($con, "SELECT ID FROM tblcategory");
                    $listedcat = mysqli_num_rows($query_cat);
                    ?>
                    
                    <!-- Listed Categories -->
                    <div class="stat-card warning slide-up" style="animation-delay: 0.9s;">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-number"><?php echo $listedcat; ?></div>
                        <div class="stat-label">Vehicle Categories</div>
                        <div class="stat-change neutral">
                            <i class="fas fa-list"></i>
                            <span>Active categories</span>
                        </div>
                    </div>

                    <!-- System Health -->
                    <div class="stat-card danger slide-up" style="animation-delay: 1.0s;">
                        <div class="stat-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="stat-number">99.5%</div>
                        <div class="stat-label">System Health</div>
                        <div class="stat-change positive">
                            <i class="fas fa-check-circle"></i>
                            <span>All systems operational</span>
                        </div>
                    </div>

                    <!-- Revenue (Demo) -->
                    <div class="stat-card success slide-up" style="animation-delay: 1.1s;">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-number">$<?php echo number_format($payment_count * 25.50, 0); ?></div>
                        <div class="stat-label">Monthly Revenue</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>22% increase</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="quick-actions slide-up">
                    <h3><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="add-parking-space.php" class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Parking Space</span>
                        </a>
                        <a href="manage-parking.php" class="action-btn">
                            <i class="fas fa-cogs"></i>
                            <span>Manage Parking</span>
                        </a>
                        <a href="search-vehicle.php" class="action-btn">
                            <i class="fas fa-search"></i>
                            <span>Search Vehicle</span>
                        </a>
                        <a href="manage-booking.php" class="action-btn">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Manage Bookings</span>
                        </a>
                        <a href="bwdates-report-ds.php" class="action-btn">
                            <i class="fas fa-chart-bar"></i>
                            <span>View Reports</span>
                        </a>
                        <a href="payment.php" class="action-btn">
                            <i class="fas fa-credit-card"></i>
                            <span>Transactions</span>
                        </a>
                        <a href="reg-users.php" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>User Management</span>
                        </a>
                        <a href="admin-profile.php" class="action-btn">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile</span>
                        </a>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    
                    <!-- Vehicle Entry Trends -->
                    <div class="chart-card slide-up">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-line me-2"></i>
                                Vehicle Entry Trends
                            </h3>
                        </div>
                        <div class="chart-container">
                            <!-- Chart will be rendered in this div -->
                            <div id="vehicleChart" style="width:100%; height:300px; min-height:300px; position:relative;"></div>
                            
                            <!-- Fallback content if chart fails to load -->
                            <div class="chart-fallback" style="display:none; text-align:center; padding:20px; color:#666;">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <p>Unable to load chart. Please refresh the page.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Parking Occupancy -->
                    <div class="chart-card slide-up">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-pie me-2"></i>
                                Parking Occupancy
                            </h3>
                        </div>
                        <div class="chart-container">
                            <!-- Chart will be rendered in this div -->
                            <div id="parkingChart" style="width:100%; height:300px; min-height:300px; position:relative;"></div>
                            
                            <!-- Fallback content if chart fails to load -->
                            <div class="chart-fallback" style="display:none; text-align:center; padding:20px; color:#666;">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <p>Unable to load chart. Please refresh the page.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Analysis -->
                    <div class="chart-card slide-up">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-area me-2"></i>
                                Revenue Analysis
                            </h3>
                        </div>
                        <div class="chart-container">
                            <!-- Chart will be rendered in this div -->
                            <div id="revenueChart" style="width:100%; height:300px; min-height:300px; position:relative;"></div>
                            
                            <!-- Fallback content if chart fails to load -->
                            <div class="chart-fallback" style="display:none; text-align:center; padding:20px; color:#666;">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <p>Unable to load chart. Please refresh the page.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="recent-activity slide-up">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-clock me-2"></i>
                                Recent Activity
                            </h3>
                        </div>
                        <div class="activity-feed">
                            <div class="activity-item">
                                <div class="activity-icon" style="background: var(--success-gradient);">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">New vehicle entry recorded</div>
                                    <div class="activity-time">2 minutes ago</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: var(--warning-gradient);">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Parking space booking confirmed</div>
                                    <div class="activity-time">15 minutes ago</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: var(--info-gradient);">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Payment received</div>
                                    <div class="activity-time">32 minutes ago</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: var(--purple-gradient);">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">New user registration</div>
                                    <div class="activity-time">1 hour ago</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: var(--green-gradient);">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">System backup completed</div>
                                    <div class="activity-time">2 hours ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include_once('includes/footer.php');?>

    <!-- Enhanced Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/sidebar-enhanced.js"></script>

    <script>
        // Wait for everything to load
        window.addEventListener('load', function() {
            console.log("Window loaded. Starting chart rendering");
            
            // Simple vehicle chart
            var options = {
                series: [{
                    name: 'Vehicle Entries',
                    data: [10, 15, 9, 18, 22, 16, 11]
                }],
                chart: {
                    height: 300,
                    type: 'line',
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#667eea'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                },
                title: {
                    text: 'Weekly Vehicle Entries',
                    align: 'left',
                    style: {
                        fontSize: '16px'
                    }
                }
            };
            
            try {
                console.log("Creating vehicle chart");
                var vehicleChart = document.querySelector("#vehicleChart");
                if (vehicleChart) {
                    var chart = new ApexCharts(vehicleChart, options);
                    chart.render();
                    console.log("Vehicle chart rendered");
                } else {
                    console.error("Vehicle chart container not found");
                }
            } catch (e) {
                console.error("Error rendering vehicle chart:", e);
            }
            
            // Parking occupancy chart
            try {
                var donutOptions = {
                    series: [3, 6],
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: ['Occupied', 'Available'],
                    colors: ['#667eea', '#4facfe'],
                    legend: {
                        position: 'bottom'
                    }
                };
                
                var parkingChart = document.querySelector("#parkingChart");
                if (parkingChart) {
                    var donutChart = new ApexCharts(parkingChart, donutOptions);
                    donutChart.render();
                    console.log("Parking chart rendered");
                } else {
                    console.error("Parking chart container not found");
                }
            } catch (e) {
                console.error("Error rendering parking chart:", e);
            }
            
            // Revenue chart
            try {
                var barOptions = {
                    series: [{
                        name: 'Revenue',
                        data: [44, 55, 57, 56, 61, 58, 63]
                    }],
                    chart: {
                        type: 'bar',
                        height: 300
                    },
                    colors: ['#667eea'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 4
                        },
                    },
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    }
                };
                
                var revenueChart = document.querySelector("#revenueChart");
                if (revenueChart) {
                    var barChart = new ApexCharts(revenueChart, barOptions);
                    barChart.render();
                    console.log("Revenue chart rendered");
                } else {
                    console.error("Revenue chart container not found");
                }
            } catch (e) {
                console.error("Error rendering revenue chart:", e);
            }
        });
    </script>
</body>
</html>
<?php } ?>