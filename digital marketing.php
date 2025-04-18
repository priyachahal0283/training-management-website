<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require database configuration (this sets $mysqli)
require_once 'config.php'; // <--- Sets up $mysqli

// --- Course Specific Variables ---
// *** IMPORTANT: Unique Slug for this course ***
$course_slug = 'digital-marketing-masterclass-2024'; // Example unique slug
$course_title_display = 'The Complete Digital Marketing Masterclass 2024'; // Title from the page
$is_paid_course = true; // Assume paid based on price shown

// --- User Enrollment Status ---
$is_enrolled = false;
$user_logged_in = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id']));
$user_id = null;

// Check enrollment only if user is logged in AND database connection exists
if ($user_logged_in) {
    $user_id = $_SESSION['id'];
    // === Use $mysqli from config.php ===
    if (isset($mysqli) && $mysqli instanceof mysqli && !$mysqli->connect_error) {
        $sql_check_enroll = "SELECT id FROM user_courses WHERE user_id = ? AND course_slug = ?";
        // === Use $mysqli ===
        if ($stmt_check = mysqli_prepare($mysqli, $sql_check_enroll)) {
             // Use the $course_slug for this specific page
            mysqli_stmt_bind_param($stmt_check, "is", $user_id, $course_slug);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $is_enrolled = true;
            }
            mysqli_stmt_close($stmt_check);
        } else {
             // === Use $mysqli ===
            error_log("Error preparing enrollment check statement for course '$course_slug': " . mysqli_error($mysqli));
        }
    } else {
        // === Use $mysqli ===
        $db_error = isset($mysqli) && $mysqli instanceof mysqli ? $mysqli->connect_error : '$mysqli variable not set or not a mysqli object';
        error_log("Database connection error in page for course '$course_slug': " . $db_error);
    }
}

// --- Utility Function ---
function safe_echo($str) {
    echo htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamic Title -->
    <title><?php safe_echo($course_title_display); ?> | EduPro</title>
    <meta name="description" content="Master SEO, Social Media, Google Ads, Email Marketing and more with our comprehensive digital marketing course. Build real campaigns and grow your business.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Tailwind configuration (Using standard blue primary color)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { // Standard Blue
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af'
                        },
                        secondary: {
                            400: '#60a5fa',
                            500: '#3b82f6'
                        },
                         // Keep template's purple if needed
                        template_primary: {
                             600: '#7c3aed',
                             700: '#6d28d9',
                             800: '#5b21b6'
                         }
                    },
                     fontFamily: {
                        sans: ['Inter', 'sans-serif'], // Standard Font
                    },
                    animation: { // Keep animations
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite'
                    },
                    keyframes: { // Keep keyframes
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
        /* Custom CSS styles (Combined) */
         @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
         body { font-family: 'Inter', sans-serif; }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .accordion-toggle:checked ~ .accordion-content { max-height: 1000px; }
        .lesson-checkbox:checked + label { text-decoration: line-through; color: #9CA3AF; }
        .lesson-checkbox:checked + label .fa-check { display: inline-block !important; }
        .progress-bar { transition: width 0.5s ease; }
        .tooltip { opacity: 0; transition: opacity 0.2s ease; pointer-events: none; position: absolute; z-index: 10; }
        .has-tooltip:hover .tooltip { opacity: 1; }
        .spinner { display: inline-block; border: 3px solid rgba(255,255,255,.3); border-left-color: #fff; border-radius: 50%; width: 1rem; height: 1rem; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        button:disabled, a.disabled { opacity: 0.6; cursor: not-allowed; }
        #curriculum-section { scroll-margin-top: 90px; } /* Added scroll margin */
    </style>
</head>
<body class="bg-gray-900 text-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800 border-b border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                 <!-- Left Side Nav -->
                <div class="flex items-center">
                    <a href="Home.php" class="flex items-center"> <!-- Link to Home -->
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i> <!-- Standard blue -->
                        <span class="text-xl font-bold">EduPro</span>
                    </a>
                    <div class="hidden md:block ml-10">
                        <div class="flex space-x-4">
                            <a href="courses_list.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Courses</a> <!-- Link to Courses List -->
                            <a href="resources.html" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Resources</a>
                        </div>
                    </div>
                </div>
                 <!-- Right Side Nav -->
                <div class="hidden md:block">
                    <div class="flex items-center">
                        <div class="relative mx-4">
                            <input type="text" placeholder="Search courses..." class="bg-gray-700 text-white px-4 py-2 rounded-md w-64 focus:outline-none focus:ring-2 focus:ring-primary-600">
                            <button class="absolute right-3 top-2.5 text-gray-400">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>

                        <!-- === LOGIN/SIGNUP/PROFILE AREA START === -->
                        <?php if ($user_logged_in): ?>
                             <div class="relative">
                                <a href="Dashboard.php" class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-2 rounded-full transition" title="Go to Dashboard">
                                     <span class="inline-block h-8 w-8 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center">
                                          <i class="fas fa-user text-gray-300"></i>
                                     </span>
                                    <!-- Completion badge can be added here if needed -->
                                    <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                                </a>
                             </div>
                        <?php else: ?>
                            <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="px-4 py-2 rounded-md hover:bg-gray-700 transition text-sm font-medium">Log in</a>
                            <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="ml-2 px-4 py-2 bg-primary-600 rounded-md hover:bg-primary-700 transition text-sm font-medium">Sign up</a>
                        <?php endif; ?>
                        <!-- === LOGIN/SIGNUP/PROFILE AREA END === -->

                    </div>
                </div>
                 <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button class="mobile-menu-button p-2 rounded-md hover:bg-gray-700 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu (hidden by default) -->
    <div class="mobile-menu hidden md:hidden bg-gray-800">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
             <a href="courses_list.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700">Courses</a>
             <a href="resources.html" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Resources</a>
             <div class="pt-4 border-t border-gray-700">
                  <!-- === Mobile Login/Signup/Profile === -->
                  <?php if ($user_logged_in): ?>
                       <a href="Dashboard.php" class="flex items-center w-full px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700">
                            <span class="inline-block h-8 w-8 mr-3 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center"> <i class="fas fa-user text-gray-300"></i> </span> My Dashboard
                       </a>
                       <a href="logout.php" class="block w-full mt-2 px-3 py-2 text-left rounded-md text-base font-medium hover:bg-gray-700">Log out</a>
                  <?php else: ?>
                     <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="block w-full px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700">Log in</a>
                     <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="block w-full mt-2 px-3 py-2 bg-primary-600 rounded-md hover:bg-primary-700 transition text-center">Sign up</a>
                  <?php endif; ?>
                  <!-- === End Mobile Login/Signup/Profile === -->
             </div>
         </div>
    </div>

    <!-- Hero Section -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Course Content -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                    <div class="flex items-center space-x-2">
                        <span class="inline-block bg-green-500 text-black px-2 py-1 rounded-md text-xs font-bold">BESTSELLER</span>
                        <span class="inline-block bg-blue-500 text-white px-2 py-1 rounded-md text-xs font-bold">UPDATED</span>
                    </div>
                     <!-- Dynamic Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mt-3"><?php safe_echo($course_title_display); ?></h1>
                    <p class="text-gray-400 mt-2 text-lg">Master SEO, Social Media, Google Ads, Email Marketing, and More | Grow Your Business Online</p>

                    <!-- Ratings/Info (Original) -->
                    <div class="flex flex-wrap items-center mt-4 gap-2"> <div class="flex items-center"> <div class="flex items-center text-yellow-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-1 text-sm">4.6 (48,000 ratings)</span></div><span class="text-gray-400 text-sm">•</span><span class="text-sm">250,000+ learners</span><span class="text-gray-400 text-sm">•</span><span class="text-sm">Last updated 5/2024</span></div>
                    <div class="flex items-center mt-3"> <span class="text-sm">Created by </span> <a href="#" class="text-secondary-400 hover:underline ml-1 text-sm">Sarah Johnson</a><span class="text-sm mx-1">,</span> <a href="#" class="text-secondary-400 hover:underline text-sm">Michael Lee</a> </div>
                    <div class="mt-6"> <div class="flex items-center"><i class="fas fa-globe text-gray-400 mr-2"></i><span class="text-sm">English</span></div><div class="flex items-center mt-2"><i class="fas fa-closed-captioning text-gray-400 mr-2"></i><span class="text-sm">English [Auto], Spanish, Portuguese</span></div></div>
                </div>

                <!-- Course Tabs (Original) -->
                <div class="mt-6">
                    <div class="border-b border-gray-700">
                         <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                              <!-- Use standard blue for active tab -->
                             <a href="#" class="border-b-2 border-primary-600 text-primary-500 whitespace-nowrap py-4 px-1 font-medium text-sm" aria-current="page">Overview</a>
                             <a href="#curriculum-section" class="border-b-2 border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500 whitespace-nowrap py-4 px-1 font-medium text-sm">Curriculum</a>
                             <a href="#" class="border-b-2 border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500 whitespace-nowrap py-4 px-1 font-medium text-sm">Instructors</a>
                             <a href="#" class="border-b-2 border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500 whitespace-nowrap py-4 px-1 font-medium text-sm">Reviews</a>
                             <a href="#" class="border-b-2 border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500 whitespace-nowrap py-4 px-1 font-medium text-sm">FAQ</a>
                         </nav>
                    </div>
                </div>

                <!-- Course Highlights (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                     <h2 class="text-2xl font-bold">What you'll learn</h2>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-sm">
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Master SEO to rank websites on Google's first page</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Run successful social media campaigns on all platforms</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Create and optimize Google Ads for maximum ROI</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Build effective email marketing funnels</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Analyze data using Google Analytics</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Develop a comprehensive digital marketing strategy</span></div>
                     </div>
                </div>

                <!-- Course Content Accordion (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl shadow-lg overflow-hidden" id="curriculum-section">
                     <h2 class="text-2xl font-bold p-6">Course content</h2>
                     <div class="divide-y divide-gray-700">
                          <!-- Module 1 --> <div class="accordion-item"> <input type="checkbox" id="module1" class="accordion-toggle hidden"> <label for="module1" class="flex justify-between items-center p-4 hover:bg-gray-700 cursor-pointer"> <div class="flex items-center"> <i class="fas fa-chevron-down accordion-icon transform transition-transform mr-3 text-sm"></i> <span class="font-medium text-sm">Module 1: Digital Marketing Fundamentals</span> </div><span class="text-xs text-gray-400">5 lectures • 2h 30m</span></label> <div class="accordion-content"> <div class="pl-12 pr-4 pb-4 text-sm"> <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Introduction to Digital Marketing</span></div><span class="text-xs text-gray-400">25m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>The Digital Marketing Landscape</span></div><span class="text-xs text-gray-400">30m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Creating Your Marketing Strategy</span></div><span class="text-xs text-gray-400">40m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Understanding Your Audience</span></div><span class="text-xs text-gray-400">35m</span></div><div class="flex justify-between items-center py-2"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Project: Develop a Marketing Plan</span></div><span class="text-xs text-gray-400">1h 20m</span></div></div></div></div>
                          <!-- Module 2 --> <div class="accordion-item"> <input type="checkbox" id="module2" class="accordion-toggle hidden"> <label for="module2" class="flex justify-between items-center p-4 hover:bg-gray-700 cursor-pointer"> <div class="flex items-center"> <i class="fas fa-chevron-down accordion-icon transform transition-transform mr-3 text-sm"></i> <span class="font-medium text-sm">Module 2: Search Engine Optimization (SEO)</span> </div><span class="text-xs text-gray-400">7 lectures • 3h 45m</span></label> <div class="accordion-content"> <div class="pl-12 pr-4 pb-4 text-sm"> <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>SEO Fundamentals</span></div><span class="text-xs text-gray-400">45m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Keyword Research Mastery</span></div><span class="text-xs text-gray-400">50m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>On-Page Optimization</span></div><span class="text-xs text-gray-400">40m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Technical SEO</span></div><span class="text-xs text-gray-400">35m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Link Building Strategies</span></div><span class="text-xs text-gray-400">30m</span></div><div class="flex justify-between items-center py-2"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Project: Optimize a Website for SEO</span></div><span class="text-xs text-gray-400">1h 25m</span></div></div></div></div>
                         <!-- Add more modules as needed -->
                     </div>
                     <div class="p-4 text-center border-t border-gray-700"> <button class="text-primary-600 hover:underline text-sm">Show all 10 modules</button> </div> <!-- Use standard blue -->
                </div>

                <!-- Requirements (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                       <h2 class="text-2xl font-bold">Requirements</h2>
                       <ul class="list-disc list-inside text-gray-300 mt-4 space-y-2 text-sm">
                           <li>No prior marketing experience needed</li>
                           <li>A computer with internet access</li>
                           <li>Basic understanding of social media platforms</li>
                           <li>Willingness to implement strategies as you learn</li>
                       </ul>
                </div>

                <!-- Description (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                       <h2 class="text-2xl font-bold">Description</h2>
                       <div class="mt-4 text-gray-300 space-y-4 text-sm">
                           <p>Welcome to <span class="font-semibold text-primary-500">The Complete Digital Marketing Masterclass 2024</span>, the most comprehensive digital marketing course...</p> <!-- Use standard blue -->
                           <p>Whether you're a complete beginner looking to start a career...</p>
                           <h3 class="text-xl font-semibold mt-4">Why This Course?</h3>
                           <ul class="list-disc list-inside space-y-2 ml-4"> <li><span class="font-medium">Practical</span> - With real-world campaigns...</li><li><span class="font-medium">Comprehensive</span> - Covers all major digital...</li><li><span class="font-medium">Up-to-date</span> - Includes 2024 algorithm changes...</li><li><span class="font-medium">Results-focused</span> - Teaches strategies...</li></ul>
                           <h3 class="text-xl font-semibold mt-4">What's Included?</h3>
                           <ul class="list-disc list-inside space-y-2 ml-4"> <li>Lifetime access...</li><li>Downloadable resources...</li><li>Certificate of completion</li><li>Access to our exclusive marketer community</li><li>Regular content updates...</li></ul>
                           <p class="mt-4">By the end of this course, you'll have run real campaigns...</p>
                           <div class="bg-gray-700 p-4 rounded-lg mt-6"> <h4 class="font-bold text-lg">30-Day Money-Back Guarantee</h4> <p class="mt-2 text-xs">We're confident you'll love this course...</p></div>
                       </div>
                </div>

                <!-- Instructor Section (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                        <h2 class="text-2xl font-bold">Instructors</h2>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Instructor 1 --> <div class="flex items-start"> <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson" class="w-20 h-20 rounded-full object-cover border-2 border-primary-600"> <div class="ml-4"> <h3 class="text-xl font-bold">Sarah Johnson</h3> <p class="text-gray-400 text-sm">Digital Marketing Expert</p><div class="flex items-center mt-2"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs">4.7 Instructor Rating</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-user-graduate text-gray-400 mr-2 w-4 text-center"></i><span>250,000+ Students</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>12 Courses</span></div><p class="mt-3 text-gray-300 text-xs">Sarah has over 10 years of experience...</p><div class="flex space-x-3 mt-3"><a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-linkedin"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fas fa-globe"></i></a></div></div></div>
                            <!-- Instructor 2 --> <div class="flex items-start"> <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Michael Lee" class="w-20 h-20 rounded-full object-cover border-2 border-primary-600"> <div class="ml-4"> <h3 class="text-xl font-bold">Michael Lee</h3> <p class="text-gray-400 text-sm">SEO & PPC Specialist</p><div class="flex items-center mt-2"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span class="ml-2 text-xs">4.9 Instructor Rating</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-user-graduate text-gray-400 mr-2 w-4 text-center"></i><span>180,000+ Students</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>8 Courses</span></div><p class="mt-3 text-gray-300 text-xs">Michael is a certified Google Ads...</p><div class="flex space-x-3 mt-3"><a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-linkedin"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fas fa-globe"></i></a></div></div></div>
                        </div>
                </div>

                <!-- Student Feedback (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                      <h2 class="text-2xl font-bold">Student feedback</h2>
                      <div class="mt-4 flex flex-col md:flex-row items-center"> <div class="text-center md:text-left md:mr-8"> <div class="text-5xl font-bold text-primary-500">4.6</div><div class="flex justify-center md:justify-start mt-1"><div class="flex items-center text-yellow-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div></div><div class="text-sm mt-1 text-gray-400">Course Rating</div></div><div class="w-full mt-4 md:mt-0"> <div class="flex items-center text-xs"><span class="w-10">5 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 72%"></div></div><span class="w-10 text-right">72%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">4 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 20%"></div></div><span class="w-10 text-right">20%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">3 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 5%"></div></div><span class="w-10 text-right">5%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">2 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 2%"></div></div><span class="w-10 text-right">2%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">1 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 1%"></div></div><span class="w-10 text-right">1%</span></div></div></div>
                      <div class="mt-8"> <h3 class="text-lg font-semibold">Reviews</h3> <div class="space-y-6 mt-4"> <!-- Review 1 --> <div class="border-b border-gray-700 pb-6"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/women/63.jpg" alt="Priya S." class="w-10 h-10 rounded-full"> <div class="ml-3"> <div class="font-medium text-sm">Priya S.</div><div class="flex items-center text-xs text-gray-400"><div class="flex items-center text-yellow-400 mr-1 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span>3 months ago</span></div></div></div><div class="mt-3 text-sm"> <h4 class="font-semibold text-base">Transformed my business!</h4> <p class="text-gray-300 mt-1">This course helped me take my small business online...</p></div></div><!-- Review 2 --> <div class="border-b border-gray-700 pb-6"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/men/82.jpg" alt="David R." class="w-10 h-10 rounded-full"> <div class="ml-3"> <div class="font-medium text-sm">David R.</div><div class="flex items-center text-xs text-gray-400"><div class="flex items-center text-yellow-400 mr-1 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span>1 month ago</span></div></div></div><div class="mt-3 text-sm"> <h4 class="font-semibold text-base">Best marketing course I've taken</h4> <p class="text-gray-300 mt-1">I've taken several digital marketing courses before...</p></div></div></div> <button class="mt-6 text-primary-600 hover:underline text-sm">See all 10,250 reviews</button> </div> <!-- Use standard blue -->
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-20">
                    <!-- Course Card -->
                    <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-700">
                        <div class="relative">
                              <img src="https://images.unsplash.com/photo-1499750310107-5fef28a66643?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="<?php safe_echo($course_title_display); ?>" class="w-full h-48 object-cover">
                            <div class="absolute top-3 left-3 bg-black/70 text-white px-2 py-1 rounded-md text-xs"> <i class="fas fa-play-circle mr-1"></i> <span>Preview available</span> </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between"> <p class="text-2xl font-bold">₹599</p> <div class="text-right"> <p class="text-gray-400 line-through">₹2,999</p> <p class="text-red-500 font-bold">80% off</p> </div></div>
                            <p class="text-red-500 text-sm mt-1 flex items-center"> <i class="fas fa-clock mr-1"></i> <span id="countdown-timer">3 hours left at this price!</span> </p>

                            <!-- === Dynamic Enrollment Buttons START === -->
                            <div class="mt-6 space-y-3" id="enrollment-action-area">
                                <?php if ($is_enrolled): ?>
                                     <a href="#curriculum-section" class="w-full flex items-center justify-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-medium text-base shadow-md">
                                         <i class="fas fa-play-circle mr-2"></i>Continue Learning
                                     </a>
                                     <p class="text-center text-sm text-green-400 mt-2">You are enrolled in this course.</p>
                                <?php else: ?>
                                     <button id="enroll-btn" data-course-slug="<?php safe_echo($course_slug); ?>" class="w-full flex items-center justify-center bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition font-medium text-base shadow-md">
                                         <span class="btn-text">Enroll Now</span>
                                         <span class="spinner hidden ml-2"></span>
                                     </button>
                                      <!-- Keep original buy/cart buttons if needed -->
                                      <button class="w-full bg-gray-700 text-white py-3 rounded-lg hover:bg-gray-600 transition font-medium">
                                          Add to cart (Original)
                                      </button>
                                      <!-- <button class="w-full bg-primary-700 text-white py-3 rounded-lg hover:bg-primary-800 transition font-medium"> Buy now </button> -->
                                <?php endif; ?>
                            </div>
                            <?php if (!$is_enrolled && !$user_logged_in): ?>
                                <p class="text-xs text-center text-gray-400 mt-3">
                                   <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-500 hover:underline">Log in</a> or <!-- Use standard blue -->
                                   <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-500 hover:underline">sign up</a>
                                   to enroll.
                                </p>
                            <?php endif; ?>
                             <!-- === Dynamic Enrollment Buttons END === -->


                            <p class="text-center text-sm mt-4 text-gray-400">30-Day Money-Back Guarantee</p>

                            <div class="mt-6">
                                <h3 class="font-bold text-sm">This course includes:</h3>
                                <ul class="mt-3 space-y-2 text-sm">
                                     <li class="flex items-center"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>50 hours on-demand video</span></li>
                                     <li class="flex items-center"><i class="fas fa-file-alt text-gray-400 mr-2 w-4 text-center"></i><span>35 articles</span></li>
                                     <li class="flex items-center"><i class="fas fa-download text-gray-400 mr-2 w-4 text-center"></i><span>30 downloadable resources</span></li>
                                     <li class="flex items-center"><i class="fas fa-infinity text-gray-400 mr-2 w-4 text-center"></i><span>Full lifetime access</span></li>
                                     <li class="flex items-center"><i class="fas fa-mobile-alt text-gray-400 mr-2 w-4 text-center"></i><span>Access on mobile and TV</span></li>
                                     <li class="flex items-center"><i class="fas fa-certificate text-gray-400 mr-2 w-4 text-center"></i><span>Certificate of completion</span></li>
                                </ul>
                            </div>

                            <div class="mt-6 border-t border-gray-700 pt-4">
                                <button class="w-full text-center text-primary-500 hover:underline font-medium text-sm"> <!-- Use standard blue -->
                                    Gift this course
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Training Programs (Original) -->
                    <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                           <h3 class="font-bold text-sm">Training 5 or more people?</h3>
                           <p class="text-gray-400 text-xs mt-2">Get your team access to 5,000+ top courses anytime, anywhere.</p>
                           <button class="w-full mt-4 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition text-sm"> Learn about team plans </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Related Courses (Original) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <h2 class="text-2xl font-bold mb-8">Students also bought</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Course 1 --> <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="SEO Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">SEO Mastery: Rank #1 on Google</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs text-gray-400">(15,320)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹499</span><span class="text-xs text-gray-400 line-through">₹2,499</span></div></div></div>
              <!-- Course 2 --> <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1611162616475-46b635cb6868?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Social Media Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">Social Media Marketing Mastery</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span class="ml-2 text-xs text-gray-400">(12,450)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹399</span><span class="text-xs text-gray-400 line-through">₹1,999</span></div></div></div>
              <!-- Course 3 --> <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1552581234-26160f608093?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Google Ads Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">Google Ads & Analytics Complete Guide</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs text-gray-400">(8,720)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹599</span><span class="text-xs text-gray-400 line-through">₹2,999</span></div></div></div>
          </div>
    </div>

    <!-- FAQ Section (Original) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
         <h2 class="text-2xl font-bold mb-8">Frequently asked questions</h2>
         <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
             <div class="divide-y divide-gray-700">
                  <!-- FAQ Item 1 --><div class="accordion-item"><input type="checkbox" id="faq1" class="accordion-toggle hidden"><label for="faq1" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">How is this course different from free YouTube tutorials?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>While free tutorials can be helpful...</p></div></div></div>
                  <!-- FAQ Item 2 --><div class="accordion-item"><input type="checkbox" id="faq2" class="accordion-toggle hidden"><label for="faq2" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">Will I get certified after completing this course?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>Yes! Upon completion, you'll receive a certificate...</p></div></div></div>
                  <!-- FAQ Item 3 --><div class="accordion-item"><input type="checkbox" id="faq3" class="accordion-toggle hidden"><label for="faq3" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">How long will it take to complete the course?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>The course contains about 50 hours of content...</p></div></div></div>
                  <!-- FAQ Item 4 --><div class="accordion-item"><input type="checkbox" id="faq4" class="accordion-toggle hidden"><label for="faq4" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">Will this course help me get a job in digital marketing?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>Absolutely! Many of our students have landed jobs...</p></div></div></div>
             </div>
         </div>
    </div>

    <!-- Footer (Original) -->
    <footer class="bg-gray-800 border-t border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12"> <div class="grid grid-cols-1 md:grid-cols-4 gap-8"> <div> <div class="flex items-center"> <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i> <span class="text-xl font-bold">EduPro</span> </div><p class="mt-4 text-gray-400 text-sm">Learn the latest marketing skills to grow your business or career.</p><div class="flex space-x-4 mt-4"> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-youtube"></i></a> </div></div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Company</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">About</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Careers</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Blog</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Press</a></li></ul> </div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Support</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">Contact Us</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Help Center</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Privacy Policy</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Terms of Service</a></li></ul> </div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Resources</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">Marketing Blog</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Free Tools</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Guides</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Community</a></li></ul> </div></div><div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center"> <p class="text-gray-400 text-sm">© <?php echo date('Y'); ?> EduPro, Inc. All rights reserved.</p><div class="mt-4 md:mt-0 flex space-x-6"> <a href="#" class="text-gray-400 hover:text-white text-sm">Privacy</a> <a href="#" class="text-gray-400 hover:text-white text-sm">Terms</a> <a href="#" class="text-gray-400 hover:text-white text-sm">Sitemap</a> </div></div></div>
    </footer>

    <script>
        // --- JAVASCRIPT (Combined) ---
        document.addEventListener('DOMContentLoaded', function() {

             // --- Mobile menu toggle ---
             const mobileMenuButton = document.querySelector('.mobile-menu-button'); const mobileMenu = document.querySelector('.mobile-menu'); if (mobileMenuButton && mobileMenu) { /* ... Same logic ... */ mobileMenuButton.addEventListener('click', function() { mobileMenu.classList.toggle('hidden'); const icon = mobileMenuButton.querySelector('i'); if (icon) { icon.classList.toggle('fa-bars'); icon.classList.toggle('fa-times'); } }); }
             // --- Accordion functionality ---
             document.querySelectorAll('.accordion-toggle').forEach(toggle => { /* ... Same logic ... */ toggle.addEventListener('change', function() { const icon = this.closest('.accordion-item').querySelector('.accordion-icon'); if (icon) { icon.classList.toggle('rotate-180', this.checked); } }); });
             // --- Countdown timer ---
             const countdownElement = document.getElementById('countdown-timer'); if (countdownElement) { function updateCountdown() { /* ... Same logic ... */ const now = new Date(); const endTime = new Date(now.getTime() + 3 * 60 * 60 * 1000); const timer = setInterval(function() { const now = new Date(); const distance = endTime - now; if (distance < 0) { clearInterval(timer); if(countdownElement) countdownElement.textContent = 'Discount expired!'; return; } const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)); const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)); const seconds = Math.floor((distance % (1000 * 60)) / 1000); if(countdownElement) { countdownElement.textContent = `${hours}h ${minutes}m ${seconds}s left at this price!`; } else { clearInterval(timer); } }, 1000); } updateCountdown(); }
             // --- Smooth scrolling ---
             document.querySelectorAll('a[href^="#"]').forEach(anchor => { /* ... Same logic ... */ anchor.addEventListener('click', function(e) { const targetId = this.getAttribute('href'); const targetElement = document.querySelector(targetId); if (targetElement) { e.preventDefault(); targetElement.scrollIntoView({ behavior: 'smooth' }); } }); });

            // --- Progress & Enrollment Logic ---
             const checkboxes = document.querySelectorAll('.lesson-checkbox'); // Add these to HTML
             const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 10; // Adjust fallback

             function updateProgress() { /* ... Same logic ... */ const progressBar = document.getElementById('progress-bar'); const progressText = document.getElementById('progress-text'); if (!progressBar || !progressText) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; const progress = totalLessonsForProgress > 0 ? Math.round((completed / totalLessonsForProgress) * 100) : 0; progressBar.style.width = progress + '%'; progressText.textContent = `${completed}/${totalLessonsForProgress} lessons completed`; progressBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-primary-600'); if (progress < 30) { progressBar.classList.add('bg-red-500'); } else if (progress < 70) { progressBar.classList.add('bg-yellow-500'); } else if (progress >= 100) { progressBar.classList.add('bg-green-500'); } else { progressBar.classList.add('bg-primary-600'); } }
             function updateCompletionBadge() { /* ... Same logic ... */ const completionBadge = document.getElementById('completion-badge'); if (!completionBadge) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; if (completed > 0) { completionBadge.textContent = completed; completionBadge.classList.remove('hidden'); } else { completionBadge.classList.add('hidden'); } }
             checkboxes.forEach(checkbox => { /* ... Same logic ... */ const lessonId = checkbox.id; const storageKey = lessonId + '_<?php safe_echo($course_slug); ?>'; const savedState = localStorage.getItem(storageKey); checkbox.checked = (savedState === 'true'); const label = checkbox.nextElementSibling; const checkIcon = label ? label.querySelector('.fa-check') : null; if (checkbox.checked) { if (checkIcon) checkIcon.classList.remove('hidden'); if (label) label.classList.add('line-through', 'text-gray-500'); } else { if (checkIcon) checkIcon.classList.add('hidden'); if (label) label.classList.remove('line-through', 'text-gray-500'); } checkbox.addEventListener('change', function() { localStorage.setItem(storageKey, this.checked); const label = this.nextElementSibling; const checkIcon = label ? label.querySelector('.fa-check') : null; if (this.checked) { if (checkIcon) checkIcon.classList.remove('hidden'); if (label) label.classList.add('line-through', 'text-gray-500'); } else { if (checkIcon) checkIcon.classList.add('hidden'); if (label) label.classList.remove('line-through', 'text-gray-500'); } updateProgress(); updateCompletionBadge(); }); });
             updateProgress(); updateCompletionBadge();

            // --- Enrollment Button Logic ---
            const enrollButton = document.getElementById('enroll-btn'); // Target sidebar button
            const enrollmentActionArea = document.getElementById('enrollment-action-area'); // Target sidebar div
            const isLoggedIn = <?php echo json_encode($user_logged_in); ?>;
            const isPaidCourse = <?php echo json_encode($is_paid_course); ?>;

            if (enrollButton && enrollmentActionArea) { /* ... Same enrollment fetch logic ... */
                const enrollButtonText = enrollButton.querySelector('.btn-text'); const enrollButtonSpinner = enrollButton.querySelector('.spinner');
                enrollButton.addEventListener('click', function() { if (!isLoggedIn) { alert('Please log in or sign up to enroll.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); return; } if (isPaidCourse) { console.log("Paid course - Add payment flow here."); } const courseSlug = this.dataset.courseSlug; const button = this; button.disabled = true; if (enrollButtonText) enrollButtonText.textContent = 'Enrolling...'; else button.textContent = 'Enrolling...'; if (enrollButtonSpinner) enrollButtonSpinner.classList.remove('hidden'); button.classList.add('opacity-60', 'cursor-wait'); const formData = new FormData(); formData.append('course_slug', courseSlug);
                fetch('enroll_course.php', { method: 'POST', body: formData })
                .then(response => { if (!response.ok) { return response.text().then(text => { throw new Error(`Server responded with status ${response.status}. Response: ${text || '(empty)'}`); }); } const contentType = response.headers.get("content-type"); if (contentType && contentType.indexOf("application/json") !== -1) { return response.json(); } else { return response.text().then(text => { throw new Error(`Unexpected response format. Expected JSON, got: ${text || '(empty)'}`); }); } })
                .then(data => { if (data.success) { console.log("Enrollment successful:", data.message); enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="w-full flex items-center justify-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-medium text-base shadow-md"><i class="fas fa-play-circle mr-2"></i>Continue Learning</a> <p class="text-center text-sm text-green-400 mt-2">You are enrolled in this course.</p>`; } else { if (data.action === 'redirect_login') { alert('Session issue. Please log in again.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); } else if (data.message && data.message.includes('already enrolled')) { console.warn("Already enrolled message:", data.message); enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="w-full flex items-center justify-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-medium text-base shadow-md"><i class="fas fa-play-circle mr-2"></i>Continue Learning</a> <p class="text-center text-sm text-green-400 mt-2">You are enrolled in this course.</p>`; } else { alert('Enrollment failed: ' + (data.message || 'Unknown error.')); button.disabled = false; if (enrollButtonText) enrollButtonText.textContent = 'Enroll Now'; else button.textContent = 'Enroll Now'; if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); } } })
                .catch(error => { console.error('Enrollment fetch error:', error); alert('An error occurred while enrolling. Please try again.\nError: ' + error.message); button.disabled = false; if (enrollButtonText) enrollButtonText.textContent = 'Enroll Now'; else button.textContent = 'Enroll Now'; if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); }); });
            } else { console.log("Enrollment button or action area not found."); }

        }); // End DOMContentLoaded
    </script>
</body>
</html>
<?php
// Close the database connection if it was opened and is still open
if (isset($mysqli) && $mysqli instanceof mysqli && !$mysqli->connect_error && $mysqli->thread_id) {
   mysqli_close($mysqli);
}
?>