<?php
// ---- SESSION START MUST BE ABSOLUTELY FIRST ----
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ---- END SESSION START ----

// ---- REDIRECT IF *NOT* LOGGED IN ----
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: Login.php"); // Redirect to Login page
    exit; // Stop executing Home.php code
}
// ---- END REDIRECT ----


// --- User IS logged in if script reaches here ---

// Include config file AFTER login check if needed for DB access or constants
require_once 'config.php';

// Get user details from session for display
$user_display_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : (isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : 'User');
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : 'Not Available';
$user_profile_pic = isset($_SESSION["profile_pic"]) ? htmlspecialchars($_SESSION["profile_pic"]) : "https://avatar.iran.liara.run/public/boy"; // Default avatar

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPro | Professional Online Learning Platform</title>
    <meta name="description" content="Enhance your skills with expert-led courses in web development, data science, digital marketing and more. Start learning today with EduPro.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <!-- Added Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc',
                            400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1',
                            800: '#075985', 900: '#0c4a6e', 950: '#082f49',
                        },
                        secondary: {
                            400: '#a78bfa', 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9'
                        }
                     },
                     animation: {
                         'float': 'float 3s ease-in-out infinite',
                         'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite'
                     },
                     keyframes: {
                         float: {
                             '0%, 100%': { transform: 'translateY(0)' },
                             '50%': { transform: 'translateY(-10px)' }
                         }
                     }
                }
            }
        }
    </script>
    <style>
        .course-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .nav-link { position: relative; padding-bottom: 4px;}
        .nav-link::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 50%; transform: translateX(-50%); background-color: #0ea5e9; transition: width 0.3s ease; }
        .nav-link:hover::after { width: 70%; }
        .hero-gradient { background: linear-gradient(135deg, #0ea5e9 0%, #7c3aed 100%); }
        .testimonial-card { transition: all 0.3s ease; }
        .testimonial-card:hover { transform: scale(1.02); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .accordion-toggle:checked ~ .accordion-content { max-height: 1000px; }
        .accordion-toggle:checked + label .accordion-icon { transform: rotate(180deg); }
        .accordion-icon { transition: transform 0.3s ease; }
        /* Additional styles */
        .stat-item div:first-child { transition: color 0.3s ease; }
        .stat-item:hover div:first-child { color: #7dd3fc; /* primary-300 */ }
        .feature-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .course-badge { position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-bestseller { background-color: #f59e0b; color: white; } /* amber-500 */
        .badge-new { background-color: #10b981; color: white; } /* emerald-500 */
        .badge-hot { background-color: #ef4444; color: white; } /* red-500 */
    </style>
</head>
<body class="font-sans bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center">
                    <a href="Home.php" class="flex items-center flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 bg-primary-600 text-white rounded-lg shadow">
                            <i class="fas fa-book-reader text-xl"></i> <!-- Changed icon -->
                        </div>
                        <span class="ml-3 text-2xl font-bold text-gray-800 tracking-tight">EduPro</span>
                    </a>
                    <div class="hidden md:block ml-10">
                        <div class="flex space-x-6 items-baseline">
                            <a href="Home.php" class="nav-link text-gray-700 hover:text-primary-600 font-medium text-sm">Home</a>
                            <a href="#courses" class="nav-link text-gray-700 hover:text-primary-600 font-medium text-sm">Courses</a>
                            <a href="Dashboard.php" class="nav-link text-gray-700 hover:text-primary-600 font-medium text-sm">Dashboard</a>
                            <a href="#features" class="nav-link text-gray-700 hover:text-primary-600 font-medium text-sm">Features</a>
                            <a href="#testimonials" class="nav-link text-gray-700 hover:text-primary-600 font-medium text-sm">Testimonials</a>
                            <a href="#contact" class="nav-link text-gray-700 hover:text-primary-600 font-medium text-sm">Contact</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                     <span class="text-gray-600 text-sm hidden lg:block" title="<?php echo $user_email; ?>">Welcome, <?php echo $user_display_name; ?>!</span>
                     <a href="Dashboard.php" class="text-gray-500 hover:text-primary-600 p-2 rounded-full hover:bg-gray-100 transition duration-150" title="Go to Dashboard">
                         <img src="<?php echo $user_profile_pic; ?>" alt="User Avatar" class="w-8 h-8 rounded-full object-cover border-2 border-gray-200 hover:border-primary-500">
                     </a>
                    <a href="logout.php" class="px-4 py-2 text-sm bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition duration-150 shadow hover:shadow-md font-medium">Logout</a>
                </div>
                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button class="mobile-menu-button p-2 rounded-md text-gray-700 hover:text-primary-600 hover:bg-gray-100 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i> <!-- Changed icon -->
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="mobile-menu hidden md:hidden bg-white border-t border-gray-200 shadow-lg">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="Home.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100">Home</a>
                <a href="#courses" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100">Courses</a>
                <a href="Dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100">Dashboard</a>
                <a href="#features" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100">Features</a>
                <a href="#testimonials" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100">Testimonials</a>
                <a href="#contact" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100">Contact</a>
                <!-- Logged In User Info (Mobile) -->
                <div class="pt-4 mt-2 border-t border-gray-200">
                     <div class="flex items-center px-3 mb-3">
                        <div class="flex-shrink-0">
                             <img class="h-10 w-10 rounded-full object-cover border border-gray-300" src="<?php echo $user_profile_pic; ?>" alt="User Avatar">
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800"><?php echo $user_display_name; ?></div>
                            <div class="text-sm font-medium text-gray-500"><?php echo $user_email; ?></div>
                        </div>
                     </div>
                     <a href="logout.php" class="block w-full text-center mt-2 px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-gradient text-white pt-32 pb-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="animate__animated animate__fadeInLeft animate__delay-0.5s z-10">
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight tracking-tight mb-4">Level Up Your Skills, <br>Advance Your Career</h1>
                    <p class="mt-4 text-lg text-blue-100 max-w-xl mb-8">Join thousands of professionals learning in-demand skills with expert-led online courses in tech, business, design, and more.</p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                        <a href="Dashboard.php" class="px-8 py-3 bg-white text-primary-700 font-semibold rounded-lg hover:bg-gray-100 transition duration-200 text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Go to Dashboard</a>
                        <a href="#courses" class="px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:bg-opacity-10 transition duration-200 text-center">Browse Courses</a>
                    </div>
                    <div class="mt-10 flex items-center">
                        <div class="flex -space-x-2 overflow-hidden">
                            <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover" src="https://randomuser.me/api/portraits/women/12.jpg" alt="Student">
                            <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover" src="https://randomuser.me/api/portraits/men/32.jpg" alt="Student">
                            <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover" src="https://randomuser.me/api/portraits/women/44.jpg" alt="Student">
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-blue-100">Trusted by over</p>
                            <p class="font-semibold text-white">500,000+ professionals</p>
                        </div>
                    </div>
                </div>
                <div class="animate__animated animate__fadeInRight animate__delay-0.5s hidden md:block relative">
                    <img src="https://images.unsplash.com/photo-1546410531-bb4caa6b424d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1471&q=80" alt="Online Learning Illustration" class="rounded-xl shadow-2xl w-full h-auto relative z-10 animate-float">
                    <!-- Decorative shapes -->
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full filter blur-xl opacity-50"></div>
                    <div class="absolute -bottom-16 left-0 w-48 h-48 bg-secondary-500/10 rounded-full filter blur-2xl opacity-60"></div>
                </div>
            </div>
            <!-- More decorative shapes -->
             <div class="absolute top-0 left-0 w-64 h-64 bg-secondary-600/5 rounded-full filter blur-3xl opacity-30 transform -translate-x-1/2 -translate-y-1/2"></div>
        </div>
    </header>

    <!-- Trusted By Section -->
    <div class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm font-semibold text-gray-500 uppercase tracking-wider mb-8">Trusted by leading companies worldwide</p>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 items-center justify-items-center">
                <img src="https://cdn.tailgrids.com/1.0/assets/images/brands/graygrids.svg" alt="GrayGrids" class="h-8 opacity-60 hover:opacity-100 transition duration-200 col-span-1">
                <img src="https://cdn.tailgrids.com/1.0/assets/images/brands/lineicons.svg" alt="LineIcons" class="h-8 opacity-60 hover:opacity-100 transition duration-200 col-span-1">
                <img src="https://cdn.tailgrids.com/1.0/assets/images/brands/uideck.svg" alt="UIdeck" class="h-8 opacity-60 hover:opacity-100 transition duration-200 col-span-1">
                <img src="https://cdn.tailgrids.com/1.0/assets/images/brands/ayroui.svg" alt="AyroUI" class="h-8 opacity-60 hover:opacity-100 transition duration-200 col-span-1">
                <img src="https://logo.clearbit.com/google.com" alt="Google" class="h-7 opacity-60 hover:opacity-100 transition duration-200 col-span-1">
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Why Choose EduPro?</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">Unlock your potential with our platform designed for effective learning and career growth.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Feature 1 -->
                <div class="feature-card text-center p-8 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-6 bg-primary-100 rounded-full text-primary-600 text-3xl shadow-sm">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Expert Instructors</h3>
                    <p class="text-gray-600 text-sm">Learn from industry professionals with real-world experience and a passion for teaching.</p>
                </div>
                 <!-- Feature 2 -->
                <div class="feature-card text-center p-8 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-6 bg-primary-100 rounded-full text-primary-600 text-3xl shadow-sm">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Interactive Learning</h3>
                    <p class="text-gray-600 text-sm">Engage with hands-on projects, quizzes, and coding exercises that reinforce concepts.</p>
                </div>
                 <!-- Feature 3 -->
                <div class="feature-card text-center p-8 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-6 bg-primary-100 rounded-full text-primary-600 text-3xl shadow-sm">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Verified Certification</h3>
                    <p class="text-gray-600 text-sm">Earn recognized certificates to showcase your newly acquired skills to employers.</p>
                </div>
                 <!-- Feature 4 -->
                 <div class="feature-card text-center p-8 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-6 bg-primary-100 rounded-full text-primary-600 text-3xl shadow-sm">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Learn Anywhere</h3>
                    <p class="text-gray-600 text-sm">Access courses on the go with our mobile-optimized platform and dedicated app.</p>
                </div>
                 <!-- Feature 5 -->
                <div class="feature-card text-center p-8 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-6 bg-primary-100 rounded-full text-primary-600 text-3xl shadow-sm">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Community Support</h3>
                    <p class="text-gray-600 text-sm">Connect with peers, mentors, and instructors in our active learning community.</p>
                </div>
                 <!-- Feature 6 -->
                <div class="feature-card text-center p-8 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-6 bg-primary-100 rounded-full text-primary-600 text-3xl shadow-sm">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Career Services</h3>
                    <p class="text-gray-600 text-sm">Access career coaching, resume reviews, and job placement assistance after completion.</p>
                </div>
            </div>
        </div>
    </section>

     <!-- Courses Section -->
    <section id="courses" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Explore Popular Courses</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">Start learning from our curated selection of high-demand courses.</p>
            </div>

            <!-- Course Filters (Optional) -->
            <div class="flex justify-center mb-12">
                <div class="inline-flex rounded-lg shadow-sm bg-white p-1 space-x-1 border border-gray-200">
                    <button type="button" class="px-5 py-2 text-sm font-medium rounded-md bg-primary-600 text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">All</button>
                    <button type="button" class="px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">Tech</button>
                    <button type="button" class="px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">Business</button>
                    <button type="button" class="px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">Design</button>
                    <button type="button" class="px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">Marketing</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Course Card 1: Web Dev -->
                <div class="course-card bg-white rounded-xl shadow-md overflow-hidden transition duration-300 border border-gray-200">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Web Development" class="w-full h-48 object-cover">
                        <span class="course-badge badge-bestseller">Bestseller</span>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between text-xs mb-2">
                            <span class="inline-block px-2 py-0.5 font-semibold bg-primary-100 text-primary-700 rounded">Beginner</span>
                            <span class="text-gray-500 flex items-center"><i class="far fa-clock mr-1"></i> 45 hours</span>
                        </div>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 leading-tight truncate">Complete Web Development Bootcamp</h3>
                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">Master HTML, CSS, JavaScript, React, Node.js and build real projects from scratch.</p>
                        <div class="mt-3 flex items-center">
                            <div class="flex items-center text-yellow-400">
                                <i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star-half-alt text-xs"></i>
                            </div>
                            <span class="ml-2 text-xs text-gray-600">4.7 (12,345)</span>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-xl font-bold text-gray-900">₹499</span>
                            <a href="web_dev_course.php" class="px-4 py-2 text-xs bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition duration-150 font-semibold shadow-sm">View Details</a>
                        </div>
                    </div>
                </div>

                <!-- Course Card 2: Data Science -->
                <div class="course-card bg-white rounded-xl shadow-md overflow-hidden transition duration-300 border border-gray-200">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1415&q=80" alt="Data Science" class="w-full h-48 object-cover">
                        <span class="course-badge badge-new">New</span>
                    </div>
                    <div class="p-6">
                         <div class="flex items-center justify-between text-xs mb-2">
                            <span class="inline-block px-2 py-0.5 font-semibold bg-orange-100 text-orange-700 rounded">Intermediate</span>
                            <span class="text-gray-500 flex items-center"><i class="far fa-clock mr-1"></i> 60 hours</span>
                        </div>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 leading-tight truncate">Data Science & Machine Learning A-Z™</h3>
                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">Learn Python, Pandas, NumPy, Scikit-learn, Matplotlib, TensorFlow, and more.</p>
                        <div class="mt-3 flex items-center">
                            <div class="flex items-center text-yellow-400">
                                <i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i>
                            </div>
                            <span class="ml-2 text-xs text-gray-600">4.9 (8,765)</span>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-xl font-bold text-gray-900">₹699</span>
                            <a href="data_science_course.php" class="px-4 py-2 text-xs bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition duration-150 font-semibold shadow-sm">View Details</a>
                        </div>
                    </div>
                </div>

                 <!-- Course Card 3: Digital Marketing -->
                 <div class="course-card bg-white rounded-xl shadow-md overflow-hidden transition duration-300 border border-gray-200">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Digital Marketing" class="w-full h-48 object-cover">
                         <!-- No badge for this one -->
                    </div>
                    <div class="p-6">
                         <div class="flex items-center justify-between text-xs mb-2">
                            <span class="inline-block px-2 py-0.5 font-semibold bg-purple-100 text-purple-700 rounded">All Levels</span>
                            <span class="text-gray-500 flex items-center"><i class="far fa-clock mr-1"></i> 30 hours</span>
                        </div>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 leading-tight truncate">The Complete Digital Marketing Course</h3>
                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">Master SEO, Social Media, PPC, Email Marketing, Content Strategy & Analytics.</p>
                         <div class="mt-3 flex items-center">
                            <div class="flex items-center text-yellow-400">
                                <i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="fas fa-star text-xs"></i><i class="far fa-star text-xs"></i>
                            </div>
                            <span class="ml-2 text-xs text-gray-600">4.5 (6,543)</span>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-xl font-bold text-gray-900">₹599</span>
                            <a href="digital marketing.php" class="px-4 py-2 text-xs bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition duration-150 font-semibold shadow-sm">View Details</a>
                        </div>
                    </div>
                </div>
                 <!-- Add more course cards as needed -->

            </div> <!-- End grid -->

            <div class="mt-16 text-center">
                <a href="courses_list.php" class="px-8 py-3 border-2 border-primary-600 text-primary-600 font-semibold rounded-lg hover:bg-primary-50 hover:text-primary-700 transition duration-200 transform hover:scale-105">
                    View All Courses
                </a>
            </div>
        </div>
    </section>


    <!-- Stats Section -->
    <section class="py-20 bg-primary-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="stat-item p-4">
                    <div class="text-4xl lg:text-5xl font-extrabold animate-pulse-slow mb-2">500K+</div>
                    <div class="text-sm lg:text-base font-medium text-blue-200 uppercase tracking-wider">Students Enrolled</div>
                </div>
                <div class="stat-item p-4">
                    <div class="text-4xl lg:text-5xl font-extrabold animate-pulse-slow mb-2">150+</div>
                    <div class="text-sm lg:text-base font-medium text-blue-200 uppercase tracking-wider">Expert Instructors</div>
                </div>
                <div class="stat-item p-4">
                    <div class="text-4xl lg:text-5xl font-extrabold animate-pulse-slow mb-2">1.2M+</div>
                    <div class="text-sm lg:text-base font-medium text-blue-200 uppercase tracking-wider">Hours of Content</div>
                </div>
                <div class="stat-item p-4">
                    <div class="text-4xl lg:text-5xl font-extrabold animate-pulse-slow mb-2">95%</div>
                    <div class="text-sm lg:text-base font-medium text-blue-200 uppercase tracking-wider">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">What Our Students Say</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">Real stories from learners who transformed their careers with EduPro.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="testimonial-card bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Sarah Johnson" class="w-14 h-14 rounded-full mr-4 border-2 border-primary-200 object-cover">
                        <div>
                            <h4 class="font-semibold text-gray-900">Sarah Johnson</h4>
                            <p class="text-sm text-primary-600">Web Developer @ Google</p>
                        </div>
                    </div>
                    <div class="mb-3 flex text-yellow-400">
                         <i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed">"The web development course was a game-changer. The hands-on projects and instructor support were incredible. Landed my dream job six months after completion!"</p>
                </div>
                 <!-- Testimonial 2 -->
                 <div class="testimonial-card bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/54.jpg" alt="Michael Chen" class="w-14 h-14 rounded-full mr-4 border-2 border-primary-200 object-cover">
                        <div>
                            <h4 class="font-semibold text-gray-900">Michael Chen</h4>
                            <p class="text-sm text-primary-600">Data Scientist @ Amazon</p>
                        </div>
                    </div>
                     <div class="mb-3 flex text-yellow-400">
                         <i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed">"EduPro's data science path is incredibly comprehensive. The practical experience gained from the projects was exactly what I needed for my career transition."</p>
                </div>
                 <!-- Testimonial 3 -->
                 <div class="testimonial-card bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Priya Patel" class="w-14 h-14 rounded-full mr-4 border-2 border-primary-200 object-cover">
                        <div>
                            <h4 class="font-semibold text-gray-900">Priya Patel</h4>
                            <p class="text-sm text-primary-600">Digital Marketing Manager</p>
                        </div>
                    </div>
                    <div class="mb-3 flex text-yellow-400">
                         <i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star-half-alt text-sm"></i>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed">"The digital marketing course provided real-world strategies I implemented immediately. Our company's online leads doubled within just three months!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section Placeholder (If you want to add it back) -->
    <!-- <section id="pricing" class="py-16 bg-gray-50"> ... </section> -->

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Frequently Asked Questions</h2>
                <p class="mt-4 text-lg text-gray-600">Find answers to common questions about our platform and courses.</p>
            </div>

            <div class="space-y-5">
                <!-- FAQ Item 1 -->
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <input type="checkbox" id="faq1" class="accordion-toggle hidden">
                    <label for="faq1" class="flex justify-between items-center cursor-pointer text-left">
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">How do I access my courses after enrolling?</h3>
                        <i class="fas fa-chevron-down accordion-icon transform transition-transform text-primary-600"></i>
                    </label>
                    <div class="accordion-content mt-3 pt-3 border-t border-gray-200">
                        <p class="text-sm text-gray-600 leading-relaxed">Once enrolled, log in to your EduPro account. Your courses appear in your dashboard. Access materials anytime, on any internet-connected device. Download lessons for offline viewing via our mobile app (premium feature).</p>
                    </div>
                </div>
                <!-- FAQ Item 2 -->
                 <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <input type="checkbox" id="faq2" class="accordion-toggle hidden">
                    <label for="faq2" class="flex justify-between items-center cursor-pointer text-left">
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Are the certificates recognized by employers?</h3>
                        <i class="fas fa-chevron-down accordion-icon transform transition-transform text-primary-600"></i>
                    </label>
                    <div class="accordion-content mt-3 pt-3 border-t border-gray-200">
                        <p class="text-sm text-gray-600 leading-relaxed">Yes! Our certificates are valued by many employers globally as proof of skill acquisition and commitment to professional development. Many students leverage them for career advancement or transitions.</p>
                    </div>
                </div>
                 <!-- FAQ Item 3 -->
                 <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <input type="checkbox" id="faq3" class="accordion-toggle hidden">
                    <label for="faq3" class="flex justify-between items-center cursor-pointer text-left">
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">What if I'm not satisfied with a course?</h3>
                        <i class="fas fa-chevron-down accordion-icon transform transition-transform text-primary-600"></i>
                    </label>
                    <div class="accordion-content mt-3 pt-3 border-t border-gray-200">
                        <p class="text-sm text-gray-600 leading-relaxed">We offer a 30-day money-back guarantee on all course purchases. If you're unsatisfied, contact our support team within 30 days of enrollment for a full refund, no questions asked.</p>
                    </div>
                </div>
                 <!-- FAQ Item 4 -->
                 <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <input type="checkbox" id="faq4" class="accordion-toggle hidden">
                    <label for="faq4" class="flex justify-between items-center cursor-pointer text-left">
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Do you offer plans for teams or businesses?</h3>
                        <i class="fas fa-chevron-down accordion-icon transform transition-transform text-primary-600"></i>
                    </label>
                    <div class="accordion-content mt-3 pt-3 border-t border-gray-200">
                        <p class="text-sm text-gray-600 leading-relaxed">Absolutely! We provide discounted EduPro for Business plans for organizations looking to upskill their workforce. These include features like advanced analytics, dedicated support, and custom learning paths. <a href="#contact" class="text-primary-600 hover:underline font-medium">Contact our sales team</a> for details.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary-600 to-secondary-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Ready to Start Learning?</h2>
            <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto">Join thousands of professionals who have transformed their careers with EduPro's cutting-edge courses.</p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                 <!-- Link to Dashboard as user is logged in -->
                <a href="Dashboard.php" class="inline-block px-8 py-3 bg-white text-primary-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition duration-200 transform hover:-translate-y-0.5">
                    Go to My Dashboard
                </a>
                <a href="#courses" class="inline-block px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:bg-opacity-10 transition duration-200">
                    Explore Courses
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Get In Touch</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">Have questions or need support? Reach out to our team.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
                <!-- Contact Form -->
                <div class="bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Send us a message</h3>
                    <form action="#" method="POST" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" id="name" name="name" required class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="email-contact" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email-contact" name="email" required class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" id="subject" name="subject" required class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea id="message" name="message" rows="4" required class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"></textarea>
                        </div>
                        <div>
                            <button type="submit" class="w-full px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Send Message</button>
                        </div>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="space-y-8 pt-2">
                     <h3 class="text-xl font-semibold text-gray-900 mb-6">Contact Information</h3>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-primary-100 text-primary-600 rounded-lg"><i class="fas fa-map-marker-alt text-xl"></i></div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-gray-900">Address</h4>
                            <p class="mt-1 text-sm text-gray-600">123 Education Ave, Learning City, LC 54321</p>
                        </div>
                    </div>
                     <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-primary-100 text-primary-600 rounded-lg"><i class="fas fa-envelope text-xl"></i></div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-gray-900">Email</h4>
                            <p class="mt-1 text-sm text-gray-600">support@edupro.com</p>
                        </div>
                    </div>
                     <div class="flex items-start">
                         <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-primary-100 text-primary-600 rounded-lg"><i class="fas fa-phone-alt text-xl"></i></div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-gray-900">Phone</h4>
                            <p class="mt-1 text-sm text-gray-600">+1 (555) 123-4567</p>
                        </div>
                    </div>
                     <div class="flex items-start">
                         <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-primary-100 text-primary-600 rounded-lg"><i class="fas fa-clock text-xl"></i></div>
                         <div class="ml-4">
                            <h4 class="text-sm font-semibold text-gray-900">Support Hours</h4>
                            <p class="mt-1 text-sm text-gray-600">Monday - Friday: 9am - 6pm (EST)</p>
                        </div>
                    </div>

                     <!-- Social Links -->
                     <div class="pt-4">
                         <h3 class="text-lg font-semibold text-gray-900 mb-4">Follow Us</h3>
                        <div class="flex space-x-5">
                            <a href="#" class="text-gray-400 hover:text-primary-600 transition duration-150">
                                <i class="fab fa-facebook-f text-2xl"></i><span class="sr-only">Facebook</span>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-primary-600 transition duration-150">
                                <i class="fab fa-twitter text-2xl"></i><span class="sr-only">Twitter</span>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-primary-600 transition duration-150">
                                <i class="fab fa-linkedin-in text-2xl"></i><span class="sr-only">LinkedIn</span>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-primary-600 transition duration-150">
                                <i class="fab fa-instagram text-2xl"></i><span class="sr-only">Instagram</span>
                            </a>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-12">
                <!-- Footer Column 1: Brand -->
                <div class="col-span-2 lg:col-span-1">
                     <a href="Home.php" class="flex items-center mb-4">
                        <div class="flex items-center justify-center w-8 h-8 bg-primary-600 text-white rounded-md mr-2">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <span class="text-xl font-bold text-white">EduPro</span>
                    </a>
                    <p class="text-sm mb-4 pr-4">Empowering learners worldwide with accessible, high-quality online education.</p>
                </div>
                 <!-- Footer Column 2: Courses -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Courses</h3>
                    <ul class="space-y-2">
                        <li><a href="#courses" class="text-sm hover:text-white transition">Web Development</a></li>
                        <li><a href="#courses" class="text-sm hover:text-white transition">Data Science</a></li>
                        <li><a href="#courses" class="text-sm hover:text-white transition">Digital Marketing</a></li>
                        <li><a href="#courses" class="text-sm hover:text-white transition">Business</a></li>
                        <li><a href="#courses" class="text-sm hover:text-white transition">Design</a></li>
                    </ul>
                </div>
                <!-- Footer Column 3: Company -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="text-sm hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="text-sm hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-sm hover:text-white transition">Press</a></li>
                        <li><a href="#" class="text-sm hover:text-white transition">Partners</a></li>
                    </ul>
                </div>
                 <!-- Footer Column 4: Support -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm hover:text-white transition">Help Center</a></li>
                        <li><a href="#contact" class="text-sm hover:text-white transition">Contact Us</a></li>
                        <li><a href="#faq" class="text-sm hover:text-white transition">FAQ</a></li>
                        <li><a href="#" class="text-sm hover:text-white transition">Community</a></li>
                    </ul>
                </div>
                 <!-- Footer Column 5: Legal -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="text-sm hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm hover:text-white transition">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="mt-12 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center">
                <p class="text-xs text-gray-500">© <?php echo date("Y"); ?> EduPro Learning Solutions. All rights reserved.</p>
                 <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition"><i class="fab fa-facebook-f"></i><span class="sr-only">Facebook</span></a>
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition"><i class="fab fa-twitter"></i><span class="sr-only">Twitter</span></a>
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition"><i class="fab fa-linkedin-in"></i><span class="sr-only">LinkedIn</span></a>
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition"><i class="fab fa-instagram"></i><span class="sr-only">Instagram</span></a>
                </div>
            </div>
        </div>
    </footer>


    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuIcon = mobileMenuButton?.querySelector('i'); // Use optional chaining

        if (mobileMenuButton && mobileMenu && menuIcon) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                // Toggle between bars and times icons
                 if (menuIcon.classList.contains('fa-bars')) {
                     menuIcon.classList.remove('fa-bars');
                     menuIcon.classList.add('fa-times');
                 } else {
                     menuIcon.classList.remove('fa-times');
                     menuIcon.classList.add('fa-bars');
                 }
            });
        }

        // Accordion functionality
        // No JS needed if using checkbox toggle technique with CSS pseudo-selectors

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                 const targetId = this.getAttribute('href');
                 if (targetId && targetId.length > 1 && targetId.startsWith('#')) {
                     const targetElement = document.querySelector(targetId);
                     if (targetElement) {
                         e.preventDefault();
                         const headerOffset = 80; // Height of the fixed navbar (adjust if needed: h-20 = 5rem = 80px)
                         const elementPosition = targetElement.getBoundingClientRect().top;
                         const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                         window.scrollTo({
                             top: offsetPosition,
                             behavior: 'smooth'
                         });

                         // Close mobile menu if open after clicking a link
                         if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                           mobileMenu.classList.add('hidden');
                           if (menuIcon) {
                               menuIcon.classList.remove('fa-times'); // Reset icon
                               menuIcon.classList.add('fa-bars');
                           }
                         }
                     }
                 }
            });
        });
    </script>
</body>
</html>