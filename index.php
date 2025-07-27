<!DOCTYPE html>
<html lang="en">
    <head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VPMS - Smart Parking Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
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
    .gradient-bg {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }
    .card-hover {
      transition: all 0.3s ease;
    }
    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
  </style>
<body class="flex flex-col min-h-screen bg-gray-50">

  <!-- Header -->
  <header class="bg-white shadow sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <div class="flex items-center">
        <img src="assets/img/images.png" alt="VPMS Logo" class="h-10 mr-3">
        <h1 class="text-2xl font-bold text-indigo-600">VPMS</h1>
      </div>
      
      <!-- Desktop Navigation -->
      <nav class="hidden md:flex space-x-6">
        <a href="admin/index.php" class="font-medium text-gray-600 hover:text-indigo-600 transition">Admin</a>
        <a href="users/login.php" class="font-medium text-gray-600 hover:text-indigo-600 transition">Users</a>
        <a href="#about" class="font-medium text-gray-600 hover:text-indigo-600 transition">About</a>
        <a href="#features" class="font-medium text-gray-600 hover:text-indigo-600 transition">Features</a>
        <a href="#testimonials" class="font-medium text-gray-600 hover:text-indigo-600 transition">Testimonials</a>
        <a href="#contact" class="font-medium text-gray-600 hover:text-indigo-600 transition">Contact</a>
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
        <a href="admin/index.php" class="block py-2 text-gray-600 hover:text-indigo-600">Admin</a>
        <a href="users/login.php" class="block py-2 text-gray-600 hover:text-indigo-600">Users</a>
        <a href="#about" class="block py-2 text-gray-600 hover:text-indigo-600">About</a>
        <a href="#features" class="block py-2 text-gray-600 hover:text-indigo-600">Features</a>
        <a href="#testimonials" class="block py-2 text-gray-600 hover:text-indigo-600">Testimonials</a>
        <a href="#contact" class="block py-2 text-gray-600 hover:text-indigo-600">Contact</a>
      </div>
    </div>
  </header>
<!-- Hero -->
<section class="gradient-bg py-20 text-white" id="hero">
  <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
    
    <!-- Text Content -->
    <div class="md:w-1/2 md:pr-12 text-center md:text-left mb-10 md:mb-0 animate-fade-in">
      <span class="inline-block px-3 py-1 bg-white bg-opacity-20 text-white rounded-full text-sm font-semibold mb-4">
        Smart Parking Solution
      </span>
      <h2 class="text-5xl font-bold mb-6 leading-tight">
        Next-Gen <span class="text-yellow-300">Parking Management</span> System
      </h2>
      <p class="text-xl mb-8 opacity-90 max-w-lg">
        Streamline your parking operations with our intelligent, automated system designed for modern businesses and cities.
      </p>
      
      <div class="flex flex-wrap gap-4 justify-center md:justify-start">
        <a href="users/login.php" class="bg-white text-indigo-700 px-8 py-3 rounded-full font-semibold hover:bg-yellow-300 transition duration-300 flex items-center">
          Get Started <i class="fas fa-arrow-right ml-2"></i>
        </a>
        <a href="#features" class="border border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-indigo-700 transition duration-300">
          Learn More
        </a>
      </div>
      
      <!-- Stats -->
      <div class="mt-12 grid grid-cols-3 gap-4 max-w-lg">
        <div class="text-center">
          <div class="text-3xl font-bold">500+</div>
          <div class="text-sm opacity-75">Parking Spaces</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold">24/7</div>
          <div class="text-sm opacity-75">Availability</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold">99%</div>
          <div class="text-sm opacity-75">Satisfaction</div>
        </div>
      </div>
    </div>

    <!-- Image -->
    <div class="md:w-1/2 animate-slide-up">
      <img src="users/includes/images/parking-concept.jpg" alt="VPMS Illustration"
        class="w-full max-w-md mx-auto rounded-2xl shadow-2xl transform rotate-1" />
      
      <!-- Floating elements -->
      <div class="relative">
        <div class="absolute -top-16 -left-4 bg-white p-4 rounded-lg shadow-lg animate-bounce hidden md:block">
          <i class="fas fa-map-marker-alt text-red-500 text-xl"></i>
          <span class="ml-2 font-medium">Find parking instantly</span>
        </div>
        <div class="absolute -bottom-10 -right-4 bg-white p-4 rounded-lg shadow-lg animate-pulse hidden md:block">
          <i class="fas fa-shield-alt text-green-500 text-xl"></i>
          <span class="ml-2 font-medium">Secure & reliable</span>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- About Section -->
<section class="py-24 bg-white" id="about">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16 animate-fade-in">
      <h2 class="text-sm font-bold text-indigo-600 tracking-wider uppercase mb-3">About Our System</h2>
      <h3 class="text-4xl font-bold text-gray-800 mb-4">Smart Parking for Smart Cities</h3>
      <div class="w-24 h-1 bg-indigo-600 mx-auto"></div>
    </div>
    
    <div class="grid md:grid-cols-2 gap-12 items-center">
      <!-- Image on the left with enhanced styling -->
      <div class="flex justify-center relative">
        <div class="absolute -bottom-6 -right-6 w-3/4 h-3/4 bg-indigo-100 rounded-lg -z-10"></div>
        <img src="users/includes/images/using-parking.jpg" 
             alt="About VPMS" 
             class="rounded-lg shadow-2xl w-full object-cover h-[400px] z-10">
        <div class="absolute -top-4 -left-4 bg-indigo-600 text-white p-4 rounded-lg shadow-xl z-20">
          <span class="text-2xl font-bold">5+</span>
          <span class="block text-sm">Years Experience</span>
        </div>
      </div>

      <!-- Text on the right with better formatting -->
      <div class="animate-slide-up">
        <h3 class="text-3xl font-bold text-gray-800 mb-6">Revolutionizing Parking Management</h3>
        
        <p class="text-gray-700 text-lg mb-6 leading-relaxed">
          VPMS is a state-of-the-art parking solution designed to eliminate the frustration of finding parking spaces. Using advanced IoT sensors, real-time data analytics, and user-friendly interfaces, we transform how parking works for both operators and users.
        </p>
        
        <div class="space-y-4 mb-8">
          <div class="flex items-start">
            <div class="bg-indigo-100 p-2 rounded-full mr-4">
              <i class="fas fa-check text-indigo-600"></i>
            </div>
            <div>
              <h4 class="font-semibold mb-1">Smart Space Management</h4>
              <p class="text-gray-600">Optimize parking space usage with intelligent allocation algorithms.</p>
            </div>
          </div>
          
          <div class="flex items-start">
            <div class="bg-indigo-100 p-2 rounded-full mr-4">
              <i class="fas fa-check text-indigo-600"></i>
            </div>
            <div>
              <h4 class="font-semibold mb-1">Seamless User Experience</h4>
              <p class="text-gray-600">Mobile app, predictive analytics, and secure online payments.</p>
            </div>
          </div>
          
          <div class="flex items-start">
            <div class="bg-indigo-100 p-2 rounded-full mr-4">
              <i class="fas fa-check text-indigo-600"></i>
            </div>
            <div>
              <h4 class="font-semibold mb-1">Data-Driven Insights</h4>
              <p class="text-gray-600">Make informed decisions with comprehensive reporting tools.</p>
            </div>
          </div>
        </div>
        
        <a href="#features" class="inline-flex items-center text-indigo-600 font-semibold group">
          Learn more about our features
          <svg class="ml-2 w-4 h-4 group-hover:ml-3 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
</section>



<!-- Features Section -->
<section class="py-24 bg-gray-50" id="features">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16 animate-fade-in">
      <h2 class="text-sm font-bold text-indigo-600 tracking-wider uppercase mb-3">What We Offer</h2>
      <h3 class="text-4xl font-bold text-gray-800 mb-4">Powerful Features for Modern Parking</h3>
      <p class="max-w-2xl mx-auto text-gray-600 text-lg">
        Our comprehensive solution offers everything you need to manage parking effectively
      </p>
      <div class="w-24 h-1 bg-indigo-600 mx-auto mt-6"></div>
    </div>
    
    <div class="grid md:grid-cols-3 gap-8">
      <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
        <div class="relative">
          <img src="users/includes/images/time.jpg" alt="Real-Time Availability" 
               class="w-full h-52 object-cover">
          <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-transparent to-black opacity-50"></div>
          <div class="absolute top-4 right-4 bg-indigo-600 text-white rounded-full p-3">
            <i class="fas fa-clock text-xl"></i>
          </div>
        </div>
        <div class="p-6">
          <h4 class="text-xl font-semibold mb-3 text-gray-800">Real-Time Availability</h4>
          <p class="text-gray-600 mb-4">Track available spaces in real-time with our advanced sensor network and dynamic allocation system.</p>
          <ul class="space-y-2 mb-4">
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Live occupancy data
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Predictive availability
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Space reservation
            </li>
          </ul>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
        <div class="relative">
          <img src="users/includes/images/mobile-access.jpg" alt="Mobile App" 
               class="w-full h-52 object-cover">
          <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-transparent to-black opacity-50"></div>
          <div class="absolute top-4 right-4 bg-indigo-600 text-white rounded-full p-3">
            <i class="fas fa-mobile-alt text-xl"></i>
          </div>
        </div>
        <div class="p-6">
          <h4 class="text-xl font-semibold mb-3 text-gray-800">Mobile App Access</h4>
          <p class="text-gray-600 mb-4">Manage your parking experience from anywhere with our intuitive mobile application.</p>
          <ul class="space-y-2 mb-4">
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> One-tap reservations
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Digital parking pass
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Navigation guidance
            </li>
          </ul>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
        <div class="relative">
          <img src="users/includes/images/online-payment.jpg" alt="Payments" 
               class="w-full h-52 object-cover">
          <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-transparent to-black opacity-50"></div>
          <div class="absolute top-4 right-4 bg-indigo-600 text-white rounded-full p-3">
            <i class="fas fa-credit-card text-xl"></i>
          </div>
        </div>
        <div class="p-6">
          <h4 class="text-xl font-semibold mb-3 text-gray-800">Seamless Payments</h4>
          <p class="text-gray-600 mb-4">Enjoy convenient, secure payment options with automatic billing and receipt generation.</p>
          <ul class="space-y-2 mb-4">
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Multiple payment methods
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Contactless transactions
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Digital receipts
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Additional Features in Icons Row -->
    <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-6">
      <div class="text-center p-4">
        <div class="bg-indigo-100 rounded-full p-4 w-16 h-16 flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-shield-alt text-indigo-600 text-xl"></i>
        </div>
        <h5 class="font-semibold mb-2">Enhanced Security</h5>
        <p class="text-sm text-gray-600">24/7 monitoring and secure access control</p>
      </div>
      
      <div class="text-center p-4">
        <div class="bg-indigo-100 rounded-full p-4 w-16 h-16 flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
        </div>
        <h5 class="font-semibold mb-2">Analytics Dashboard</h5>
        <p class="text-sm text-gray-600">Comprehensive reports and insights</p>
      </div>
      
      <div class="text-center p-4">
        <div class="bg-indigo-100 rounded-full p-4 w-16 h-16 flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-users text-indigo-600 text-xl"></i>
        </div>
        <h5 class="font-semibold mb-2">User Management</h5>
        <p class="text-sm text-gray-600">Role-based access and permissions</p>
      </div>
      
      <div class="text-center p-4">
        <div class="bg-indigo-100 rounded-full p-4 w-16 h-16 flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-headset text-indigo-600 text-xl"></i>
        </div>
        <h5 class="font-semibold mb-2">24/7 Support</h5>
        <p class="text-sm text-gray-600">Always available customer assistance</p>
      </div>
    </div>
  </div>
</section>



  <!-- Testimonials Section -->
  <section class="py-24 bg-white" id="testimonials">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16 animate-fade-in">
        <h2 class="text-sm font-bold text-indigo-600 tracking-wider uppercase mb-3">Testimonials</h2>
        <h3 class="text-4xl font-bold text-gray-800 mb-4">What Our Users Say</h3>
        <p class="max-w-2xl mx-auto text-gray-600 text-lg">
          Don't just take our word for it - hear from our satisfied customers
        </p>
        <div class="w-24 h-1 bg-indigo-600 mx-auto mt-6"></div>
      </div>
      
      <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <div class="bg-white p-8 rounded-xl shadow-lg relative card-hover">
          <div class="absolute -top-5 left-8 bg-indigo-600 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fas fa-quote-left text-white"></i>
          </div>
          <div class="mb-6">
            <div class="flex mb-3">
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
            </div>
            <p class="text-gray-700 italic mb-4 text-lg leading-relaxed">
              "VPMS has saved me so much time and reduced my stress levels. I no longer drive in circles looking for parking! The reservation feature is a game-changer."
            </p>
          </div>
          <div class="flex items-center">
            <img src="assets/img/avataaars.svg" alt="Jamie R." class="w-12 h-12 rounded-full object-cover mr-4 border-2 border-indigo-600">
            <div>
              <p class="text-indigo-600 font-semibold text-lg">Jamie R.</p>
              <p class="text-gray-500 text-sm">Regular Commuter</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white p-8 rounded-xl shadow-lg relative card-hover md:transform md:translate-y-4">
          <div class="absolute -top-5 left-8 bg-indigo-600 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fas fa-quote-left text-white"></i>
          </div>
          <div class="mb-6">
            <div class="flex mb-3">
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
            </div>
            <p class="text-gray-700 italic mb-4 text-lg leading-relaxed">
              "The app is super intuitive and payment is seamless. I can see which spots are available before I even leave my home. Highly recommend VPMS!"
            </p>
          </div>
          <div class="flex items-center">
            <img src="assets/img/avataaars.svg" alt="Priya S." class="w-12 h-12 rounded-full object-cover mr-4 border-2 border-indigo-600">
            <div>
              <p class="text-indigo-600 font-semibold text-lg">Priya S.</p>
              <p class="text-gray-500 text-sm">Business Owner</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white p-8 rounded-xl shadow-lg relative card-hover">
          <div class="absolute -top-5 left-8 bg-indigo-600 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fas fa-quote-left text-white"></i>
          </div>
          <div class="mb-6">
            <div class="flex mb-3">
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
              <i class="fas fa-star text-yellow-400"></i>
            </div>
            <p class="text-gray-700 italic mb-4 text-lg leading-relaxed">
              "Excellent customer service and the technology just works. The digital receipts and history tracking have made expense reports so much easier to manage!"
            </p>
          </div>
          <div class="flex items-center">
            <img src="assets/img/avataaars.svg" alt="Carlos M." class="w-12 h-12 rounded-full object-cover mr-4 border-2 border-indigo-600">
            <div>
              <p class="text-indigo-600 font-semibold text-lg">Carlos M.</p>
              <p class="text-gray-500 text-sm">Corporate Executive</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Testimonial Stats -->
      <div class="mt-20 bg-indigo-600 rounded-2xl p-8 shadow-xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="text-center text-white">
            <div class="text-4xl font-bold mb-2">98%</div>
            <div class="text-indigo-100">Customer Satisfaction</div>
          </div>
          <div class="text-center text-white">
            <div class="text-4xl font-bold mb-2">30min</div>
            <div class="text-indigo-100">Average Time Saved</div>
          </div>
          <div class="text-center text-white">
            <div class="text-4xl font-bold mb-2">15k+</div>
            <div class="text-indigo-100">Active Users</div>
          </div>
          <div class="text-center text-white">
            <div class="text-4xl font-bold mb-2">4.9/5</div>
            <div class="text-indigo-100">App Store Rating</div>
          </div>
        </div>
      </div>
      
    </div>
  </section>

  <!-- Contact Section -->
  <section class="py-24 bg-gray-50" id="contact">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16 animate-fade-in">
        <h2 class="text-sm font-bold text-indigo-600 tracking-wider uppercase mb-3">Get in Touch</h2>
        <h3 class="text-4xl font-bold text-gray-800 mb-4">Contact Us</h3>
        <p class="max-w-2xl mx-auto text-gray-600 text-lg">
          Have questions or need assistance? Our team is here to help.
        </p>
        <div class="w-24 h-1 bg-indigo-600 mx-auto mt-6"></div>
      </div>
      
      <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-indigo-600 p-6">
            <h4 class="text-xl font-bold text-white mb-2">Contact Information</h4>
            <p class="text-indigo-100">Reach out to us using any of these methods</p>
          </div>
          <div class="p-6 space-y-6">
            <div class="flex items-start">
              <div class="bg-indigo-100 p-3 rounded-full mr-4">
                <i class="fas fa-map-marker-alt text-indigo-600"></i>
              </div>
              <div>
                <h5 class="font-semibold mb-1">Our Location</h5>
                <p class="text-gray-600">123 Parking Street, Cityville, 10001</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-indigo-100 p-3 rounded-full mr-4">
                <i class="fas fa-phone text-indigo-600"></i>
              </div>
              <div>
                <h5 class="font-semibold mb-1">Phone Number</h5>
                <p class="text-gray-600">+254 123 456 789</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-indigo-100 p-3 rounded-full mr-4">
                <i class="fas fa-envelope text-indigo-600"></i>
              </div>
              <div>
                <h5 class="font-semibold mb-1">Email Address</h5>
                <p class="text-gray-600">support@vpms.com</p>
              </div>
            </div>
            <div class="flex space-x-4 mt-8">
              <a href="#" class="bg-indigo-100 p-3 rounded-full text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="#" class="bg-indigo-100 p-3 rounded-full text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                <i class="fab fa-twitter"></i>
              </a>
              <a href="#" class="bg-indigo-100 p-3 rounded-full text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                <i class="fab fa-linkedin-in"></i>
              </a>
              <a href="#" class="bg-indigo-100 p-3 rounded-full text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                <i class="fab fa-instagram"></i>
              </a>
            </div>
          </div>
        </div>
        
        <!-- Contact Form -->
        <div class="md:col-span-2">
          <form class="bg-white p-8 rounded-xl shadow-lg">
            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div>
                <label for="name" class="block text-gray-700 font-medium mb-2">Your Name</label>
                <input type="text" id="name" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label for="email" class="block text-gray-700 font-medium mb-2">Your Email</label>
                <input type="email" id="email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
              </div>
            </div>
            <div class="mb-6">
              <label for="subject" class="block text-gray-700 font-medium mb-2">Subject</label>
              <input type="text" id="subject" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-6">
              <label for="message" class="block text-gray-700 font-medium mb-2">Your Message</label>
              <textarea id="message" rows="5" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors duration-300 inline-flex items-center">
              Send Message
              <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
              </svg>
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="gradient-bg text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid md:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center mb-4">
            <img src="assets/img/images.png" alt="VPMS Logo" class="h-10 mr-3 bg-white p-1 rounded">
            <h4 class="text-2xl font-bold">VPMS</h4>
          </div>
          <p class="text-indigo-100 mb-4">
            Your trusted partner for modern parking solutions. Simplifying parking management since 2020.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-white hover:text-yellow-300 transition"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="text-white hover:text-yellow-300 transition"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-white hover:text-yellow-300 transition"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="text-white hover:text-yellow-300 transition"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
        
        <div>
          <h5 class="text-lg font-semibold mb-4">Quick Links</h5>
          <ul class="space-y-2 text-indigo-100">
            <li><a href="#" class="hover:text-white">Home</a></li>
            <li><a href="#about" class="hover:text-white">About Us</a></li>
            <li><a href="#features" class="hover:text-white">Features</a></li>
            <li><a href="#testimonials" class="hover:text-white">Testimonials</a></li>
            <li><a href="#contact" class="hover:text-white">Contact</a></li>
          </ul>
        </div>
        
        <div>
          <h5 class="text-lg font-semibold mb-4">Resources</h5>
          <ul class="space-y-2 text-indigo-100">
            <li><a href="#" class="hover:text-white">Help Center</a></li>
            <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
            <li><a href="#" class="hover:text-white">Terms of Service</a></li>
            <li><a href="#" class="hover:text-white">Blog</a></li>
          </ul>
        </div>
        
        <div>
          <h5 class="text-lg font-semibold mb-4">Newsletter</h5>
          <p class="text-indigo-100 mb-4">Subscribe to our newsletter for updates</p>
          <form>
            <div class="flex">
              <input type="email" placeholder="Your email" class="p-2 rounded-l-lg w-full focus:outline-none text-gray-800">
              <button type="submit" class="bg-yellow-400 px-4 rounded-r-lg text-indigo-800 font-semibold hover:bg-yellow-300 transition">
                <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
      
      <div class="border-t border-indigo-400 pt-6 text-center">
        <p>&copy; 2025 VPMS. All rights reserved.</p>
      </div>
    </div>
  </footer>
  </footer>

  <!-- Scripts -->
  <script>
    // Smooth Scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetElement = document.querySelector(this.getAttribute('href'));
        if (targetElement) {
          targetElement.scrollIntoView({
            behavior: 'smooth'
          });
        }
        
        // Close mobile menu if it's open
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu.classList.contains('block')) {
          mobileMenu.classList.remove('block');
          mobileMenu.classList.add('hidden');
        }
      });
    });
    
    // Mobile Menu Toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      if (mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.remove('hidden');
        mobileMenu.classList.add('block');
      } else {
        mobileMenu.classList.remove('block');
        mobileMenu.classList.add('hidden');
      }
    });
    
    // Animate elements when they enter the viewport
    const animateOnScroll = () => {
      const elementsToAnimate = document.querySelectorAll('.card-hover, .animate-fade-in, .animate-slide-up');
      
      elementsToAnimate.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementHeight = element.getBoundingClientRect().height;
        const windowHeight = window.innerHeight;
        
        if (elementTop < windowHeight - elementHeight / 2) {
          element.style.opacity = '1';
          element.style.transform = 'translateY(0)';
        }
      });
    };
    
    // Set initial state for animations
    document.addEventListener('DOMContentLoaded', () => {
      const animateElements = document.querySelectorAll('.card-hover:not(.animate-fade-in):not(.animate-slide-up)');
      animateElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      });
      
      // Run once on load
      animateOnScroll();
      
      // Add scroll event listener
      window.addEventListener('scroll', animateOnScroll);
    });
  </script>

</body>
</html>