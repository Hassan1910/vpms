<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log all request information to help diagnose issues
error_log("======= NEW PAGE REQUEST =======");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data received: " . print_r($_POST, true));
    error_log("POST keys: " . implode(', ', array_keys($_POST)));
    if (isset($_POST['submit'])) {
        error_log("FORM SUBMISSION DETECTED: 'submit' key found in POST data");
    } else {
        error_log("WARNING: POST data received but 'submit' key not found");
    }
}

include('includes/dbconnection.php');
if (strlen($_SESSION['vpmsuid']) == 0) {
    header('location:logout.php');
} else {
    if (isset($_POST['submit'])) {
        // Enhanced debugging
        error_log("Form submitted - POST data received at " . date('Y-m-d H:i:s'));
        error_log("Complete POST data: " . print_r($_POST, true));
        error_log("POST keys: " . implode(', ', array_keys($_POST)));
        
        // Check if parking_number exists in POST
        if (!isset($_POST['parking_number']) || empty($_POST['parking_number'])) {
            error_log("ERROR: parking_number is missing from POST data");
            echo "<script>alert('Error: Parking space selection is missing. Please try again.');</script>";
            echo "<script>console.error('Parking number missing from form submission');</script>";
        }
        
        // Sanitize user inputs with extra error checking
        $parkingnumber = isset($_POST['parking_number']) ? mysqli_real_escape_string($con, $_POST['parking_number']) : '';
        $catename = isset($_POST['catename']) ? mysqli_real_escape_string($con, $_POST['catename']) : '';
        $vehcomp = isset($_POST['vehcomp']) ? mysqli_real_escape_string($con, $_POST['vehcomp']) : '';
        $vehreno = isset($_POST['vehreno']) ? mysqli_real_escape_string($con, $_POST['vehreno']) : '';
        $userid = $_SESSION['vpmsuid'];
        $start_time = date('Y-m-d H:i:s');
        
        // Log session data for debugging
        error_log("Session user ID: " . $userid);
        
        // Debug: Log sanitized data
        error_log("Sanitized data - Parking: $parkingnumber, Category: $catename, Company: $vehcomp, Reg: $vehreno, User: $userid");
        
        // Validation
        if(empty($parkingnumber) || empty($catename) || empty($vehcomp) || empty($vehreno)) {
            error_log("Validation failed - empty fields detected");
            echo "<script>alert('Please fill all required fields');</script>";
        } else {
            // Debug log
            error_log("Starting booking process for user: $userid, parking: $parkingnumber");
            
            // Check if parking space is still available
            $check_space = mysqli_query($con, "SELECT status FROM parking_space WHERE parking_number='$parkingnumber'");
            if($check_space && mysqli_num_rows($check_space) > 0) {
                $space_data = mysqli_fetch_assoc($check_space);
                
                if($space_data['status'] == 'available') {
                    // Set autocommit to false and begin transaction
                    mysqli_autocommit($con, false);
                    mysqli_begin_transaction($con);
                    
                    try {
                        // Insert into tblvehicle
                        $vehicle_query = mysqli_query(
                            $con,
                            "INSERT INTO tblvehicle
                                (ParkingNumber, VehicleCategory, VehicleCompanyname, RegistrationNumber, OwnerName, Status, InTime)
                            VALUES
                                ('$parkingnumber','$catename','$vehcomp','$vehreno','$userid','IN', '$start_time')"
                        );

                        if ($vehicle_query) {
                            $vehicle_id = mysqli_insert_id($con); // Last inserted vehicle ID
                            error_log("Vehicle inserted successfully with ID: $vehicle_id");

                            // Insert into bookings table
                            $booking_query = mysqli_query(
                                $con,
                                "INSERT INTO bookings (user_id, parking_number, vehicle_id, start_time, status, created_at)
                                VALUES ('$userid', '$parkingnumber', '$vehicle_id', '$start_time', 'active', NOW())"
                            );

                            if (!$booking_query) {
                                throw new Exception("Booking insert failed: " . mysqli_error($con));
                            }

                            // Get the booking ID immediately after insert
                            $booking_id = mysqli_insert_id($con);
                            
                            // Debug: Log the SQL query for verification
                            error_log("Booking query executed: INSERT INTO bookings (user_id, parking_number, vehicle_id, start_time, status, created_at) VALUES ('$userid', '$parkingnumber', '$vehicle_id', '$start_time', 'active', NOW())");
                            
                            // Debug: Check if booking_id is valid
                            error_log("Booking ID from mysqli_insert_id: $booking_id");
                            
                            // If booking_id is 0, try an alternative method
                            if ($booking_id <= 0) {
                                $backup_query = mysqli_query($con, "SELECT id FROM bookings WHERE vehicle_id = '$vehicle_id' AND user_id = '$userid' ORDER BY id DESC LIMIT 1");
                                if ($backup_query && mysqli_num_rows($backup_query) > 0) {
                                    $backup_row = mysqli_fetch_assoc($backup_query);
                                    $booking_id = $backup_row['id'];
                                    error_log("Using backup method, booking ID: $booking_id");
                                }
                            }

                            // Update parking space status
                            $update_space = mysqli_query($con, "UPDATE parking_space SET status='booked' WHERE parking_number='$parkingnumber'");
                            
                            if (!$update_space) {
                                throw new Exception("Space update failed: " . mysqli_error($con));
                            }
                            
                            // Verify booking was created correctly
                            if ($booking_id <= 0) {
                                throw new Exception("Failed to get booking ID - booking may not have been created properly");
                            }
                            
                            // Commit transaction
                            mysqli_commit($con);
                            
                            // Re-enable autocommit
                            mysqli_autocommit($con, true);
                            
                            // Debug log
                            error_log("Booking successful! Booking ID: $booking_id, Vehicle ID: $vehicle_id, User ID: $userid");
                            
                            // Success message
                            $_SESSION['booking_success'] = true;
                            $_SESSION['booking_message'] = "Your parking space has been successfully booked.";
                            $_SESSION['booking_id'] = $booking_id;
                            
                            // Check if headers can be sent
                            if (!headers_sent()) {
                                // Use PHP redirect for better reliability
                                header("Location: manage-booking.php?new_booking=$booking_id");
                                exit();
                            } else {
                                // Fallback to JavaScript redirect
                                echo "<script>window.location.href='manage-booking.php?new_booking=$booking_id'</script>";
                                exit();
                            }
                        } else {
                            throw new Exception("Vehicle insert failed: " . mysqli_error($con));
                        }
                    } catch (Exception $e) {
                        // Rollback on error
                        mysqli_rollback($con);
                        mysqli_autocommit($con, true);
                        error_log("Booking failed: " . $e->getMessage());
                        
                        // Set error message in session instead of showing alert
                        $_SESSION['booking_error'] = $e->getMessage();
                        
                        // Redirect back to booking form with error
                        if (!headers_sent()) {
                            header("Location: book-space.php?error=1");
                            exit();
                        } else {
                            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='book-space.php?error=1';</script>";
                            exit();
                        }
                    }
                } else {
                    echo "<script>alert('Sorry, this parking space is no longer available. Please select another space.');</script>";
                    echo "<script>window.location.href ='book-space.php'</script>";
                }
            } else {
                echo "<script>alert('Invalid parking space selected.');</script>";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Book Parking Space - VPMS</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,.08);
            border: none;
            margin-bottom: 24px;
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid #f1f1f1;
            padding: 20px 25px;
            font-weight: 600;
            color: #4e73df;
            font-size: 18px;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
        .card-body {
            padding: 25px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            height: auto;
            border: 1px solid #e3e6f0;
            font-size: 15px;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);
        }
        .btn-primary:hover {
            background-color: #3a5bbf;
            border-color: #3a5bbf;
            transform: translateY(-1px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, .1), 0 3px 6px rgba(0, 0, 0, .08);
        }
        label {
            font-weight: 500;
            color: #5a5c69;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control-label {
            padding-top: 12px;
        }
        .parking-space-selection {
            margin-top: 30px;
        }
        .parking-space-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e3e6f0;
        }
        .parking-space-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .space-item {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            text-align: center;
            padding: 15px 10px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        .space-item.available {
            background-color: #eaffe6;
            border-color: #c3e6cb;
        }
        .space-item.occupied {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            opacity: 0.7;
            pointer-events: none;
        }
        .space-item:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,.08);
            transform: translateY(-2px);
        }
        .space-item.selected {
            border: 2px solid #4e73df;
            background-color: rgba(78, 115, 223, 0.1);
        }
        .space-number {
            font-weight: 600;
            font-size: 18px;
            color: #2e59d9;
            margin-bottom: 5px;
        }
        .space-price {
            font-size: 13px;
            color: #858796;
        }
        
        /* Mobile responsive styles */
        @media (max-width: 768px) {
            .card-header {
                padding: 15px;
                font-size: 16px;
            }
            .card-body {
                padding: 15px;
            }
            .form-steps {
                flex-wrap: wrap;
            }
            .step {
                flex: 0 0 100%;
                margin-bottom: 10px;
            }
            .step:not(:last-child)::after {
                display: none;
            }
            .parking-space-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 10px;
            }
            .space-number {
                font-size: 14px;
            }
            .space-price {
                font-size: 11px;
            }
            .form-control-label {
                padding-top: 0;
            }
            .welcome-user {
                padding: 15px;
            }
            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
        }
        
        /* Animation for steps transition */
        .form-section {
            transition: all 0.3s ease;
        }
        
        /* Pulse animation for selected space */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.5);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(78, 115, 223, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(78, 115, 223, 0);
            }
        }
        
        .space-item.selected {
            animation: pulse 2s infinite;
        }
        
        /* Tooltips */
        [data-tooltip] {
            position: relative;
            cursor: help;
        }
        
        [data-tooltip]:before,
        [data-tooltip]:after {
            position: absolute;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: 99;
        }
        
        [data-tooltip]:before {
            content: attr(data-tooltip);
            background-color: #4e73df;
            color: #fff;
            font-size: 12px;
            padding: 7px 12px;
            border-radius: 5px;
            white-space: nowrap;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
        }
        
        [data-tooltip]:hover:before,
        [data-tooltip]:hover:after {
            visibility: visible;
            opacity: 1;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #4e73df;
        }
        .vehicle-img {
            max-width: 120px;
            margin-bottom: 15px;
            opacity: 0.7;
        }
        .summary-card {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dotted #e3e6f0;
        }
        .summary-label {
            font-weight: 500;
            color: #5a5c69;
        }
        .summary-value {
            font-weight: 600;
            color: #4e73df;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #e3e6f0;
            font-weight: 700;
            font-size: 16px;
        }
        .form-steps {
            display: flex;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step:not(:last-child)::after {
            content: "";
            position: absolute;
            top: 15px;
            right: 0;
            width: calc(100% - 30px);
            height: 2px;
            background-color: #e3e6f0;
            z-index: 0;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e3e6f0;
            color: #858796;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 1;
            font-weight: 600;
        }
        .step.active .step-number {
            background-color: #4e73df;
            color: white;
        }
        .step.completed .step-number {
            background-color: #1cc88a;
            color: white;
        }
        .step-label {
            font-size: 13px;
            color: #858796;
            font-weight: 500;
        }
        .step.active .step-label {
            color: #4e73df;
            font-weight: 600;
        }
        .step.completed .step-label {
            color: #1cc88a;
        }
    </style>
</head>
<body>

<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="content">
    <div class="animated fadeIn">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-car mr-2"></i> Book Parking Space
                    </div>
                    <div class="card-body card-block">
                        <!-- Error Message Display -->
                        <?php if(isset($_SESSION['booking_error']) || isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Booking Error:</strong> 
                            <?php 
                                if(isset($_SESSION['booking_error'])) {
                                    echo htmlspecialchars($_SESSION['booking_error']);
                                    unset($_SESSION['booking_error']); // Clear the error
                                } else {
                                    echo "An error occurred while processing your booking. Please try again.";
                                }
                            ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Success Message Display -->
                        <?php if(isset($_SESSION['booking_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['booking_message']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php 
                            unset($_SESSION['booking_success']);
                            unset($_SESSION['booking_message']);
                        ?>
                        <?php endif; ?>
                        
                        <!-- Progress Steps -->
                        <div class="form-steps">
                            <div class="step active">
                                <div class="step-number">1</div>
                                <div class="step-label">Vehicle Details</div>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <div class="step-label">Choose Space</div>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <div class="step-label">Confirm</div>
                            </div>
                        </div>

                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" class="form-horizontal" id="bookingForm" onsubmit="return validateBookingForm()">

<script>
function validateBookingForm() {
    console.log("Validating form before submission");
    
    // Debug: Check all form values
    var catename = document.getElementById('catename').value;
    var vehcomp = document.getElementById('vehcomp').value;
    var vehreno = document.getElementById('vehreno').value;
    var selected_parking_number = document.getElementById('selected_parking_number').value;
    
    console.log("Form values:", {
        catename: catename,
        vehcomp: vehcomp,
        vehreno: vehreno,
        parking_number: selected_parking_number
    });
    
    // Check if all fields are filled
    if (catename === '' || vehcomp === '' || vehreno === '') {
        alert('Please fill all vehicle details.');
        return false;
    }
    
    // Check if parking space is selected
    if (selected_parking_number === '') {
        alert('Please select a parking space to complete your booking.');
        
        // Switch to section 2
        document.getElementById('section3').style.display = 'none';
        document.getElementById('section2').style.display = 'block';
        
        // Update step indicators
        var steps = document.querySelectorAll('.step');
        steps[1].classList.add('active');
        steps[1].classList.remove('completed');
        steps[2].classList.remove('active');
        
        return false;
    }
    
    // Show loading animation on submit button
    var submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Processing...';
    submitBtn.disabled = true;
    
    // Ensure parking number is passed correctly
    console.log("Double checking parking number before submission: " + selected_parking_number);
    
    // Make extra sure parking_number is in the form data
    var parkingInput = document.getElementById('selected_parking_number');
    if (parkingInput) {
        console.log("Parking input exists with value: " + parkingInput.value);
        // Make sure it has the right name attribute
        parkingInput.name = 'parking_number';
    } else {
        console.error("Parking input element not found!");
    }
    
    console.log("Form validation passed, submitting...");
    return true;
}
</script>
                            <!-- Vehicle Information Section -->
                            <div class="form-section" id="section1" style="display: block;">
                                <div class="section-title">
                                    <i class="fas fa-info-circle mr-2"></i> Vehicle Information
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-control-label">Vehicle Category</label>
                                        <select name="catename" id="catename" class="form-control" required>
                                            <option value="">Select Category</option>
                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM tblcategory");
                                            while ($row = mysqli_fetch_array($query)) {
                                                echo '<option value="' . $row['VehicleCat'] . '">' . $row['VehicleCat'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <small class="form-text text-muted">Select the type of your vehicle</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-control-label">Vehicle Company</label>
                                        <input type="text" name="vehcomp" id="vehcomp" class="form-control" required placeholder="e.g., Toyota, Honda, BMW">
                                        <small class="form-text text-muted">Enter the manufacturer of your vehicle</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-control-label">Registration Number</label>
                                        <input type="text" name="vehreno" id="vehreno" class="form-control" required placeholder="e.g., ABC-123-XYZ">
                                        <small class="form-text text-muted">Enter your vehicle's registration number</small>
                                    </div>
                                </div>
                                
                                <div class="text-right mt-4">
                                    <button type="button" class="btn btn-primary" id="nextBtn1" onclick="goToSection2()">
                                        Next <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                                
                                <script>
                                function goToSection2() {
                                    console.log("goToSection2 function called");
                                    
                                    // Validate first section
                                    var catename = document.getElementById('catename');
                                    var vehcomp = document.getElementById('vehcomp');
                                    var vehreno = document.getElementById('vehreno');
                                    
                                    if (catename.value === '') {
                                        catename.classList.add('is-invalid');
                                        catename.focus();
                                        return;
                                    }
                                    
                                    if (vehcomp.value === '') {
                                        vehcomp.classList.add('is-invalid');
                                        vehcomp.focus();
                                        return;
                                    }
                                    
                                    if (vehreno.value === '') {
                                        vehreno.classList.add('is-invalid');
                                        vehreno.focus();
                                        return;
                                    }
                                    
                                    // Update step indicators
                                    var steps = document.querySelectorAll('.step');
                                    steps[0].classList.add('completed');
                                    steps[0].classList.remove('active');
                                    steps[1].classList.add('active');
                                    
                                    // Show next section
                                    document.getElementById('section1').style.display = 'none';
                                    document.getElementById('section2').style.display = 'block';
                                }
                                </script>
                            </div>

                            <!-- Parking Space Selection Section -->
                            <div class="form-section" id="section2" style="display: none;">
                                <div class="section-title">
                                    <i class="fas fa-map-marker-alt mr-2"></i> Select Parking Space
                                </div>
                                
                                <div class="parking-space-header">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-6">
                                            <h6 class="mb-0">Available Parking Spaces</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-md-end">
                                                <div class="mr-4">
                                                    <span class="badge badge-success mr-2">•</span>
                                                    <small>Available</small>
                                                </div>
                                                <div>
                                                    <span class="badge badge-danger mr-2">•</span>
                                                    <small>Occupied</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="searchSpace" placeholder="Search for parking space..." oninput="searchSpaces(this.value)">
                                                
                                                <script>
                                                function searchSpaces(searchTerm) {
                                                    console.log("Searching for:", searchTerm);
                                                    searchTerm = searchTerm.toLowerCase();
                                                    
                                                    var spaceItems = document.querySelectorAll('.space-item');
                                                    
                                                    for (var i = 0; i < spaceItems.length; i++) {
                                                        var item = spaceItems[i];
                                                        var spaceNumber = item.getAttribute('data-space').toLowerCase();
                                                        
                                                        if (searchTerm === '' || spaceNumber.includes(searchTerm)) {
                                                            item.style.display = '';
                                                        } else {
                                                            item.style.display = 'none';
                                                        }
                                                    }
                                                }
                                                </script>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                                </div>
                                                <select class="form-control" id="priceFilter" onchange="filterByPrice(this.value)">
                                                    <option value="all">All Price Ranges</option>
                                                    <option value="0-50">KSh 0 - KSh 50/hr</option>
                                                    <option value="51-100">KSh 51 - KSh 100/hr</option>
                                                    <option value="101+">KSh 101+/hr</option>
                                                </select>
                                                
                                                <script>
                                                function filterByPrice(filterValue) {
                                                    console.log("Filtering by price:", filterValue);
                                                    
                                                    var spaceItems = document.querySelectorAll('.space-item');
                                                    
                                                    for (var i = 0; i < spaceItems.length; i++) {
                                                        var item = spaceItems[i];
                                                        var priceText = item.querySelector('.space-price').textContent;
                                                        var price = parseFloat(priceText.replace('KSh', '').replace('/hr', '').replace(',', '').trim());
                                                        
                                                        if (filterValue === 'all') {
                                                            item.style.display = '';
                                                        } else if (filterValue === '0-50' && price >= 0 && price <= 50) {
                                                            item.style.display = '';
                                                        } else if (filterValue === '51-100' && price > 50 && price <= 100) {
                                                            item.style.display = '';
                                                        } else if (filterValue === '101+' && price > 100) {
                                                            item.style.display = '';
                                                        } else {
                                                            item.style.display = 'none';
                                                        }
                                                    }
                                                }
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="parking-space-grid">
                                    <?php
                                    // Get all parking spaces
                                    $result = mysqli_query($con, "SELECT * FROM parking_space");
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $parking_number = htmlspecialchars($row['parking_number']);
                                            $status = $row['status'];
                                            $price_per_hour = $row['price_per_hour'];
                                            
                                            $spaceClass = ($status == 'available') ? 'available' : 'occupied';
                                            $disabled = ($status == 'available') ? '' : 'disabled';
                                            
                                            if ($status == 'available') {
                                                echo '<div class="space-item ' . $spaceClass . '" data-space="' . $parking_number . '" onclick="selectParkingSpace(this, \'' . $parking_number . '\')">';
                                            } else {
                                                echo '<div class="space-item ' . $spaceClass . '" data-space="' . $parking_number . '" ' . $disabled . '>';
                                            }
                                            echo '<div class="space-number">' . $parking_number . '</div>';
                                            echo '<div class="space-price">KSh ' . number_format($price_per_hour, 2) . '/hr</div>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<div class="col-12 text-center py-4"><p>No parking spaces found</p></div>';
                                    }
                                    ?>
                                </div>

                                <!-- Hidden input to store the selected parking space -->
                                <input type="hidden" name="parking_number" id="selected_parking_number" required>
                                <!-- Debug values for form tracking -->
                                <div id="debug_values" style="display: none;"></div>

                                <div id="spaceSelection" class="alert alert-info" style="display: none;">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span id="selectionText">Please select a parking space to continue.</span>
                                </div>

                                <div class="text-right mt-4">
                                    <button type="button" class="btn btn-secondary mr-2" id="backBtn2" onclick="goToSection1()">
                                        <i class="fas fa-arrow-left mr-2"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-primary" id="nextBtn2" onclick="goToSection3()">
                                        Next <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                                
                                <script>
                                function goToSection1() {
                                    console.log("goToSection1 function called");
                                    
                                    // Update step indicators
                                    var steps = document.querySelectorAll('.step');
                                    steps[0].classList.add('active');
                                    steps[0].classList.remove('completed');
                                    steps[1].classList.remove('active');
                                    
                                    // Show previous section
                                    document.getElementById('section2').style.display = 'none';
                                    document.getElementById('section1').style.display = 'block';
                                }
                                
                                function goToSection3() {
                                    console.log("goToSection3 function called");
                                    
                                    // Validate second section
                                    var selected_parking_number = document.getElementById('selected_parking_number');
                                    var spaceSelection = document.getElementById('spaceSelection');
                                    
                                    if (selected_parking_number.value === '') {
                                        spaceSelection.classList.remove('alert-success');
                                        spaceSelection.classList.add('alert-danger');
                                        spaceSelection.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> Please select a parking space to continue.';
                                        spaceSelection.style.display = 'block';
                                        return;
                                    }
                                    
                                    // Extra validation to ensure parking number is set with correct name attribute
                                    console.log("Selected parking space before section 3: " + selected_parking_number.value);
                                    selected_parking_number.name = 'parking_number'; // Ensure name attribute is correctly set
                                    
                                    // Update step indicators
                                    var steps = document.querySelectorAll('.step');
                                    steps[1].classList.add('completed');
                                    steps[1].classList.remove('active');
                                    steps[2].classList.add('active');
                                    
                                    // Update summary
                                    document.getElementById('summary_category').textContent = document.getElementById('catename').value;
                                    document.getElementById('summary_company').textContent = document.getElementById('vehcomp').value;
                                    document.getElementById('summary_regno').textContent = document.getElementById('vehreno').value;
                                    document.getElementById('summary_space').textContent = selected_parking_number.value;
                                    
                                    // Show next section
                                    document.getElementById('section2').style.display = 'none';
                                    document.getElementById('section3').style.display = 'block';
                                    
                                    // Additional check - make sure form will submit correctly
                                    var form = document.getElementById('bookingForm');
                                    console.log("Form action: " + form.action);
                                    console.log("Form method: " + form.method);
                                    
                                    // Check all input names for debugging
                                    var inputs = form.querySelectorAll('input, select');
                                    var inputNames = [];
                                    for (var i = 0; i < inputs.length; i++) {
                                        inputNames.push(inputs[i].name + '=' + inputs[i].value);
                                    }
                                    console.log("Form inputs: " + inputNames.join(', '));
                                }
                                </script>
                            </div>

                            <!-- Summary and Confirmation Section -->
                            <div class="form-section" id="section3" style="display: none;">
                                <div class="section-title">
                                    <i class="fas fa-clipboard-check mr-2"></i> Booking Summary
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="summary-card">
                                            <div class="summary-item">
                                                <div class="summary-label">Vehicle Category</div>
                                                <div class="summary-value" id="summary_category">-</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Vehicle Company</div>
                                                <div class="summary-value" id="summary_company">-</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Registration Number</div>
                                                <div class="summary-value" id="summary_regno">-</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Parking Space</div>
                                                <div class="summary-value" id="summary_space">-</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Booking Date</div>
                                                <div class="summary-value"><?php echo date('d M Y, h:i A'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-center d-flex flex-column align-items-center justify-content-center">
                                        <img src="../assets/img/images.png" alt="Vehicle Parking" class="vehicle-img">
                                        <div class="alert alert-success mt-3">
                                            <i class="fas fa-check-circle mr-2"></i> Your space will be reserved upon confirmation.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-right mt-4">
                                    <button type="button" class="btn btn-secondary mr-2" id="backBtn3" onclick="goToSection2FromSection3()">
                                        <i class="fas fa-arrow-left mr-2"></i> Previous
                                    </button>
                                    <button type="submit" name="submit" value="1" id="confirmBookingBtn" class="btn btn-success">
                                        <i class="fas fa-check mr-2"></i> Confirm Booking
                                    </button>
                                </div>
                                
                                <script>
                                    // Add a click handler for the confirm button (as a backup to form submission)
                                    document.getElementById('confirmBookingBtn').addEventListener('click', function(e) {
                                        console.log("Confirm button clicked directly");
                                        
                                        // Don't prevent default form submission
                                        // Just add extra debugging
                                        var parkingInput = document.getElementById('selected_parking_number');
                                        console.log("Parking number on click: " + parkingInput.value);
                                        
                                        // Double check name attribute and value
                                        if (parkingInput.name !== 'parking_number') {
                                            console.warn("Parking input name was not correct, fixing...");
                                            parkingInput.name = 'parking_number';
                                        }
                                        
                                        if (!parkingInput.value) {
                                            console.error("Parking number is empty, trying to get from summary");
                                            var summarySpace = document.getElementById('summary_space');
                                            if (summarySpace && summarySpace.textContent) {
                                                parkingInput.value = summarySpace.textContent;
                                                console.log("Updated parking number from summary: " + parkingInput.value);
                                            }
                                        }
                                        
                                        // Add a manual hidden field as a fallback
                                        var form = document.getElementById('bookingForm');
                                        var backupInput = document.createElement('input');
                                        backupInput.type = 'hidden';
                                        backupInput.name = 'submit';
                                        backupInput.value = '1';
                                        form.appendChild(backupInput);
                                        
                                        // Submit the form manually as a fallback
                                        console.log("Manually submitting form");
                                        form.submit();
                                    });
                                </script>
                                
                                <script>
                                function goToSection2FromSection3() {
                                    console.log("goToSection2FromSection3 function called");
                                    
                                    // Update step indicators
                                    var steps = document.querySelectorAll('.step');
                                    steps[1].classList.add('active');
                                    steps[1].classList.remove('completed');
                                    steps[2].classList.remove('active');
                                    
                                    // Show previous section
                                    document.getElementById('section3').style.display = 'none';
                                    document.getElementById('section2').style.display = 'block';
                                }
                                </script>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div>

<?php include_once('includes/footer.php'); ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
<script src="../admin/assets/js/main.js"></script>

<script type="text/javascript">
// Debug console log to check if script is loading
console.log("Booking script initialized");

// Wait for document to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded");
    
    // Check if there's an error parameter in URL to preserve form state
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === '1') {
        console.log("Error detected, preserving form state");
        // You can add code here to restore form state if needed
    }
    
    // Initialize tooltips if Bootstrap is properly loaded
    if(typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Auto-capitalize vehicle registration
    document.getElementById('vehreno').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Form navigation with smooth transitions - Using direct DOM methods
    document.getElementById('nextBtn1').addEventListener('click', function() {
        console.log("Next button 1 clicked");
        
        // Validate first section
        var catename = document.getElementById('catename');
        var vehcomp = document.getElementById('vehcomp');
        var vehreno = document.getElementById('vehreno');
        
        if (catename.value === '') {
            catename.classList.add('is-invalid');
            catename.focus();
            return;
        } else {
            catename.classList.remove('is-invalid');
        }
        
        if (vehcomp.value === '') {
            vehcomp.classList.add('is-invalid');
            vehcomp.focus();
            return;
        } else {
            vehcomp.classList.remove('is-invalid');
        }
        
        if (vehreno.value === '') {
            vehreno.classList.add('is-invalid');
            vehreno.focus();
            return;
        } else {
            vehreno.classList.remove('is-invalid');
        }
        
        // Update step indicators with animation
        var steps = document.querySelectorAll('.step');
        steps[0].classList.add('completed');
        steps[0].classList.remove('active');
        steps[1].classList.add('active');
        
        // Show next section with display change
        document.getElementById('section1').style.display = 'none';
        document.getElementById('section2').style.display = 'block';
        
        // Scroll to top of form
        var cardHeader = document.querySelector('.card-header');
        if(cardHeader) {
            window.scrollTo({
                top: cardHeader.offsetTop - 20,
                behavior: 'smooth'
            });
        }
    });
    
    document.getElementById('backBtn2').addEventListener('click', function() {
        console.log("Back button 2 clicked");
        
        // Update step indicators
        var steps = document.querySelectorAll('.step');
        steps[0].classList.add('active');
        steps[0].classList.remove('completed');
        steps[1].classList.remove('active');
        
        // Show previous section
        document.getElementById('section2').style.display = 'none';
        document.getElementById('section1').style.display = 'block';
    });
    
    document.getElementById('nextBtn2').addEventListener('click', function() {
        console.log("Next button 2 clicked");
        
        // Validate second section
        var selected_parking_number = document.getElementById('selected_parking_number');
        var spaceSelection = document.getElementById('spaceSelection');
        
        if (selected_parking_number.value === '') {
            spaceSelection.classList.remove('alert-success');
            spaceSelection.classList.add('alert-danger');
            spaceSelection.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> Please select a parking space to continue.';
            spaceSelection.style.display = 'block';
            
            // Flash animation effect
            setTimeout(function() { spaceSelection.style.display = 'none'; }, 500);
            setTimeout(function() { spaceSelection.style.display = 'block'; }, 1000);
            setTimeout(function() { spaceSelection.style.display = 'none'; }, 1500);
            setTimeout(function() { spaceSelection.style.display = 'block'; }, 2000);
            return;
        }
        
        // Update step indicators
        var steps = document.querySelectorAll('.step');
        steps[1].classList.add('completed');
        steps[1].classList.remove('active');
        steps[2].classList.add('active');
        
        // Update summary
        document.getElementById('summary_category').textContent = document.getElementById('catename').value;
        document.getElementById('summary_company').textContent = document.getElementById('vehcomp').value;
        document.getElementById('summary_regno').textContent = document.getElementById('vehreno').value;
        document.getElementById('summary_space').textContent = selected_parking_number.value;
        
        // Show next section
        document.getElementById('section2').style.display = 'none';
        document.getElementById('section3').style.display = 'block';
        
        // Scroll to top of form
        var cardHeader = document.querySelector('.card-header');
        if(cardHeader) {
            window.scrollTo({
                top: cardHeader.offsetTop - 20,
                behavior: 'smooth'
            });
        }
    });
    
    document.getElementById('backBtn3').addEventListener('click', function() {
        console.log("Back button 3 clicked");
        
        // Update step indicators
        var steps = document.querySelectorAll('.step');
        steps[1].classList.add('active');
        steps[1].classList.remove('completed');
        steps[2].classList.remove('active');
        
        // Show previous section
        document.getElementById('section3').style.display = 'none';
        document.getElementById('section2').style.display = 'block';
    });
    
    // Enhanced parking space selection
    var availableSpaces = document.querySelectorAll('.space-item.available');
    availableSpaces.forEach(function(space) {
        space.addEventListener('click', function() {
            console.log("Parking space clicked");
            
            // Remove selected class from all spaces
            document.querySelectorAll('.space-item').forEach(function(s) {
                s.classList.remove('selected');
            });
            
            // Add selected class to clicked space with animation
            this.classList.add('selected');
            
            // Update hidden input with selected space
            var spaceNumber = this.getAttribute('data-space');
            document.getElementById('selected_parking_number').value = spaceNumber;
            
            // Get price from the selected space
            var priceText = this.querySelector('.space-price').textContent;
            
            // Update selection text and show message with success style
            var spaceSelection = document.getElementById('spaceSelection');
            spaceSelection.classList.remove('alert-info', 'alert-danger');
            spaceSelection.classList.add('alert-success');
            spaceSelection.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Space <strong>' + spaceNumber + '</strong> selected (' + priceText + ')';
            spaceSelection.style.display = 'block';
            
            // Visual indication for next button
            var nextBtn2 = document.getElementById('nextBtn2');
            nextBtn2.classList.add('animated', 'pulse');
            setTimeout(function() {
                nextBtn2.classList.remove('animated', 'pulse');
            }, 1000);
        });
    });
    
    // Search functionality for parking spaces
    document.getElementById('searchSpace').addEventListener('input', function() {
        var searchTerm = this.value.toLowerCase();
        var spaceItems = document.querySelectorAll('.space-item');
        
        spaceItems.forEach(function(item) {
            var spaceNumber = item.getAttribute('data-space').toLowerCase();
            
            if (searchTerm === '' || spaceNumber.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Price filter functionality
    document.getElementById('priceFilter').addEventListener('change', function() {
        var filterValue = this.value;
        var spaceItems = document.querySelectorAll('.space-item');
        
        spaceItems.forEach(function(item) {
            var priceText = item.querySelector('.space-price').textContent;
            var price = parseFloat(priceText.replace('KSh', '').replace('/hr', ''));
            
            if (filterValue === 'all' || 
                (filterValue === '0-50' && price >= 0 && price <= 50) ||
                (filterValue === '51-100' && price > 50 && price <= 100) ||
                (filterValue === '101+' && price > 100)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Add data attributes to parking spaces for filtering
    document.querySelectorAll('.space-item').forEach(function(item) {
        var priceText = item.querySelector('.space-price').textContent;
        var price = parseFloat(priceText.replace('KSh', '').replace('/hr', ''));
        item.setAttribute('data-price', price);
    });
    
    // Form submission validation and animation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        console.log("Form submission event triggered");
        
        // Debug: Log all form data before submission
        var formData = new FormData(this);
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        var selected_parking_number = document.getElementById('selected_parking_number');
        
        if (selected_parking_number.value === '') {
            console.log("Form submission blocked - no parking space selected");
            e.preventDefault();
            
            // Show error
            var spaceSelection = document.getElementById('spaceSelection');
            spaceSelection.classList.remove('alert-success');
            spaceSelection.classList.add('alert-danger');
            spaceSelection.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> Please select a parking space to complete your booking.';
            spaceSelection.style.display = 'block';
            
            // Go back to section 2
            document.getElementById('section3').style.display = 'none';
            document.getElementById('section2').style.display = 'block';
            
            // Update step indicators
            var steps = document.querySelectorAll('.step');
            steps[1].classList.add('active');
            steps[1].classList.remove('completed');
            steps[2].classList.remove('active');
            
            return false;
        }
        
        console.log("Form submission proceeding...");
        
        // Show loading animation
        var submitBtn = document.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Processing...';
        submitBtn.disabled = true;
        
        // Allow form submission
        return true;
    });
    
    // Add tooltips to elements (using title attribute instead of data-tooltip)
    document.querySelectorAll('.space-item.available').forEach(function(item) {
        item.setAttribute('title', 'Click to select this space');
    });
    
    document.querySelectorAll('.space-item.occupied').forEach(function(item) {
        item.setAttribute('title', 'This space is already occupied');
    });
    
    // Add field tooltips
    document.getElementById('catename').setAttribute('title', 'Select your vehicle type');
    document.getElementById('vehcomp').setAttribute('title', 'Enter your vehicle manufacturer');
    document.getElementById('vehreno').setAttribute('title', 'Enter your license plate number');
});

// Define the selectParkingSpace function globally
function selectParkingSpace(element, spaceNumber) {
    console.log("selectParkingSpace called with:", spaceNumber);
    
    // Remove selected class from all spaces
    document.querySelectorAll('.space-item').forEach(function(s) {
        s.classList.remove('selected');
    });
    
    // Add selected class to clicked space
    element.classList.add('selected');
    
    // Update hidden input with selected space
    document.getElementById('selected_parking_number').value = spaceNumber;
    
    // Update any debug element to track selection
    var debugElement = document.getElementById('debug_values');
    if (debugElement) {
        debugElement.innerHTML = 'Selected space: ' + spaceNumber;
    }
    
    // Get price from the selected space
    var priceText = element.querySelector('.space-price').textContent;
    
    // Update selection text and show message with success style
    var spaceSelection = document.getElementById('spaceSelection');
    spaceSelection.classList.remove('alert-info', 'alert-danger');
    spaceSelection.classList.add('alert-success');
    spaceSelection.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Space <strong>' + spaceNumber + '</strong> selected (' + priceText + ')';
    spaceSelection.style.display = 'block';
    
    console.log("Parking space selection completed:", spaceNumber);
}

// Also add jQuery version as a fallback
$(document).ready(function() {
    console.log("jQuery ready event triggered");
    
    // Form navigation with smooth transitions using jQuery
    $('#nextBtn1').on('click', function() {
        console.log("Next button 1 clicked (jQuery)");
        
        // Validate first section
        if ($('#catename').val() === '') {
            $('#catename').addClass('is-invalid').focus();
            return;
        } else {
            $('#catename').removeClass('is-invalid');
        }
        
        if ($('#vehcomp').val() === '') {
            $('#vehcomp').addClass('is-invalid').focus();
            return;
        } else {
            $('#vehcomp').removeClass('is-invalid');
        }
        
        if ($('#vehreno').val() === '') {
            $('#vehreno').addClass('is-invalid').focus();
            return;
        } else {
            $('#vehreno').removeClass('is-invalid');
        }
        
        // Update step indicators with animation
        $('.step').eq(0).addClass('completed').removeClass('active');
        $('.step').eq(1).addClass('active');
        
        // Show next section with fade effect
        $('#section1').fadeOut(200, function() {
            $('#section2').fadeIn(200);
            
            // Scroll to top of form
            $('html, body').animate({
                scrollTop: $('.card-header').offset().top - 20
            }, 300);
        });
    });
    
    $('#backBtn2').on('click', function() {
        console.log("Back button 2 clicked (jQuery)");
        
        // Update step indicators
        $('.step').eq(0).addClass('active').removeClass('completed');
        $('.step').eq(1).removeClass('active');
        
        // Show previous section with fade effect
        $('#section2').fadeOut(200, function() {
            $('#section1').fadeIn(200);
        });
    });
    
    $('#nextBtn2').on('click', function() {
        console.log("Next button 2 clicked (jQuery)");
        
        // Validate second section
        if ($('#selected_parking_number').val() === '') {
            $('#spaceSelection')
                .removeClass('alert-success')
                .addClass('alert-danger')
                .html('<i class="fas fa-exclamation-circle mr-2"></i> Please select a parking space to continue.')
                .show()
                .fadeOut(500).fadeIn(500).fadeOut(500).fadeIn(500);
            return;
        }
        
        // Update step indicators
        $('.step').eq(1).addClass('completed').removeClass('active');
        $('.step').eq(2).addClass('active');
        
        // Update summary
        $('#summary_category').text($('#catename').val());
        $('#summary_company').text($('#vehcomp').val());
        $('#summary_regno').text($('#vehreno').val());
        $('#summary_space').text($('#selected_parking_number').val());
        
        // Show next section with fade effect
        $('#section2').fadeOut(200, function() {
            $('#section3').fadeIn(200);
            
            // Scroll to top of form
            $('html, body').animate({
                scrollTop: $('.card-header').offset().top - 20
            }, 300);
        });
    });
});
</script>

<script>
// Simple test to verify that JavaScript is working on the page
console.log("Initial page load test");

// Add click event listeners after the DOM is loaded
window.addEventListener('load', function() {
    console.log("Window fully loaded");
    
    // Test click on next button
    var nextBtn1 = document.getElementById('nextBtn1');
    if(nextBtn1) {
        console.log("Found nextBtn1");
        nextBtn1.addEventListener('click', function() {
            console.log("nextBtn1 direct click handler fired");
        });
    } else {
        console.error("nextBtn1 not found in DOM");
    }
});
</script>



</body>
</html>
