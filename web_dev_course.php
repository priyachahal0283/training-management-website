<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require database configuration (this sets $mysqli)
require_once 'config.php'; // <--- Sets up $mysqli

// --- Course Specific Variables ---
// *** IMPORTANT: Unique Slug for this course ***
$course_slug = 'web-dev-bootcamp-2024'; // Example unique slug
$course_title_display = 'The Complete Web Development Bootcamp 2024'; // Title from the page
$is_paid_course = true; // Assume paid based on price shown

// --- User Enrollment Status ---
$is_enrolled = false;
$user_logged_in = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id']));
$user_id = null;
$db_connection_error = false; // Flag for connection issues

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
            error_log("Error preparing enrollment check for course '$course_slug': " . mysqli_error($mysqli));
            $db_connection_error = true;
        }
    } else {
        // === Use $mysqli ===
        $db_error_msg = isset($mysqli) && $mysqli instanceof mysqli ? $mysqli->connect_error : '$mysqli variable not set or not a mysqli object';
        error_log("Database connection error in page for course '$course_slug': " . $db_error_msg);
        $db_connection_error = true;
    }
}

// --- Determine Button State based on logic ---
$button_text = $is_paid_course ? 'Enroll Now' : 'Start Free Course'; // Default Text
$button_disabled = false; // Default state
$button_classes_extra = 'hover:bg-primary-700'; // Default hover effect (using template purple)

if ($is_enrolled) {
    // User is already enrolled
    $button_text = $is_paid_course ? '<i class="fas fa-check mr-2"></i>Enrolled' : '<i class="fas fa-play mr-2"></i>Continue Learning';
    $button_disabled = true;
    $button_classes_extra = 'opacity-75 cursor-not-allowed bg-green-600'; // Use green for continue/enrolled
} elseif ($db_connection_error) {
    // DB connection error prevents enrollment
     $button_text = 'Service Unavailable';
     $button_disabled = true;
     $button_classes_extra = 'opacity-50 cursor-not-allowed bg-gray-500';
} elseif (!$user_logged_in && !$is_paid_course) {
    // User not logged in, but it's a free course
    $button_text = 'Start Free Course';
} elseif (!$user_logged_in && $is_paid_course) {
     // User not logged in for a paid course
     $button_text = 'Enroll Now';
     // Need to adjust button color to purple if not logged in/enrolled
     $button_classes_extra = 'bg-primary-600 hover:bg-primary-700'; // Purple from template
} else {
     // Logged in, not enrolled, paid or free - use default text and purple color
     $button_classes_extra = 'bg-primary-600 hover:bg-primary-700'; // Purple from template
}
// --- END: PHP Logic ---


// --- Utility Function ---
function safe_echo($str) {
    echo htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
// Get user profile picture
$user_profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : "https://randomuser.me/api/portraits/men/46.jpg"; // Default picture

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamic Title -->
    <title><?php safe_echo($course_title_display); ?> | EduPro</title>
    <meta name="description" content="Master HTML, CSS, JavaScript, React, Node.js and more with our comprehensive web development bootcamp. Build real-world projects and launch your career.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Tailwind configuration (Original)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { // Keep original theme (Purple)
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6'
                        },
                        secondary: { // Standard Blue
                            400: '#60a5fa',
                            500: '#3b82f6'
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

        .course-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .accordion-toggle:checked ~ .accordion-content { max-height: 1000px; }
        .lesson-checkbox:checked + label { text-decoration: line-through; color: #9CA3AF; }
        .lesson-checkbox:checked + label .fa-check { display: inline-block !important; } /* Corrected */
        .lesson-checkbox + label .fa-check { display: none; } /* Hide by default */
        .progress-bar { transition: width 0.5s ease; }
        .tooltip { opacity: 0; transition: opacity 0.2s ease; pointer-events: none; position: absolute; z-index: 10; background-color: #1f2937; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; white-space: nowrap; bottom: 100%; left: 50%; transform: translateX(-50%) translateY(-5px);}
        .has-tooltip:hover .tooltip { opacity: 1; }
        .spinner { display: inline-block; border: 3px solid rgba(255,255,255,.3); border-left-color: #fff; border-radius: 50%; width: 1rem; height: 1rem; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner.hidden { display: none; }
        button:disabled, a.disabled { opacity: 0.6; cursor: not-allowed; }
        #curriculum-section { scroll-margin-top: 90px; } /* Added scroll margin */
         .mobile-menu a { display: block; padding: 0.75rem 1rem; border-bottom: 1px solid #374151; }
         .mobile-menu a:last-child { border-bottom: none; }
        /* Dropdown styles */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; right: 0; background-color: #1F2937; min-width: 280px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 100; border-radius: 0.5rem; border: 1px solid #374151; overflow: hidden; margin-top: 8px; }
        .dropdown-content.show { display: block; animation: fadeIn 0.2s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .user-dashboard { display: none; position: absolute; right: 0; top: 100%; margin-top: 10px; background-color: #1F2937; width: 300px; border-radius: 0.5rem; border: 1px solid #374151; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); z-index: 100; overflow: hidden; }
        .user-dashboard.show { display: block; animation: fadeIn 0.2s; }
        .user-header { padding: 16px; border-bottom: 1px solid #374151; display: flex; align-items: center; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; margin-right: 12px; }
        .user-name { font-weight: 600; margin-bottom: 2px; }
        .user-email { font-size: 12px; color: #9CA3AF; }
        .user-menu-item { padding: 12px 16px; display: flex; align-items: center; transition: background-color 0.2s; }
        .user-menu-item:hover { background-color: #374151; }
        .user-menu-item i { margin-right: 12px; width: 20px; text-align: center; }
        .user-footer { padding: 12px 16px; border-top: 1px solid #374151; text-align: center; }
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
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i> <!-- Template's purple -->
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
                                <button onclick="toggleDropdown('user-dashboard')" class="flex items-center space-x-2 hover:bg-gray-700 px-1 py-1 rounded-full transition relative">
                                    <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" class="w-8 h-8 rounded-full">
                                    <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-secondary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-gray-800">0</span>
                                </button>
                                <!-- User Dashboard Dropdown -->
                                <div id="user-dashboard" class="user-dashboard dropdown-content">
                                   <div class="user-header">
                                       <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" class="user-avatar">
                                       <div>
                                           <div class="user-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></div>
                                           <div class="user-email"><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'email@example.com'; ?></div>
                                       </div>
                                   </div>
                                   <div>
                                       <a href="Dashboard.php" class="user-menu-item"> <i class="fas fa-tachometer-alt text-gray-400"></i> Dashboard </a>
                                       <a href="#" class="user-menu-item"> <i class="fas fa-user text-gray-400"></i> My Profile </a>
                                       <a href="#" class="user-menu-item"> <i class="fas fa-cog text-gray-400"></i> Settings </a>
                                   </div>
                                   <div class="user-footer">
                                       <a href="logout.php" class="text-sm text-red-500 hover:text-red-400">Sign Out</a>
                                   </div>
                               </div>
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
         <!-- Mobile Menu Content -->
         <div class="mobile-menu hidden md:hidden bg-gray-800 border-t border-gray-700">
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
    </nav>

    <!-- Hero Section -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Course Content -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                     <span class="inline-block bg-green-500 text-black px-2 py-1 rounded-md text-xs font-bold">BESTSELLER</span>
                     <!-- Dynamic Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mt-3"><?php safe_echo($course_title_display); ?></h1>
                    <p class="text-gray-400 mt-2 text-lg">Master HTML, CSS, JavaScript, React, Node.js, and More | Build Real-World Projects</p>

                     <!-- Ratings/Info (Original) -->
                     <div class="flex flex-wrap items-center mt-4 gap-2"> <div class="flex items-center"> <div class="flex items-center text-yellow-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-1 text-sm">4.7 (52,000 ratings)</span></div><span class="text-gray-400 text-sm">•</span><span class="text-sm">300,000+ learners</span><span class="text-gray-400 text-sm">•</span><span class="text-sm">Last updated 4/2024</span></div>
                     <div class="flex items-center mt-3"> <span class="text-sm">Created by </span> <a href="#" class="text-secondary-400 hover:underline ml-1 text-sm">John Doe</a><span class="text-sm mx-1">,</span> <a href="#" class="text-secondary-400 hover:underline text-sm">Jane Smith</a> </div>
                     <div class="mt-6"> <div class="flex items-center"><i class="fas fa-globe text-gray-400 mr-2"></i><span class="text-sm">English</span></div><div class="flex items-center mt-2"><i class="fas fa-closed-captioning text-gray-400 mr-2"></i><span class="text-sm">English [Auto], French, Spanish</span></div></div>
                </div>

                <!-- Course Tabs (Original) -->
                <div class="mt-6">
                    <div class="border-b border-gray-700">
                         <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                             <a href="#" class="border-b-2 border-primary-600 text-primary-400 whitespace-nowrap py-4 px-1 font-medium text-sm" aria-current="page">Overview</a>
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
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Build responsive websites using HTML, CSS, and JavaScript</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Develop dynamic web applications with React and Node.js</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Master backend development with Express and MongoDB</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Deploy websites and apps to the cloud</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Learn best practices for web development</span></div>
                         <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Build a professional portfolio of real-world projects</span></div>
                     </div>
                </div>

                <!-- Course Content Accordion (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl shadow-lg overflow-hidden" id="curriculum-section">
                     <h2 class="text-2xl font-bold p-6">Course content</h2>
                     <div class="divide-y divide-gray-700">
                          <!-- Module 1 --> <div class="accordion-item"> <input type="checkbox" id="module1" class="accordion-toggle hidden"> <label for="module1" class="flex justify-between items-center p-4 hover:bg-gray-700 cursor-pointer"> <div class="flex items-center"> <i class="fas fa-chevron-down accordion-icon transform transition-transform mr-3 text-sm"></i> <span class="font-medium text-sm">Module 1: HTML & CSS Fundamentals</span> </div><span class="text-xs text-gray-400">6 lectures • 2h 45m</span></label> <div class="accordion-content"> <div class="pl-12 pr-4 pb-4 text-sm"> <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Introduction to HTML</span></div><span class="text-xs text-gray-400">22m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>CSS Basics and Selectors</span></div><span class="text-xs text-gray-400">35m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Layouts with Flexbox and Grid</span></div><span class="text-xs text-gray-400">45m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Responsive Design Principles</span></div><span class="text-xs text-gray-400">30m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>CSS Animations and Transitions</span></div><span class="text-xs text-gray-400">25m</span></div><div class="flex justify-between items-center py-2"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Project: Build a Portfolio Page</span></div><span class="text-xs text-gray-400">1h 8m</span></div></div></div></div>
                          <!-- Module 2 --> <div class="accordion-item"> <input type="checkbox" id="module2" class="accordion-toggle hidden"> <label for="module2" class="flex justify-between items-center p-4 hover:bg-gray-700 cursor-pointer"> <div class="flex items-center"> <i class="fas fa-chevron-down accordion-icon transform transition-transform mr-3 text-sm"></i> <span class="font-medium text-sm">Module 2: JavaScript Mastery</span> </div><span class="text-xs text-gray-400">8 lectures • 4h 15m</span></label> <div class="accordion-content"> <div class="pl-12 pr-4 pb-4 text-sm"> <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>JavaScript Fundamentals</span></div><span class="text-xs text-gray-400">45m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>DOM Manipulation</span></div><span class="text-xs text-gray-400">50m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>ES6+ Features</span></div><span class="text-xs text-gray-400">40m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Async JavaScript</span></div><span class="text-xs text-gray-400">35m</span></div><div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Error Handling</span></div><span class="text-xs text-gray-400">25m</span></div><div class="flex justify-between items-center py-2"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Project: Interactive Web App</span></div><span class="text-xs text-gray-400">1h 20m</span></div></div></div></div>
                          <!-- Add more modules as needed -->
                     </div>
                     <div class="p-4 text-center border-t border-gray-700"> <button class="text-primary-600 hover:underline text-sm">Show all 12 modules</button> </div> <!-- Use template purple -->
                </div>

                <!-- Requirements (Original) -->
                 <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                       <h2 class="text-2xl font-bold">Requirements</h2>
                       <ul class="list-disc list-inside text-gray-300 mt-4 space-y-2 text-sm">
                           <li>No prior programming experience required - we'll teach you everything you need to know</li>
                           <li>A computer with internet access (Windows, Mac, or Linux)</li>
                           <li>Any modern web browser (Chrome, Firefox, Safari, Edge)</li>
                           <li>Willingness to learn and practice coding regularly</li>
                       </ul>
                 </div>

                <!-- Description (Original) -->
                 <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                       <h2 class="text-2xl font-bold">Description</h2>
                       <div class="mt-4 text-gray-300 space-y-4 text-sm">
                           <p>Welcome to <span class="font-semibold text-primary-400">The Complete Web Development Bootcamp 2024</span>...</p> <!-- Use template purple -->
                           <p>Whether you're a complete beginner...</p>
                           <h3 class="text-xl font-semibold mt-4">Why This Course?</h3>
                           <ul class="list-disc list-inside space-y-2 ml-4"> <li><span class="font-medium">Project-based</span>...</li><li><span class="font-medium">Comprehensive</span>...</li><li><span class="font-medium">Up-to-date</span>...</li><li><span class="font-medium">Career-focused</span>...</li></ul>
                           <h3 class="text-xl font-semibold mt-4">What's Included?</h3>
                           <ul class="list-disc list-inside space-y-2 ml-4"> <li>Lifetime access...</li><li>Downloadable resources...</li><li>Certificate of completion</li><li>Access to our exclusive student community</li><li>Regular content updates</li></ul>
                           <p class="mt-4">By the end of this course, you'll have built an impressive portfolio...</p>
                           <div class="bg-gray-700 p-4 rounded-lg mt-6"> <h4 class="font-bold text-lg">30-Day Money-Back Guarantee</h4> <p class="mt-2 text-xs">We're confident you'll love this course...</p></div>
                       </div>
                 </div>

                <!-- Instructor Section (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                        <h2 class="text-2xl font-bold">Instructors</h2>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Instructor 1 --> <div class="flex items-start"> <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Doe" class="w-20 h-20 rounded-full object-cover border-2 border-primary-600"> <div class="ml-4"> <h3 class="text-xl font-bold">John Doe</h3> <p class="text-gray-400 text-sm">Senior Web Developer & Educator</p><div class="flex items-center mt-2"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs">4.7 Instructor Rating</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-user-graduate text-gray-400 mr-2 w-4 text-center"></i><span>250,000+ Students</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>15 Courses</span></div><p class="mt-3 text-gray-300 text-xs">John has over 10 years of experience...</p><div class="flex space-x-3 mt-3"><a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-github"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-linkedin"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fas fa-globe"></i></a></div></div></div>
                            <!-- Instructor 2 --> <div class="flex items-start"> <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Jane Smith" class="w-20 h-20 rounded-full object-cover border-2 border-primary-600"> <div class="ml-4"> <h3 class="text-xl font-bold">Jane Smith</h3> <p class="text-gray-400 text-sm">Frontend Architect & UI Specialist</p><div class="flex items-center mt-2"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span class="ml-2 text-xs">4.9 Instructor Rating</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-user-graduate text-gray-400 mr-2 w-4 text-center"></i><span>180,000+ Students</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>8 Courses</span></div><p class="mt-3 text-gray-300 text-xs">Jane is a frontend architect...</p><div class="flex space-x-3 mt-3"><a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-github"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-linkedin"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fas fa-globe"></i></a></div></div></div>
                        </div>
                </div>

                <!-- Student Feedback (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                      <h2 class="text-2xl font-bold">Student feedback</h2>
                      <div class="mt-4 flex flex-col md:flex-row items-center"> <div class="text-center md:text-left md:mr-8"> <div class="text-5xl font-bold text-primary-400">4.7</div><div class="flex justify-center md:justify-start mt-1"><div class="flex items-center text-yellow-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div></div><div class="text-sm mt-1 text-gray-400">Course Rating</div></div><div class="w-full mt-4 md:mt-0"> <div class="flex items-center text-xs"><span class="w-10">5 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 75%"></div></div><span class="w-10 text-right">75%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">4 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 18%"></div></div><span class="w-10 text-right">18%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">3 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 5%"></div></div><span class="w-10 text-right">5%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">2 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 1%"></div></div><span class="w-10 text-right">1%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">1 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 1%"></div></div><span class="w-10 text-right">1%</span></div></div></div>
                      <div class="mt-8"> <h3 class="text-lg font-semibold">Reviews</h3> <div class="space-y-6 mt-4"> <!-- Review 1 --> <div class="border-b border-gray-700 pb-6"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/women/63.jpg" alt="Sarah K." class="w-10 h-10 rounded-full"> <div class="ml-3"> <div class="font-medium text-sm">Sarah K.</div><div class="flex items-center text-xs text-gray-400"><div class="flex items-center text-yellow-400 mr-1 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span>2 months ago</span></div></div></div><div class="mt-3 text-sm"> <h4 class="font-semibold text-base">Changed my career path!</h4> <p class="text-gray-300 mt-1">This course is absolutely amazing...</p></div></div><!-- Review 2 --> <div class="border-b border-gray-700 pb-6"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/men/82.jpg" alt="Michael T." class="w-10 h-10 rounded-full"> <div class="ml-3"> <div class="font-medium text-sm">Michael T.</div><div class="flex items-center text-xs text-gray-400"><div class="flex items-center text-yellow-400 mr-1 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span>1 month ago</span></div></div></div><div class="mt-3 text-sm"> <h4 class="font-semibold text-base">Best investment I've made</h4> <p class="text-gray-300 mt-1">I've taken several web development courses before...</p></div></div></div> <button class="mt-6 text-primary-400 hover:underline text-sm">See all 12,500 reviews</button> </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-20">
                    <!-- Course Card -->
                    <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-700">
                        <div class="relative">
                             <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="<?php safe_echo($course_title_display); ?>" class="w-full h-48 object-cover">
                             <div class="absolute top-3 left-3 bg-black/70 text-white px-2 py-1 rounded-md text-xs"> <i class="fas fa-play-circle mr-1"></i> <span>Preview available</span> </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between"> <p class="text-2xl font-bold">₹499</p> <div class="text-right"> <p class="text-gray-400 line-through">₹2,499</p> <p class="text-red-500 font-bold">80% off</p> </div></div>
                            <p class="text-red-500 text-sm mt-1 flex items-center"> <i class="fas fa-clock mr-1"></i> <span id="countdown-timer">3 hours left at this price!</span> </p>

                             <!-- === Dynamic Buttons START (in Sidebar) === -->
                            <div class="mt-6 space-y-3" id="enrollment-action-area-sidebar"> <!-- Different ID if needed -->
                                 <?php if ($is_enrolled): ?>
                                     <a href="#curriculum-section" class="w-full flex items-center justify-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-medium text-base shadow-md">
                                         <i class="fas fa-play-circle mr-2"></i>Continue Learning
                                     </a>
                                     <p class="text-center text-sm text-green-400 mt-2">You are enrolled in this course.</p>
                                <?php else: ?>
                                     <!-- Link Enroll button from Hero to here if preferred -->
                                     <!-- OR keep original buy/cart buttons -->
                                     <button class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition font-medium"> Add to cart (Original) </button>
                                     <button class="w-full bg-primary-700 text-white py-3 rounded-lg hover:bg-primary-800 transition font-medium"> Buy now (Original) </button>
                                <?php endif; ?>
                            </div>
                             <!-- === Dynamic Buttons END === -->


                            <p class="text-center text-sm mt-4 text-gray-400">30-Day Money-Back Guarantee</p>

                            <div class="mt-6">
                                <h3 class="font-bold text-sm">This course includes:</h3>
                                <ul class="mt-3 space-y-2 text-sm">
                                     <li class="flex items-center"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>65 hours on-demand video</span></li>
                                     <li class="flex items-center"><i class="fas fa-file-alt text-gray-400 mr-2 w-4 text-center"></i><span>42 articles</span></li>
                                     <li class="flex items-center"><i class="fas fa-download text-gray-400 mr-2 w-4 text-center"></i><span>36 downloadable resources</span></li>
                                     <li class="flex items-center"><i class="fas fa-infinity text-gray-400 mr-2 w-4 text-center"></i><span>Full lifetime access</span></li>
                                     <li class="flex items-center"><i class="fas fa-mobile-alt text-gray-400 mr-2 w-4 text-center"></i><span>Access on mobile and TV</span></li>
                                     <li class="flex items-center"><i class="fas fa-certificate text-gray-400 mr-2 w-4 text-center"></i><span>Certificate of completion</span></li>
                                </ul>
                            </div>

                            <div class="mt-6 border-t border-gray-700 pt-4">
                                <button class="w-full text-center text-primary-400 hover:underline font-medium text-sm">
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
              <!-- Course 1 --> <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1547658719-da2b51169166?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="React Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">The Complete React Developer Course</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs text-gray-400">(12,450)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹499</span><span class="text-xs text-gray-400 line-through">₹2,999</span></div></div></div>
              <!-- Course 2 --> <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Node.js Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">Node.js: The Complete Guide</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span class="ml-2 text-xs text-gray-400">(8,720)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹399</span><span class="text-xs text-gray-400 line-through">₹2,499</span></div></div></div>
              <!-- Course 3 --> <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1626785774573-4b799315345d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="JavaScript Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">Modern JavaScript From Scratch</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs text-gray-400">(15,320)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹299</span><span class="text-xs text-gray-400 line-through">₹1,999</span></div></div></div>
         </div>
    </div>

    <!-- FAQ Section (Original) -->
     <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <h2 class="text-2xl font-bold mb-8">Frequently asked questions</h2>
          <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
              <div class="divide-y divide-gray-700">
                  <!-- FAQ Item 1 --><div class="accordion-item"><input type="checkbox" id="faq1" class="accordion-toggle hidden"><label for="faq1" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">When does the course start and finish?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>The course starts now and never ends!...</p></div></div></div>
                  <!-- FAQ Item 2 --><div class="accordion-item"><input type="checkbox" id="faq2" class="accordion-toggle hidden"><label for="faq2" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">How long do I have access to the course?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>How does lifetime access sound?...</p></div></div></div>
                  <!-- FAQ Item 3 --><div class="accordion-item"><input type="checkbox" id="faq3" class="accordion-toggle hidden"><label for="faq3" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">What if I am unhappy with the course?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>We would never want you to be unhappy!...</p></div></div></div>
                  <!-- FAQ Item 4 --><div class="accordion-item"><input type="checkbox" id="faq4" class="accordion-toggle hidden"><label for="faq4" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">Do I need any prior experience?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>No prior experience is needed!...</p></div></div></div>
              </div>
          </div>
     </div>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12"> <div class="grid grid-cols-1 md:grid-cols-4 gap-8"> <div> <div class="flex items-center"> <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i> <span class="text-xl font-bold">EduPro</span> </div><p class="mt-4 text-gray-400 text-sm">Learn the latest skills to reach your professional goals.</p><div class="flex space-x-4 mt-4"> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-youtube"></i></a> </div></div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Company</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">About</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Careers</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Blog</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Press</a></li></ul> </div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Support</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">Contact Us</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Help Center</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Privacy Policy</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Terms of Service</a></li></ul> </div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Resources</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">Download App</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Documentation</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Guides</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Community</a></li></ul> </div></div><div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center"> <p class="text-gray-400 text-sm">© <?php echo date('Y'); ?> EduPro, Inc. All rights reserved.</p><div class="mt-4 md:mt-0 flex space-x-6"> <a href="#" class="text-gray-400 hover:text-white text-sm">Privacy</a> <a href="#" class="text-gray-400 hover:text-white text-sm">Terms</a> <a href="#" class="text-gray-400 hover:text-white text-sm">Sitemap</a> </div></div></div>
    </footer>

    <script>
        // --- JAVASCRIPT (Combined) ---
        document.addEventListener('DOMContentLoaded', function() {

             // --- Mobile menu toggle ---
             const mobileMenuButton = document.querySelector('.mobile-menu-button');
             const mobileMenu = document.querySelector('.mobile-menu');
             if (mobileMenuButton && mobileMenu) { /* ... Same logic ... */ mobileMenuButton.addEventListener('click', function() { mobileMenu.classList.toggle('hidden'); const icon = mobileMenuButton.querySelector('i'); if (icon) { icon.classList.toggle('fa-bars'); icon.classList.toggle('fa-times'); } }); }

             // --- Dropdown Toggles ---
              window.toggleDropdown = function(dropdownId) { /* ... Same logic ... */ const dropdown = document.getElementById(dropdownId); if (dropdown) { const otherDropdowns = document.querySelectorAll('.dropdown-content, .user-dashboard'); otherDropdowns.forEach(od => { if (od.id !== dropdownId) { od.classList.remove('show'); } }); dropdown.classList.toggle('show'); } if (dropdownId === 'notification-dropdown' && dropdown.classList.contains('show')) { const badge = document.getElementById('notification-badge'); if(badge) badge.classList.add('hidden'); } }
              window.onclick = function(event) { /* ... Same logic ... */ if (!event.target.closest('.dropdown') && !event.target.closest('.relative > button')) { const dropdowns = document.querySelectorAll('.dropdown-content, .user-dashboard'); dropdowns.forEach(dropdown => { dropdown.classList.remove('show'); }); } }


             // --- Accordion functionality ---
             document.querySelectorAll('.accordion-toggle').forEach(toggle => { /* ... Same logic ... */ toggle.addEventListener('change', function() { const icon = this.closest('.accordion-item').querySelector('.accordion-icon'); if (icon) { icon.classList.toggle('rotate-180', this.checked); } }); });

             // --- Countdown timer ---
             const countdownElement = document.getElementById('countdown-timer');
             if (countdownElement) { function updateCountdown() { /* ... Same logic ... */ const now = new Date(); const endTime = new Date(now.getTime() + 3 * 60 * 60 * 1000); const timer = setInterval(function() { const now = new Date(); const distance = endTime - now; if (distance < 0) { clearInterval(timer); if(countdownElement) countdownElement.textContent = 'Discount expired!'; return; } const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)); const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)); const seconds = Math.floor((distance % (1000 * 60)) / 1000); if(countdownElement) { countdownElement.textContent = `${hours}h ${minutes}m ${seconds}s left at this price!`; } else { clearInterval(timer); } }, 1000); } updateCountdown(); }

             // --- Smooth scrolling ---
             document.querySelectorAll('a[href^="#"]').forEach(anchor => { /* ... Same logic ... */ anchor.addEventListener('click', function(e) { const targetId = this.getAttribute('href'); const targetElement = document.querySelector(targetId); if (targetElement) { e.preventDefault(); targetElement.scrollIntoView({ behavior: 'smooth' }); } }); });

            // --- Progress & Enrollment Logic ---
             const checkboxes = document.querySelectorAll('.lesson-checkbox'); // Add these to HTML
             const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 7; // Adjusted fallback (3+4)

             function updateProgress() { /* ... Same logic ... */ const progressBar = document.getElementById('progress-bar'); const progressText = document.getElementById('progress-text'); if (!progressBar || !progressText) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; const progress = totalLessonsForProgress > 0 ? Math.round((completed / totalLessonsForProgress) * 100) : 0; progressBar.style.width = progress + '%'; progressText.textContent = `${completed}/${totalLessonsForProgress} lessons completed`; progressBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-primary-600'); if (progress < 30) { progressBar.classList.add('bg-red-500'); } else if (progress < 70) { progressBar.classList.add('bg-yellow-500'); } else if (progress >= 100) { progressBar.classList.add('bg-green-500'); } else { progressBar.classList.add('bg-primary-600'); } }
             function updateCompletionBadge() { /* ... Same logic ... */ const completionBadge = document.getElementById('completion-badge'); if (!completionBadge) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; if (completed > 0) { completionBadge.textContent = completed; completionBadge.classList.remove('hidden'); } else { completionBadge.classList.add('hidden'); } }
             checkboxes.forEach(checkbox => { /* ... Same logic ... */ const lessonId = checkbox.id; const storageKey = lessonId + '_<?php safe_echo($course_slug); ?>'; const savedState = localStorage.getItem(storageKey); checkbox.checked = (savedState === 'true'); updateCheckmarkVisual(checkbox); checkbox.addEventListener('change', function() { localStorage.setItem(storageKey, this.checked); updateCheckmarkVisual(this); updateProgress(); updateCompletionBadge(); }); });
             updateProgress(); updateCompletionBadge();

              // Helper for checkbox visual state
             function updateCheckmarkVisual(checkbox) { const label = checkbox.closest('.flex').querySelector('label'); const checkIcon = label ? label.querySelector('.fa-check') : null; if (checkIcon) { checkIcon.style.display = checkbox.checked ? 'inline-block' : 'none'; } }


            // --- Enrollment Button Logic ---
            const enrollButton = document.getElementById('enroll-btn');
            const enrollmentActionArea = document.getElementById('enrollment-action-area');
            const isLoggedIn = <?php echo json_encode($user_logged_in); ?>;
            const isPaidCourse = <?php echo json_encode($is_paid_course); ?>;

            if (enrollButton && enrollmentActionArea) { /* ... Same enrollment fetch logic ... */
                const enrollButtonText = enrollButton.querySelector('.btn-text'); const enrollButtonSpinner = enrollButton.querySelector('.spinner');
                enrollButton.addEventListener('click', function() { if (!isLoggedIn) { alert('Please log in or sign up to enroll.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); return; } if (isPaidCourse) { console.log("Paid course - Add payment flow here."); } const courseSlug = this.dataset.courseSlug; const button = this; button.disabled = true; if (enrollButtonText) enrollButtonText.textContent = 'Enrolling...'; else button.innerHTML = 'Enrolling...<span class="spinner ml-2"></span>'; if (enrollButtonSpinner) enrollButtonSpinner.classList.remove('hidden'); button.classList.add('opacity-60', 'cursor-wait'); const formData = new FormData(); formData.append('course_slug', courseSlug);
                fetch('enroll_course.php', { method: 'POST', body: formData })
                .then(response => { if (!response.ok) { return response.text().then(text => { throw new Error(`Server responded with status ${response.status}. Response: ${text || '(empty)'}`); }); } const contentType = response.headers.get("content-type"); if (contentType && contentType.indexOf("application/json") !== -1) { return response.json(); } else { return response.text().then(text => { throw new Error(`Unexpected response format. Expected JSON, got: ${text || '(empty)'}`); }); } })
                .then(data => { if (data.success) { console.log("Enrollment successful:", data.message); enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md"><i class="fas fa-play mr-2"></i>Continue Learning</a> <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled><i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span></button>`; } else { if (data.action === 'redirect_login') { alert('Session issue. Please log in again.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); } else if (data.message && data.message.includes('already enrolled')) { console.warn("Already enrolled message:", data.message); enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md"><i class="fas fa-play mr-2"></i>Continue Learning</a> <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled><i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span></button>`; } else { alert('Enrollment failed: ' + (data.message || 'Unknown error.')); button.disabled = false; if (enrollButtonText) enrollButtonText.innerHTML = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); else button.innerHTML = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); } } })
                .catch(error => { console.error('Enrollment fetch error:', error); alert('An error occurred during enrollment. Please try again.\nError: ' + error.message); button.disabled = false; if (enrollButtonText) enrollButtonText.innerHTML = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); else button.innerHTML = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); }); });
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