<?php
session_start();
error_reporting(0);
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
$success = false;

if(isset($_POST['submit']))
{
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "CSRF validation failed. Please try again.";
    } else {
        // Validate and sanitize inputs
        $fname = validateInput($_POST['firstname']);
        $lname = validateInput($_POST['lastname']);
        $contno = validateInput($_POST['mobilenumber']);
        $email = validateInput($_POST['email']);
        $password = $_POST['password'];
        
        // Basic validation
        if (empty($fname) || strlen($fname) < 2) {
            $errors[] = "First name must be at least 2 characters long.";
        } elseif (!preg_match("/^[a-zA-Z\s]+$/", $fname)) {
            $errors[] = "First name must contain only letters and spaces.";
        }
        
        if (empty($lname) || strlen($lname) < 2) {
            $errors[] = "Last name must be at least 2 characters long.";
        } elseif (!preg_match("/^[a-zA-Z\s]+$/", $lname)) {
            $errors[] = "Last name must contain only letters and spaces.";
        }
        
        if (empty($contno) || !preg_match("/^\d{10}$/", $contno)) {
            $errors[] = "Mobile number must be 10 digits.";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        // Password strength check
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        } elseif (!preg_match("#[0-9]+#", $password)) {
            $errors[] = "Password must include at least one number.";
        } elseif (!preg_match("#[A-Z]+#", $password)) {
            $errors[] = "Password must include at least one uppercase letter.";
        } elseif (!preg_match("#[a-z]+#", $password)) {
            $errors[] = "Password must include at least one lowercase letter.";
        }
        
        // If no validation errors, proceed
        if (empty($errors)) {
            // Check if email or mobile already exists
            $stmt = mysqli_prepare($con, "SELECT Email FROM tblregusers WHERE Email=? OR MobileNumber=?");
            mysqli_stmt_bind_param($stmt, "ss", $email, $contno);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = "This email or mobile number is already associated with another account.";
            } else {
                // Hash password with better algorithm
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Prepare the insert statement
                $insert_stmt = mysqli_prepare($con, "INSERT INTO tblregusers(FirstName, LastName, MobileNumber, Email, Password) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($insert_stmt, "sssss", $fname, $lname, $contno, $email, $hashed_password);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    $success = true;
                    // Generate new CSRF token after successful submission
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $errors[] = "Database error: " . mysqli_error($con);
                }
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
    <title>VPMS - Registration</title>
    
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
        .form-success {
            @apply bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded;
        }
        .strength-meter {
            height: 4px;
            background: #ddd;
            margin: 8px 0;
            border-radius: 2px;
        }
        .strength-meter div {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .strength-weak { width: 30%; background-color: #f56565; }
        .strength-fair { width: 50%; background-color: #ed8936; }
        .strength-good { width: 75%; background-color: #ecc94b; }
        .strength-strong { width: 100%; background-color: #48bb78; }
    </style>

    <script type="text/javascript">
        function checkpass() {
            const form = document.getElementById('signupForm');
            const firstName = form.firstname.value;
            const lastName = form.lastname.value;
            const password = form.password.value;
            const repeatPassword = form.repeatpassword.value;
            let isValid = true;
            
            // Clear any previous error messages
            const errorElements = document.querySelectorAll('.validation-error');
            errorElements.forEach(el => el.remove());
            
            // Validate first name (letters only)
            if (firstName && !/^[a-zA-Z\s]+$/.test(firstName)) {
                showError(form.firstname, 'First name must contain only letters and spaces');
                isValid = false;
            }
            
            // Validate last name (letters only)
            if (lastName && !/^[a-zA-Z\s]+$/.test(lastName)) {
                showError(form.lastname, 'Last name must contain only letters and spaces');
                isValid = false;
            }
            
            // Check password match
            if (password !== repeatPassword) {
                showError(form.repeatpassword, 'Password and Repeat Password fields do not match');
                isValid = false;
            }
            
            // Check password complexity
            if (password.length < 8) {
                showError(form.password, 'Password must be at least 8 characters long');
                isValid = false;
            } else if (!/[A-Z]/.test(password)) {
                showError(form.password, 'Password must include at least one uppercase letter');
                isValid = false;
            } else if (!/[a-z]/.test(password)) {
                showError(form.password, 'Password must include at least one lowercase letter');
                isValid = false;
            } else if (!/[0-9]/.test(password)) {
                showError(form.password, 'Password must include at least one number');
                isValid = false;
            }
            
            return isValid;
        }

        function showError(inputElement, message) {
            const parent = inputElement.parentElement;
            const errorEl = document.createElement('div');
            errorEl.className = 'text-red-500 text-sm mt-1 validation-error';
            errorEl.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> ${message}`;
            parent.appendChild(errorEl);
            inputElement.classList.add('border-red-500');
            inputElement.focus();
        }

        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthMeter = document.getElementById('strength-meter');
            const strengthText = document.getElementById('strength-text');
            
            // Remove all classes
            strengthMeter.className = 'strength-meter';
            strengthMeter.innerHTML = '<div></div>';
            const meterBar = strengthMeter.querySelector('div');
            
            // Calculate strength
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            
            // Update meter and text
            if (password.length === 0) {
                meterBar.style.width = '0%';
                strengthText.textContent = '';
            } else if (strength < 50) {
                meterBar.className = 'strength-weak';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-sm text-red-500';
            } else if (strength < 75) {
                meterBar.className = 'strength-fair';
                strengthText.textContent = 'Fair';
                strengthText.className = 'text-sm text-orange-500';
            } else if (strength < 100) {
                meterBar.className = 'strength-good';
                strengthText.textContent = 'Good';
                strengthText.className = 'text-sm text-yellow-500';
            } else {
                meterBar.className = 'strength-strong';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-sm text-green-500';
            }
        }
    </script>
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
                <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-800 transition">Login</a>
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
                <a href="login.php" class="block py-2 text-indigo-600 hover:text-indigo-800">Login</a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12">
        <div class="flex flex-wrap -mx-4">
            <!-- Left column - image and info -->
            <div class="w-full md:w-1/2 px-4 mb-8 md:mb-0">
                <div class="relative rounded-3xl overflow-hidden shadow-xl h-full flex flex-col">
                    <div class="relative h-72 md:h-96">
                        <img src="includes/images/using-parking.jpg" alt="Parking" class="w-full h-full object-cover">
                        <div class="absolute inset-0 gradient-bg opacity-80"></div>
                        <div class="absolute inset-0 p-8 flex flex-col justify-center text-white">
                            <h2 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in">Join Our Community</h2>
                            <p class="text-lg mb-6 animate-slide-up">Register now to access premium parking features and a seamless parking experience.</p>
                            <div class="flex items-center space-x-2 animate-slide-up">
                                <i class="fas fa-shield-alt text-yellow-300"></i>
                                <span>Your data is secure with us</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 flex-grow">
                        <h3 class="text-xl font-semibold mb-4 text-indigo-800">Benefits of Registration</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-check text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Easy Booking</h4>
                                    <p class="text-gray-600 text-sm">Reserve your parking spot in advance</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-history text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Parking History</h4>
                                    <p class="text-gray-600 text-sm">Keep track of all your parking activities</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-bell text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Notifications</h4>
                                    <p class="text-gray-600 text-sm">Get alerts for your bookings and payments</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-tag text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Special Offers</h4>
                                    <p class="text-gray-600 text-sm">Access exclusive discounts and promotions</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Right column - registration form -->
            <div class="w-full md:w-1/2 px-4">
                <div class="bg-white rounded-3xl shadow-xl p-8 md:p-10 animate-fade-in">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800">Create Account</h2>
                        <p class="text-gray-600 mt-2">Register to start managing your parking</p>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="form-error">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                <strong class="text-red-700">Registration failed</strong>
                            </div>
                            <ul class="list-disc list-inside text-red-600 text-sm">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="form-success">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <strong class="text-green-700">Registration Successful!</strong>
                            </div>
                            <p class="text-green-600 text-sm">Your account has been created successfully. <a href="login.php" class="text-indigo-600 font-medium hover:text-indigo-800">Click here to login</a>.</p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" onsubmit="return checkpass();" id="signupForm" novalidate class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="firstname" class="block text-gray-700 font-medium mb-2">First Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="firstname"
                                        name="firstname" 
                                        placeholder="Your first name" 
                                        required 
                                        class="form-input-field pl-10"
                                        minlength="2" 
                                        pattern="^[a-zA-Z\s]+$" 
                                        value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>"
                                    >
                                </div>
                                <small class="text-gray-500 text-xs">Must be at least 2 characters, letters only</small>
                            </div>
                            
                            <div>
                                <label for="lastname" class="block text-gray-700 font-medium mb-2">Last Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="lastname"
                                        name="lastname" 
                                        placeholder="Your last name" 
                                        required 
                                        class="form-input-field pl-10"
                                        minlength="2" 
                                        pattern="^[a-zA-Z\s]+$"
                                        value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>"
                                    >
                                </div>
                                <small class="text-gray-500 text-xs">Must be at least 2 characters, letters only</small>
                            </div>
                        </div>
                        
                        <div>
                            <label for="mobilenumber" class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input 
                                    type="tel" 
                                    id="mobilenumber"
                                    name="mobilenumber" 
                                    placeholder="10-digit mobile number" 
                                    required 
                                    class="form-input-field pl-10"
                                    maxlength="10" 
                                    pattern="[0-9]{10}"
                                    value="<?php echo isset($_POST['mobilenumber']) ? htmlspecialchars($_POST['mobilenumber']) : ''; ?>"
                                >
                            </div>
                            <small class="text-gray-500 text-xs">10-digit number without spaces or dashes</small>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input 
                                    type="email" 
                                    id="email"
                                    name="email" 
                                    placeholder="your.email@example.com" 
                                    required 
                                    class="form-input-field pl-10"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                >
                            </div>
                            <small class="text-gray-500 text-xs">We'll never share your email with anyone else</small>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="password"
                                    name="password" 
                                    placeholder="Create a strong password" 
                                    required 
                                    class="form-input-field pl-10"
                                    minlength="8"
                                    oninput="updatePasswordStrength()"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="togglePassword" class="fas fa-eye text-gray-400 cursor-pointer hover:text-gray-600"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="flex justify-between items-center mb-1">
                                    <small class="text-gray-500 text-xs">Password strength</small>
                                    <span id="strength-text" class="text-sm"></span>
                                </div>
                                <div id="strength-meter" class="strength-meter">
                                    <div></div>
                                </div>
                            </div>
                            <small class="text-gray-500 text-xs block mt-2">
                                Must be at least 8 characters with uppercase, lowercase letters and numbers
                            </small>
                        </div>
                        
                        <div>
                            <label for="repeatpassword" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="repeatpassword"
                                    name="repeatpassword" 
                                    placeholder="Confirm your password" 
                                    required 
                                    class="form-input-field pl-10"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="toggleConfirmPassword" class="fas fa-eye text-gray-400 cursor-pointer hover:text-gray-600"></i>
                                </div>
                            </div>
                            <small class="text-gray-500 text-xs">Must match the password field</small>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm">
                            <a href="login.php" class="text-indigo-600 hover:text-indigo-800">
                                Already have an account? Sign in
                            </a>
                            <a href="forgot-password.php" class="text-indigo-600 hover:text-indigo-800">
                                Forgot Password?
                            </a>
                        </div>
                        
                        <button type="submit" name="submit" class="btn-primary">
                            <i class="fas fa-user-plus mr-2"></i> Create Account
                        </button>
                        
                        <div class="text-center text-sm text-gray-600">
                            By creating an account, you agree to our 
                            <a href="#" class="text-indigo-600 hover:text-indigo-800">Terms of Service</a> and 
                            <a href="#" class="text-indigo-600 hover:text-indigo-800">Privacy Policy</a>
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
        
        // Password visibility toggle for password field
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
        
        // Password visibility toggle for confirm password field
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('repeatpassword');
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
        
        // Initialize password strength meter
        window.onload = function() {
            updatePasswordStrength();
        };
    </script>
</body>
</html>
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
