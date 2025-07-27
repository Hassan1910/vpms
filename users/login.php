<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Form validation function
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$errors = [];
$loginAttempted = false;

if(isset($_POST['login']))
{
    $loginAttempted = true;
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "CSRF validation failed. Please try again.";
    } else {
        // Sanitize input
        $emailcon = validateInput($_POST['emailcont']);
        $password = $_POST['password'];
        
        // Check if the input is empty
        if (empty($emailcon)) {
            $errors[] = "Email or contact number is required.";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
        
        // If no validation errors, proceed with login
        if (empty($errors)) {
            // Add debugging
            error_log("Login attempt with: " . $emailcon);
            
            // Handle numeric mobile number
            $mobile_numeric = is_numeric($emailcon) ? $emailcon : 0;
            
            // First check if user exists by email
            $stmt = mysqli_prepare($con, "SELECT ID, MobileNumber, Password FROM tblregusers WHERE Email=? OR MobileNumber=? OR MobileNumber=?");
            mysqli_stmt_bind_param($stmt, "ssd", $emailcon, $emailcon, $mobile_numeric);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                error_log("User found with ID: " . $row['ID']);
                
                // For debugging - check password format
                error_log("Stored password hash: " . $row['Password'] . " (length: " . strlen($row['Password']) . ")");
                
                // User found, check if password is using the new format (password_hash)
                if (strlen($row['Password']) > 40) {
                    // Likely using new password_hash format
                    if (password_verify($password, $row['Password'])) {
                        // Password is correct, proceed with login
                        $_SESSION['vpmsuid'] = $row['ID'];
                        $_SESSION['vpmsumn'] = $row['MobileNumber'];
                        
                        // Generate new CSRF token after successful login
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        
                        error_log("Successful login with password_verify");
                        
                        // Redirect to dashboard
                        header('location: dashboard.php');
                        exit;
                    } else {
                        error_log("Failed password_verify check");
                        $errors[] = "Invalid password.";
                    }
                } else {
                    // Likely using old MD5 format
                    $md5_password = md5($password);
                    error_log("Checking MD5: " . $md5_password . " vs stored: " . $row['Password']);
                    
                    if ($md5_password === $row['Password']) {
                        // Password matches old format, update to new format
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $update_stmt = mysqli_prepare($con, "UPDATE tblregusers SET Password=? WHERE ID=?");
                        mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $row['ID']);
                        $update_result = mysqli_stmt_execute($update_stmt);
                        
                        error_log("Password updated to new format: " . ($update_result ? "success" : "failed"));
                        
                        // Login the user
                        $_SESSION['vpmsuid'] = $row['ID'];
                        $_SESSION['vpmsumn'] = $row['MobileNumber'];
                        
                        // Generate new CSRF token after successful login
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        
                        // Redirect to dashboard
                        header('location: dashboard.php');
                        exit;
                    } else {
                        error_log("Failed MD5 password check");
                        $errors[] = "Invalid password.";
                    }
                }
            } else {
                error_log("No user found with email/mobile: " . $emailcon);
                $errors[] = "Invalid email/mobile number or password.";
            }
        }
    }
}
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPMS - Login</title>
    
    <!-- Tailwind CSS and Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .animate-fade-in {
            animation: fadeIn 1s ease-in;
        }
        .animate-slide-up {
            animation: slideUp 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .form-input-field {
            @apply w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all;
        }
        .btn-primary {
            @apply bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors duration-300 w-full flex justify-center items-center;
        }
        .form-error {
            @apply bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <img src="../assets/img/images.png" alt="VPMS Logo" class="h-10 mr-3">
                <h1 class="text-2xl font-bold text-indigo-600">VPMS</h1>
            </div>
            
            <!-- Navigation -->
            <nav class="hidden md:flex space-x-6">
                <a href="../index.php" class="font-medium text-gray-600 hover:text-indigo-600 transition">Home</a>
                <a href="../index.php#about" class="font-medium text-gray-600 hover:text-indigo-600 transition">About</a>
                <a href="../index.php#features" class="font-medium text-gray-600 hover:text-indigo-600 transition">Features</a>
                <a href="../index.php#contact" class="font-medium text-gray-600 hover:text-indigo-600 transition">Contact</a>
                <a href="signup.php" class="font-medium text-indigo-600 hover:text-indigo-800 transition">Register</a>
            </nav>
            
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-500 hover:text-indigo-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="container mx-auto px-4 py-2 space-y-2">
                <a href="../index.php" class="block py-2 text-gray-600 hover:text-indigo-600">Home</a>
                <a href="../index.php#about" class="block py-2 text-gray-600 hover:text-indigo-600">About</a>
                <a href="../index.php#features" class="block py-2 text-gray-600 hover:text-indigo-600">Features</a>
                <a href="../index.php#contact" class="block py-2 text-gray-600 hover:text-indigo-600">Contact</a>
                <a href="signup.php" class="block py-2 text-indigo-600 hover:text-indigo-800">Register</a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12">
        <div class="flex flex-wrap -mx-4">
            <!-- Left column - image and info -->
            <div class="w-full md:w-1/2 px-4 mb-8 md:mb-0">
                <div class="relative rounded-3xl overflow-hidden shadow-xl h-full flex flex-col">
                    <div class="relative h-72 md:h-96">
                        <img src="includes/images/parking-concept.jpg" alt="Parking" class="w-full h-full object-cover">
                        <div class="absolute inset-0 gradient-bg opacity-80"></div>
                        <div class="absolute inset-0 p-8 flex flex-col justify-center text-white">
                            <h2 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in">Welcome Back!</h2>
                            <p class="text-lg mb-6 animate-slide-up">Access your account to manage your parking needs with ease and efficiency.</p>
                            <div class="flex items-center space-x-2 animate-slide-up">
                                <i class="fas fa-shield-alt text-yellow-300"></i>
                                <span>Secure & encrypted login</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 flex-grow">
                        <h3 class="text-xl font-semibold mb-4 text-indigo-800">Why Choose VPMS?</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-car text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Easy Parking Management</h4>
                                    <p class="text-gray-600 text-sm">Find and book parking spaces with just a few clicks</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-credit-card text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Secure Payments</h4>
                                    <p class="text-gray-600 text-sm">Pay for parking securely through our platform</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-history text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Booking History</h4>
                                    <p class="text-gray-600 text-sm">Access your complete parking history anytime</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Right column - login form -->
            <div class="w-full md:w-1/2 px-4">
                <div class="bg-white rounded-3xl shadow-xl p-8 md:p-10 animate-fade-in">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800">Sign In</h2>
                        <p class="text-gray-600 mt-2">Enter your credentials to access your account</p>
                    </div>
                    
                    <?php if (!empty($errors) && $loginAttempted): ?>
                        <div class="form-error">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                <strong class="text-red-700">Login failed</strong>
                            </div>
                            <ul class="list-disc list-inside text-red-600 text-sm">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" id="loginForm" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div>
                            <label for="emailcont" class="block text-gray-700 font-medium mb-2">Email or Contact Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    id="emailcont"
                                    name="emailcont" 
                                    placeholder="Enter your email or phone number" 
                                    required 
                                    class="form-input-field pl-10"
                                    value="<?php echo isset($_POST['emailcont']) ? htmlspecialchars($_POST['emailcont']) : ''; ?>"
                                >
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-2">
                                <label for="password" class="block text-gray-700 font-medium">Password</label>
                                <a href="forgot-password.php" class="text-sm text-indigo-600 hover:text-indigo-800">Forgot password?</a>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="password"
                                    name="password" 
                                    placeholder="Enter your password" 
                                    required 
                                    class="form-input-field pl-10"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="togglePassword" class="fas fa-eye text-gray-400 cursor-pointer hover:text-gray-600"></i>
                                </div>
                            </div>
                            <?php if (!empty($errors) && $loginAttempted): ?>
                                <small class="text-blue-600 mt-2 block text-sm">
                                    Note: If you've registered before the recent system update, you may need to use your old password or reset your password.
                                </small>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" name="login" class="btn-primary">
                            <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                        </button>
                        
                        <div class="flex items-center justify-center space-x-2">
                            <span class="text-gray-500">Don't have an account?</span>
                            <a href="signup.php" class="text-indigo-600 font-medium hover:text-indigo-800">Register here</a>
                        </div>
                        
                        <div class="relative flex items-center justify-center py-4">
                            <div class="border-t border-gray-300 flex-grow"></div>
                            <span class="mx-4 text-gray-500 text-sm">or continue with</span>
                            <div class="border-t border-gray-300 flex-grow"></div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <a href="../admin/index.php" class="flex items-center justify-center py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user-shield mr-2 text-indigo-600"></i>
                                <span class="text-sm">Admin</span>
                            </a>
                            <a href="../index.php" class="flex items-center justify-center py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-home mr-2 text-indigo-600"></i>
                                <span class="text-sm">Home</span>
                            </a>
                            <a href="../index.php#contact" class="flex items-center justify-center py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-headset mr-2 text-indigo-600"></i>
                                <span class="text-sm">Help</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <img src="../assets/img/images.png" alt="VPMS Logo" class="h-8 mr-2 bg-white p-1 rounded">
                    <span class="text-xl font-bold">VPMS</span>
                </div>
                <div class="text-sm text-gray-400">
                    &copy; <?php echo date('Y'); ?> Vehicle Parking Management System. All rights reserved.
                </div>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile Menu Toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
        
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
                            </label>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
    <script src="../admin/assets/js/main.js"></script>

</body>
</html>
