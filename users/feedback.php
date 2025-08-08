<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['vpmsuid']) || strlen($_SESSION['vpmsuid']) == 0) {
    header('location:logout.php');
    exit();
}

$uid = $_SESSION['vpmsuid'];
$msg = "";
$error = "";

// Handle feedback submission
if (isset($_POST['submit'])) {
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $message = mysqli_real_escape_string($con, $_POST['message']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $priority = mysqli_real_escape_string($con, $_POST['priority']);
    
    if (!empty($subject) && !empty($message)) {
        $query = "INSERT INTO tblfeedback (UserID, Subject, Message, Category, Priority, Status) 
                  VALUES ('$uid', '$subject', '$message', '$category', '$priority', 'Open')";
        
        if (mysqli_query($con, $query)) {
            $msg = "Feedback submitted successfully! We'll get back to you soon.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Get user info
$userQuery = mysqli_query($con, "SELECT FirstName, LastName FROM tblregusers WHERE ID='$uid'");
$userInfo = mysqli_fetch_array($userQuery);
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <title>VPMS - Submit Feedback</title>
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar-style.css">
    <style>
        .feedback-form {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 20px 0;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .btn-submit {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .page-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <?php include_once('includes/sidebar.php'); ?>
    <?php include_once('includes/header.php'); ?>

    <div class="breadcrumbs">
        <div class="breadcrumbs-inner">
            <div class="row m-0">
                <div class="col-sm-4">
                    <div class="page-header float-left">
                        <div class="page-title">
                            <h1>Submit Feedback</h1>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="page-header float-right">
                        <div class="page-title">
                            <ol class="breadcrumb text-right">
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li class="active">Submit Feedback</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <?php if($msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $msg; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="feedback-form">
                        <h3 class="page-title">
                            <i class="fas fa-comment-dots me-3"></i>
                            We Value Your Feedback
                        </h3>
                        <p class="text-muted mb-4">
                            Help us improve our parking management system by sharing your thoughts, suggestions, or reporting any issues.
                        </p>

                        <form method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category">Category <span class="text-danger">*</span></label>
                                        <select class="form-control" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="General">General Feedback</option>
                                            <option value="Bug Report">Bug Report</option>
                                            <option value="Feature Request">Feature Request</option>
                                            <option value="Payment Issue">Payment Issue</option>
                                            <option value="Booking Problem">Booking Problem</option>
                                            <option value="User Interface">User Interface</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="priority">Priority <span class="text-danger">*</span></label>
                                        <select class="form-control" id="priority" name="priority" required>
                                            <option value="Low">Low</option>
                                            <option value="Medium" selected>Medium</option>
                                            <option value="High">High</option>
                                            <option value="Critical">Critical</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       placeholder="Brief description of your feedback" required maxlength="255">
                            </div>

                            <div class="form-group">
                                <label for="message">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          placeholder="Please provide detailed information about your feedback..." required></textarea>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" name="submit" class="btn btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                </button>
                                <a href="my-feedback.php" class="btn btn-outline-secondary ml-3">
                                    <i class="fas fa-list me-2"></i>View My Feedback
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="../admin/assets/js/main.js"></script>
</body>
</html>
