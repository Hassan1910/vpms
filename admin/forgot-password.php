<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

$error_message = '';
$success_message = '';

if(isset($_POST['submit'])) {
    $contactno = mysqli_real_escape_string($con, trim($_POST['contactno']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));

    if(!empty($email) && !empty($contactno)) {
        // Validate email format
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } 
        // Validate phone number format (basic validation)
        else if(!preg_match('/^[0-9]{10,15}$/', $contactno)) {
            $error_message = "Please enter a valid mobile number (10-15 digits).";
        } 
        else {
            $query = mysqli_query($con, "SELECT ID FROM tbladmin WHERE Email='$email' AND MobileNumber='$contactno'");
            
            if(!$query) {
                $error_message = "Database error occurred. Please try again.";
            } else {
                $ret = mysqli_fetch_array($query);
                if($ret && count($ret) > 0) {
                    $_SESSION['contactno'] = $contactno;
                    $_SESSION['email'] = $email;
                    $success_message = "Verification successful! Redirecting to reset password...";
                    header('refresh:2;url=reset-password.php');
                } else {
                    $error_message = "No admin account found with these details. Please check your email and mobile number.";
                }
            }
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>VPMS - Reset Password</title>
    <meta name="description" content="Vehicle Parking Management System - Password Recovery">

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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.37);
            --shadow-dark: 0 8px 32px rgba(0, 0, 0, 0.3);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
            animation: backgroundShift 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes backgroundShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Floating Elements */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 70%;
            left: 80%;
            animation-delay: 5s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 50%;
            left: 5%;
            animation-delay: 10s;
        }

        .shape:nth-child(4) {
            width: 40px;
            height: 40px;
            top: 20%;
            left: 85%;
            animation-delay: 15s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.7;
            }
            33% {
                transform: translateY(-30px) rotate(120deg);
                opacity: 1;
            }
            66% {
                transform: translateY(30px) rotate(240deg);
                opacity: 0.7;
            }
        }

        /* Main Container */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Login Card */
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            padding: 0;
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--secondary-gradient);
        }

        /* Header */
        .login-header {
            text-align: center;
            padding: 40px 40px 20px;
            background: rgba(255, 255, 255, 0.05);
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: var(--secondary-gradient);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            box-shadow: 0 8px 20px rgba(240, 147, 251, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .login-title {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            font-weight: 400;
        }

        /* Form */
        .login-form {
            padding: 30px 40px 40px;
        }

        /* Alert Messages */
        .alert-container {
            margin-bottom: 25px;
        }

        .custom-alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            font-weight: 500;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: slideInDown 0.5s ease-out;
        }

        .alert-error {
            background: rgba(250, 112, 154, 0.2);
            color: #ff6b8a;
            border: 1px solid rgba(250, 112, 154, 0.3);
        }

        .alert-success {
            background: rgba(79, 172, 254, 0.2);
            color: #4facfe;
            border: 1px solid rgba(79, 172, 254, 0.3);
        }

        /* Form Groups */
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-group label {
            position: absolute;
            top: 50%;
            left: 50px;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
            font-weight: 400;
            transition: var(--transition);
            pointer-events: none;
            background: transparent;
            z-index: 2;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 400;
            height: 56px;
            padding: 0 20px 0 50px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 1);
            border-color: rgba(240, 147, 251, 0.8);
            box-shadow: 0 0 0 0.2rem rgba(240, 147, 251, 0.25);
            outline: none;
            color: #2c3e50;
        }

        .form-control:focus + label,
        .form-control:not(:placeholder-shown) + label,
        .form-control.has-value + label,
        .form-group.focused label,
        label.active {
            top: -8px;
            left: 16px;
            font-size: 12px;
            color: #f093fb;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 8px;
            border-radius: 4px;
            transform: translateY(0);
        }

        .form-control::placeholder {
            color: transparent;
        }

        .form-control:focus::placeholder {
            color: #999;
        }

        /* Ensure labels don't interfere with input text */
        .form-group.focused .form-control::placeholder,
        .form-control.has-value::placeholder {
            color: #999;
        }

        /* Input Icons */
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 18px;
            z-index: 1;
        }

        /* Buttons */
        .btn-reset {
            background: var(--secondary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            height: 56px;
            width: 100%;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(240, 147, 251, 0.4);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .btn-reset:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Loading State */
        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Links */
        .login-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .login-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .login-link:hover {
            color: #f093fb;
            text-decoration: none;
        }

        .login-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #f093fb;
            transition: var(--transition);
        }

        .login-link:hover::after {
            width: 100%;
        }

        /* Info Box */
        .info-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }

        .info-box i {
            font-size: 32px;
            color: #f093fb;
            margin-bottom: 15px;
        }

        .info-box h6 {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .info-box p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .login-card {
                margin: 20px;
                max-width: calc(100% - 40px);
            }

            .login-header {
                padding: 30px 25px 15px;
            }

            .login-form {
                padding: 20px 25px 30px;
            }

            .login-title {
                font-size: 24px;
            }

            .login-subtitle {
                font-size: 14px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }

            .login-links {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Security Badge */
        .security-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 8px 12px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            font-weight: 500;
        }

        .security-badge i {
            color: #f093fb;
            margin-right: 4px;
        }

        /* Enhanced Focus States */
        .form-control:focus {
            animation: focusPulse 0.3s ease-out;
        }

        @keyframes focusPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Security Badge -->
    <div class="security-badge">
        <i class="fas fa-shield-alt"></i>
        Password Recovery
    </div>

    <!-- Main Login Container -->
    <div class="login-container">
        <div class="login-card fade-in">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
                <h1 class="login-title">Reset Password</h1>
                <p class="login-subtitle">Enter your details to recover your account</p>
            </div>

            <!-- Form -->
            <div class="login-form">
                <!-- Info Box -->
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <h6>Account Recovery</h6>
                    <p>Please enter your registered email address and mobile number to verify your identity and reset your password.</p>
                </div>

                <!-- Alert Messages -->
                <?php if(!empty($error_message)): ?>
                <div class="alert-container">
                    <div class="custom-alert alert-error">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(!empty($success_message)): ?>
                <div class="alert-container">
                    <div class="custom-alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                </div>
                <?php endif; ?>

                <form method="post" id="resetForm" novalidate>
                    <!-- Email Field -->
                    <div class="form-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            class="form-control" 
                            name="email" 
                            id="email"
                            placeholder="Email Address"
                            required
                            autocomplete="email"
                            maxlength="100"
                        >
                        <label for="email">Email Address</label>
                    </div>

                    <!-- Mobile Number Field -->
                    <div class="form-group">
                        <i class="fas fa-mobile-alt input-icon"></i>
                        <input 
                            type="tel" 
                            class="form-control" 
                            name="contactno" 
                            id="contactno"
                            placeholder="Mobile Number"
                            required
                            autocomplete="tel"
                            maxlength="15"
                            pattern="[0-9]{10,15}"
                        >
                        <label for="contactno">Mobile Number</label>
                    </div>

                    <!-- Reset Button -->
                    <button type="submit" name="submit" class="btn btn-reset" id="resetBtn">
                        <span class="btn-text">Verify & Reset Password</span>
                    </button>

                    <!-- Links -->
                    <div class="login-links">
                        <a href="index.php" class="login-link">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Login
                        </a>
                        <a href="../index.php" class="login-link">
                            <i class="fas fa-home me-1"></i>
                            Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        $(document).ready(function() {
            // Form validation and submission
            $('#resetForm').on('submit', function(e) {
                e.preventDefault();
                
                const email = $('#email').val().trim();
                const contactno = $('#contactno').val().trim();
                const resetBtn = $('#resetBtn');
                const btnText = resetBtn.find('.btn-text');

                // Validation
                if(!email || !contactno) {
                    showAlert('Please fill in all fields.', 'error');
                    return;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!emailRegex.test(email)) {
                    showAlert('Please enter a valid email address.', 'error');
                    return;
                }

                // Phone validation
                const phoneRegex = /^[0-9]{10,15}$/;
                if(!phoneRegex.test(contactno)) {
                    showAlert('Please enter a valid mobile number (10-15 digits).', 'error');
                    return;
                }

                // Show loading state
                resetBtn.addClass('btn-loading').prop('disabled', true);
                btnText.text('Verifying...');

                // Submit form after a brief delay for UX
                setTimeout(() => {
                    this.submit();
                }, 800);
            });

            // Input animations and label management
            $('.form-control').on('focus', function() {
                $(this).parent().addClass('focused');
                $(this).next('label').addClass('active');
            });

            $('.form-control').on('blur', function() {
                if(!$(this).val()) {
                    $(this).parent().removeClass('focused');
                    $(this).next('label').removeClass('active');
                    $(this).removeClass('has-value');
                } else {
                    $(this).addClass('has-value');
                }
            });

            $('.form-control').on('input', function() {
                if($(this).val()) {
                    $(this).addClass('has-value');
                    $(this).next('label').addClass('active');
                } else {
                    $(this).removeClass('has-value');
                    if(!$(this).is(':focus')) {
                        $(this).next('label').removeClass('active');
                    }
                }
            });

            // Initialize labels for pre-filled inputs
            $('.form-control').each(function() {
                if($(this).val()) {
                    $(this).addClass('has-value');
                    $(this).next('label').addClass('active');
                }
            });

            // Auto-focus first empty field
            const firstEmpty = $('.form-control').filter(function() {
                return !$(this).val();
            }).first();
            
            if(firstEmpty.length) {
                setTimeout(() => firstEmpty.focus(), 500);
            }

            // Phone number formatting
            $('#contactno').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if(value.length > 15) {
                    value = value.substring(0, 15);
                }
                $(this).val(value);
            });

            // Auto-dismiss success messages and redirect
            <?php if(!empty($success_message)): ?>
            setTimeout(() => {
                $('.alert-success').fadeOut();
            }, 3000);
            <?php endif; ?>

            // Keyboard shortcuts
            $(document).keydown(function(e) {
                // Ctrl/Cmd + Enter to submit
                if((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
                    $('#resetForm').submit();
                }
            });

            // Prevent multiple rapid submissions
            let isSubmitting = false;
            $('#resetForm').on('submit', function() {
                if(isSubmitting) {
                    return false;
                }
                isSubmitting = true;
                setTimeout(() => isSubmitting = false, 2000);
            });

            // Animate elements on load
            setTimeout(() => {
                $('.login-card').addClass('animate__animated animate__fadeInUp');
            }, 200);
        });

        // Alert function
        function showAlert(message, type) {
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
            
            const alertHtml = `
                <div class="alert-container">
                    <div class="custom-alert ${alertClass} animate__animated animate__fadeInDown">
                        <i class="fas ${icon} me-2"></i>
                        ${message}
                    </div>
                </div>
            `;
            
            $('.alert-container').remove();
            $('.info-box').after(alertHtml);
            
            setTimeout(() => {
                $('.alert-container').fadeOut();
            }, 5000);
        }

        // Page load optimization
        window.addEventListener('load', function() {
            // Remove any loading states
            document.body.classList.add('loaded');
            
            // Preload critical resources
            const criticalResources = [
                'index.php',
                'reset-password.php'
            ];
            
            criticalResources.forEach(url => {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                document.head.appendChild(link);
            });
        });
    </script>
</body>
</html>
