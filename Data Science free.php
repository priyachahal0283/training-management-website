<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require database configuration (this sets $mysqli)
require_once 'config.php'; // <--- Sets up $mysqli

// --- Course Specific Variables ---
// *** IMPORTANT: Unique Slug for this course ***
$course_slug = 'data-science-complete'; // Example unique slug
$course_title_display = 'The Complete Data Science Course'; // Title from the page
$is_paid_course = true; // Assume paid

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind configuration (Original)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af'
                        },
                        secondary: {
                            400: '#60a5fa',
                            500: '#3b82f6'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom CSS styles (Original + Additions) */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .course-card { transition: all 0.3s ease; border: 1px solid rgba(255, 255, 255, 0.1); }
        .course-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2); border-color: rgba(59, 130, 246, 0.5); }
        .lesson-checkbox:checked + label { text-decoration: line-through; color: #9CA3AF; }
        .lesson-checkbox:checked + label .fa-check { display: inline-block !important; }
        .progress-bar { transition: width 0.5s ease; }
        .tooltip { opacity: 0; transition: opacity 0.2s ease; pointer-events: none; position: absolute; z-index: 10;}
        .has-tooltip:hover .tooltip { opacity: 1; }
        .spinner { display: inline-block; border: 3px solid rgba(255,255,255,.3); border-left-color: #fff; border-radius: 50%; width: 1rem; height: 1rem; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        button:disabled, a.disabled { opacity: 0.6; cursor: not-allowed; }
        #curriculum-section { scroll-margin-top: 90px; } /* Added scroll margin */
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                 <!-- Left Side Nav -->
                <div class="flex items-center space-x-4">
                    <a href="Home.php" class="flex items-center"> <!-- Link to Home -->
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl"></i>
                        <span class="ml-2 text-xl font-bold">EduPro</span>
                    </a>
                    <div class="hidden md:flex space-x-6">
                        <a href="courses_list.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Courses</a> <!-- Link to Courses List -->
                        
                        <a href="resources.html" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Resources</a>
                    </div>
                </div>
                 <!-- Right Side Nav -->
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip">
                        <i class="fas fa-search text-gray-400"></i>
                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap"> Search courses </span>
                    </button>
                    <button class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip">
                        <i class="fas fa-bell text-gray-400"></i>
                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap"> Notifications </span>
                    </button>

                    <!-- === LOGIN/SIGNUP/PROFILE AREA START === -->
                    <div class="relative">
                         <?php if ($user_logged_in): ?>
                            <a href="Dashboard.php" class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-2 rounded-full transition" title="Go to Dashboard">
                                 <span class="inline-block h-8 w-8 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center">
                                      <i class="fas fa-user text-gray-300"></i>
                                 </span>
                                <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                            </a>
                         <?php else: ?>
                            <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-2 rounded-full transition" title="Login">
                                  <span class="inline-block h-8 w-8 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center">
                                      <i class="fas fa-user text-gray-300"></i>
                                  </span>
                            </a>
                         <?php endif; ?>
                    </div>
                    <!-- === LOGIN/SIGNUP/PROFILE AREA END === -->

                </div>
                 <!-- Mobile Menu Button -->
                  <div class="md:hidden">
                     <button class="mobile-menu-button p-2 rounded-md hover:bg-gray-700 focus:outline-none">
                         <i class="fas fa-bars"></i>
                     </button>
                 </div>
            </div>
             <!-- Mobile Menu -->
             <div class="mobile-menu hidden md:hidden bg-gray-800">
                  <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                      <a href="courses_list.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700">Courses</a>
                    
                      <a href="resources.html" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700">Resources</a>
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
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Course Header -->
        <div class="bg-gray-800 rounded-xl p-8 mb-8 border border-gray-700">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="md:w-2/3">
                    <span class="inline-block bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-semibold mb-4">
                        PROFESSIONAL CERTIFICATE
                    </span>
                    <!-- Dynamic Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mb-4"><?php safe_echo($course_title_display); ?></h1>
                    <p class="text-lg text-gray-300 mb-6">Master data science from scratch with Python, statistics, machine learning, and real-world projects. Become job-ready with this comprehensive program.</p>

                    <!-- Ratings/Info (Original) -->
                    <div class="flex flex-wrap items-center gap-4 mb-6"> <div class="flex items-center"> <div class="flex items-center text-yellow-400 mr-1"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span class="text-sm">4.9 (3,850 ratings)</span></div><span class="text-gray-400">•</span><span class="text-sm">78,000+ students enrolled</span><span class="text-gray-400">•</span><span class="text-sm">Updated April 2024</span></div>

                    <!-- === Enrollment Action Area START === -->
                    <div class="flex items-center space-x-4" id="enrollment-action-area">
                         <?php if ($is_enrolled): ?>
                            <a href="#curriculum-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md">
                                <i class="fas fa-play-circle mr-2"></i>Continue Learning
                            </a>
                            <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled>
                                <i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled
                                <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span>
                            </button>
                       <?php else: ?>
                            <button id="enroll-btn" data-course-slug="<?php safe_echo($course_slug); ?>" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md">
                                <span class="btn-text">Enroll Now<?php echo $is_paid_course ? '' : ' - Free'; ?></span>
                                <span class="spinner hidden ml-2"></span>
                            </button>
                            <button class="flex items-center text-gray-300 hover:text-white transition group has-tooltip">
                                <i class="far fa-heart mr-2"></i> Save
                                <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Save (coming soon)</span>
                            </button>
                       <?php endif; ?>
                    </div>
                    <?php if (!$is_enrolled && !$user_logged_in): ?>
                        <p class="text-xs text-gray-400 mt-3">
                           <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-600 hover:underline">Log in</a> or
                           <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-600 hover:underline">sign up</a>
                           to enroll<?php echo $is_paid_course ? ' in this course' : ' in this free course'; ?>.
                        </p>
                    <?php endif; ?>
                     <!-- === Enrollment Action Area END === -->
                </div>
                <!-- Course Image (Original) -->
                <div class="hidden md:block md:w-1/3 mt-6 md:mt-0"> <!-- Added Spacing -->
                    <div class="relative overflow-hidden rounded-lg">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="<?php safe_echo($course_title_display); ?>"
                             class="w-full h-64 object-cover"> <!-- Adjusted height -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-4">
                            <button class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full hover:bg-white/30 transition">
                                <a href="https://www.youtube.com/watch?v=VGhZ4f2UggM&t=10s"><i class="fas fa-play text-white"></i></a>
                            </button>
                            <span class="ml-3 text-white font-medium">Course Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Section (Original) -->
        <div class="bg-gray-800 rounded-xl p-6 mb-8 border border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold flex items-center">
                    <i class="fas fa-chart-line text-primary-600 mr-3"></i>
                    Your Learning Progress
                </h2>
                <span id="progress-text" class="text-sm text-gray-300">0/20 lessons completed</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2.5 mb-2">
                <div id="progress-bar" class="bg-primary-600 h-2.5 rounded-full progress-bar" style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400">
                <span>Beginner</span>
                <span>Intermediate</span>
                <span>Advanced</span>
            </div>
        </div>

        <!-- Course Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8" id="curriculum-section"> <!-- Added ID -->
                    <h2 class="text-2xl font-bold mb-6">Course Curriculum</h2>

                    <!-- Module 1 (Original) -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-primary-500">Module 1: Python for Data Science</h3> <span class="text-sm text-gray-400">4 lessons</span> </div>
                        <div class="space-y-3">
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-1-ds" class="lesson-checkbox hidden"> <label for="lesson-1-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Python Fundamentals for Data Analysis</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">35 min</span> <a href="https://www.youtube.com/watch?v=mut8eTdoRxU" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-2-ds" class="lesson-checkbox hidden"> <label for="lesson-2-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>NumPy and Pandas Mastery</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">45 min</span> <a href="https://www.youtube.com/watch?v=r67SfaiYaDI" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-3-ds" class="lesson-checkbox hidden"> <label for="lesson-3-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Data Visualization with Matplotlib & Seaborn</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">40 min</span> <a href="https://www.youtube.com/watch?v=FN78JowwpSY" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-3b-ds" class="lesson-checkbox hidden"> <label for="lesson-3b-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Working with APIs & Web Scraping</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">50 min</span> <a href="https://www.youtube.com/watch?v=roTZJaxjnJc" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                        </div>
                    </div>

                    <!-- Module 2 (Original) -->
                    <div class="mb-8">
                         <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-purple-500">Module 2: Statistics for Data Science</h3> <span class="text-sm text-gray-400">5 lessons</span> </div>
                         <div class="space-y-3">
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-4-ds" class="lesson-checkbox hidden"> <label for="lesson-4-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Descriptive Statistics and Probability</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">50 min</span> <a href="https://www.youtube.com/watch?v=FzujIYo9GYo" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-5-ds" class="lesson-checkbox hidden"> <label for="lesson-5-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Inferential Statistics and Hypothesis Testing</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">55 min</span> <a href="https://www.youtube.com/watch?v=-FtlH4svqx4" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-5b-ds" class="lesson-checkbox hidden"> <label for="lesson-5b-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Regression Analysis</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">40 min</span> <a href="https://www.youtube.com/watch?v=WWqE7YHR4Jc" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-5c-ds" class="lesson-checkbox hidden"> <label for="lesson-5c-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Bayesian Statistics Introduction</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">35 min</span> <a href="https://www.youtube.com/watch?v=9TDjifpGj-k" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-5d-ds" class="lesson-checkbox hidden"> <label for="lesson-5d-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Statistical Experiments (A/B Testing)</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">40 min</span> <a href="https://www.youtube.com/watch?v=DUNk4GPZ9bw" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                         </div>
                    </div>

                    <!-- Module 3 (Original) -->
                    <div class="mb-8">
                         <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-green-500">Module 3: Machine Learning</h3> <span class="text-sm text-gray-400">6 lessons</span> </div>
                         <div class="space-y-3">
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-6-ds" class="lesson-checkbox hidden"> <label for="lesson-6-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Supervised Learning Algorithms</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">60 min</span> <a href="https://www.youtube.com/watch?v=E0Hmnixke2g" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-7-ds" class="lesson-checkbox hidden"> <label for="lesson-7-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Model Evaluation and Validation</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">45 min</span> <a href="https://www.youtube.com/watch?v=v6DtYYafrWQ" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-7b-ds" class="lesson-checkbox hidden"> <label for="lesson-7b-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Unsupervised Learning (Clustering)</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">50 min</span> <a href="https://www.youtube.com/watch?v=5FpsGnkbEpM" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-7c-ds" class="lesson-checkbox hidden"> <label for="lesson-7c-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Dimensionality Reduction (PCA)</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">40 min</span> <a href="https://www.youtube.com/watch?v=Dv-Kk7PDEas" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-7d-ds" class="lesson-checkbox hidden"> <label for="lesson-7d-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Introduction to Deep Learning</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">55 min</span> <a href="https://www.youtube.com/watch?v=ajeTYqhRHno" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                              <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"> <input type="checkbox" id="lesson-7e-ds" class="lesson-checkbox hidden"> <label for="lesson-7e-ds" class="flex items-center cursor-pointer"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Feature Engineering Techniques</span> </label> </div><div class="flex items-center space-x-3"> <span class="text-sm text-gray-400">45 min</span> <a href="https://www.youtube.com/watch?v=vZDDmULsCUU" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start Lesson</span></a> </div></div>
                         </div>
                    </div>

                    <div class="text-center mt-8">
                        <button class="text-primary-600 hover:text-primary-500 font-medium"> Show all 7 modules <i class="fas fa-chevron-down ml-1 text-xs"></i> </button>
                    </div>
                </div>

                <!-- Instructor Section (Original) -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                     <h2 class="text-2xl font-bold mb-6">About the Instructor</h2>
                     <div class="flex flex-col md:flex-row items-start"> <img src="https://randomuser.me/api/portraits/women/65.jpg" class="w-24 h-24 rounded-full object-cover mb-4 md:mb-0 md:mr-6 border-4 border-primary-600/30"> <div> <h3 class="text-xl font-bold mb-1">Dr. Emily Rodriguez</h3> <p class="text-primary-500 mb-3">Lead Data Scientist | EduPro Instructor</p><p class="text-gray-300 mb-4 text-sm"> Former Data Science Lead at Amazon with a PhD in Applied Statistics from MIT. Dr. Rodriguez has 10+ years of experience building data products... </p><div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-400"> <div class="flex items-center"><i class="fas fa-star text-yellow-400 mr-1"></i><span>4.9 Instructor Rating</span></div><div class="flex items-center"><i class="fas fa-user-graduate mr-1"></i><span>25,300 Students</span></div><div class="flex items-center"><i class="fas fa-play-circle mr-1"></i><span>7 Courses</span></div></div></div></div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-20"> <!-- Adjusted top sticky value -->
                    <!-- Course Features -->
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
                         <h3 class="text-lg font-bold mb-4">This Course Includes:</h3>
                         <ul class="space-y-3 text-sm">
                             <li class="flex items-center"><i class="fas fa-video text-primary-600 mr-3 w-5 text-center"></i><span>65 hours on-demand video</span></li>
                             <li class="flex items-center"><i class="fas fa-file-alt text-primary-600 mr-3 w-5 text-center"></i><span>50 downloadable resources</span></li>
                             <li class="flex items-center"><i class="fas fa-laptop-code text-primary-600 mr-3 w-5 text-center"></i><span>30 coding exercises</span></li>
                             <li class="flex items-center"><i class="fas fa-project-diagram text-primary-600 mr-3 w-5 text-center"></i><span>5 real-world projects</span></li>
                             <li class="flex items-center"><i class="fas fa-certificate text-primary-600 mr-3 w-5 text-center"></i><span>Certificate of completion</span></li>
                              <li class="flex items-center"><i class="fas fa-infinity text-primary-600 mr-3 w-5 text-center"></i><span>Full lifetime access</span></li>
                         </ul>
                    </div>

                    <!-- Resources (Original) -->
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
                         <h3 class="text-lg font-bold mb-4">Resources</h3>
                         <div class="space-y-3">
                             <a href="https://jakevdp.github.io/PythonDataScienceHandbook/" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition"> <div class="bg-primary-600/20 text-primary-600 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-file-pdf"></i></div><div><h4 class="font-medium text-sm">Data Science Handbook</h4><p class="text-xs text-gray-400">PDF • 3.1MB</p></div></a>
                             <a href="https://jakevdp.github.io/PythonDataScienceHandbook/" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition"> <div class="bg-purple-600/20 text-purple-500 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-database"></i></div><div><h4 class="font-medium text-sm">Practice Datasets</h4><p class="text-xs text-gray-400">ZIP • 28.5MB</p></div></a>
                             <a href="https://chat.whatsapp.com/CqW46y9Fkgh6RT09xO8ylV" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition"> <div class="bg-green-600/20 text-green-500 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-users"></i></div><div><h4 class="font-medium text-sm">Data Science Community</h4><p class="text-xs text-gray-400">Slack • 35K+ members</p></div></a>
                         </div>
                    </div>

                    <!-- Related Courses (Original) -->
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                         <h3 class="text-lg font-bold mb-4">Students Also Viewed</h3>
                         <div class="space-y-4">
                             <a href="AI&Machine Learning.php" class="flex items-center hover:bg-gray-700 p-3 -m-3 rounded-lg transition"> <img src="https://images.unsplash.com/photo-1620712943543-bcc4688e7485?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" class="w-16 h-12 rounded-lg object-cover mr-3 flex-shrink-0"> <div><h4 class="font-medium text-sm leading-tight">AI & Machine Learning Bootcamp</h4><div class="flex items-center text-xs text-gray-400 mt-1"><i class="fas fa-star text-yellow-400 mr-1 text-xs"></i><span>4.8 • 15.2k students</span></div></div></a>
                             <a href="digital marketing.php" class="flex items-center hover:bg-gray-700 p-3 -m-3 rounded-lg transition"> <img src="https://images.unsplash.com/photo-1624953587687-daf255b6b80a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" class="w-16 h-12 rounded-lg object-cover mr-3 flex-shrink-0"> <div><h4 class="font-medium text-sm leading-tight">Digital Marketing</h4><div class="flex items-center text-xs text-gray-400 mt-1"><i class="fas fa-star text-yellow-400 mr-1 text-xs"></i><span>4.7 • 12.5k students</span></div></div></a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 py-12">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"> <div class="grid grid-cols-1 md:grid-cols-4 gap-8"> <div> <div class="flex items-center mb-4"> <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i> <span class="text-xl font-bold">EduPro</span> </div><p class="text-gray-400 mb-4 text-sm">Advancing careers through world-class digital education.</p><div class="flex space-x-4"> <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a> <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a> <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-youtube"></i></a> </div></div><div> <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Company</h3> <ul class="space-y-2"> <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">About Us</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Careers</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Blog</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Press</a></li></ul> </div><div> <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3> <ul class="space-y-2"> <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Help Center</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Contact Us</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Feedback</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Accessibility</a></li></ul> </div><div> <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3> <ul class="space-y-2"> <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Terms of Service</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Privacy Policy</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Cookie Policy</a></li><li><a href="#" class="text-sm text-gray-400 hover:text-white transition">GDPR</a></li></ul> </div></div><div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center"> <p class="text-gray-500 text-sm mb-4 md:mb-0">© <?php echo date('Y'); ?> EduPro, Inc. All rights reserved.</p><div class="flex space-x-6"> <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Sitemap</a> <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Trademark</a> <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Policies</a> </div></div></div>
    </footer>

    <script>
        // --- JAVASCRIPT (Combined) ---
        document.addEventListener('DOMContentLoaded', function() {

             // --- Mobile menu toggle ---
             const mobileMenuButton = document.querySelector('.mobile-menu-button');
             const mobileMenu = document.querySelector('.mobile-menu');
             if (mobileMenuButton && mobileMenu) { mobileMenuButton.addEventListener('click', function() { mobileMenu.classList.toggle('hidden'); const icon = mobileMenuButton.querySelector('i'); if (icon) { icon.classList.toggle('fa-bars'); icon.classList.toggle('fa-times'); } }); }

             // --- Accordion functionality (if needed for curriculum) ---
             // Assuming accordion structure might be added later
             document.querySelectorAll('.accordion-toggle').forEach(toggle => { toggle.addEventListener('change', function() { const icon = this.closest('.accordion-item').querySelector('.accordion-icon'); if (icon) { icon.classList.toggle('rotate-180', this.checked); } }); });

             // --- Smooth scrolling (if needed) ---
             document.querySelectorAll('a[href^="#"]').forEach(anchor => { anchor.addEventListener('click', function(e) { const targetId = this.getAttribute('href'); const targetElement = document.querySelector(targetId); if (targetElement) { e.preventDefault(); targetElement.scrollIntoView({ behavior: 'smooth' }); } }); });


            // --- Progress & Enrollment Logic ---
             const checkboxes = document.querySelectorAll('.lesson-checkbox'); // Add these to your HTML
             const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 20; // Adjust fallback total

             function updateProgress() { /* ... Same progress logic ... */ const progressBar = document.getElementById('progress-bar'); const progressText = document.getElementById('progress-text'); if (!progressBar || !progressText) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; const progress = totalLessonsForProgress > 0 ? Math.round((completed / totalLessonsForProgress) * 100) : 0; progressBar.style.width = progress + '%'; progressText.textContent = `${completed}/${totalLessonsForProgress} lessons completed`; progressBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-primary-600'); if (progress < 30) { progressBar.classList.add('bg-red-500'); } else if (progress < 70) { progressBar.classList.add('bg-yellow-500'); } else if (progress >= 100) { progressBar.classList.add('bg-green-500'); } else { progressBar.classList.add('bg-primary-600'); } }
             function updateCompletionBadge() { /* ... Same badge logic ... */ const completionBadge = document.getElementById('completion-badge'); if (!completionBadge) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; if (completed > 0) { completionBadge.textContent = completed; completionBadge.classList.remove('hidden'); } else { completionBadge.classList.add('hidden'); } }
             checkboxes.forEach(checkbox => { /* ... Same checkbox logic ... */ const lessonId = checkbox.id; const storageKey = lessonId + '_<?php safe_echo($course_slug); ?>'; const savedState = localStorage.getItem(storageKey); checkbox.checked = (savedState === 'true'); const label = checkbox.nextElementSibling; const checkIcon = label ? label.querySelector('.fa-check') : null; if (checkbox.checked) { if (checkIcon) checkIcon.classList.remove('hidden'); if (label) label.classList.add('line-through', 'text-gray-500'); } else { if (checkIcon) checkIcon.classList.add('hidden'); if (label) label.classList.remove('line-through', 'text-gray-500'); } checkbox.addEventListener('change', function() { localStorage.setItem(storageKey, this.checked); const label = this.nextElementSibling; const checkIcon = label ? label.querySelector('.fa-check') : null; if (this.checked) { if (checkIcon) checkIcon.classList.remove('hidden'); if (label) label.classList.add('line-through', 'text-gray-500'); } else { if (checkIcon) checkIcon.classList.add('hidden'); if (label) label.classList.remove('line-through', 'text-gray-500'); } updateProgress(); updateCompletionBadge(); }); });
             updateProgress(); updateCompletionBadge();


            // --- Enrollment Button Logic ---
            const enrollButton = document.getElementById('enroll-btn'); // ID must be on the button
            const enrollmentActionArea = document.getElementById('enrollment-action-area'); // ID must be on the container div
            const isLoggedIn = <?php echo json_encode($user_logged_in); ?>;
            const isPaidCourse = <?php echo json_encode($is_paid_course); ?>;

            if (enrollButton && enrollmentActionArea) { /* ... Same enrollment fetch logic ... */
                const enrollButtonText = enrollButton.querySelector('.btn-text'); const enrollButtonSpinner = enrollButton.querySelector('.spinner');
                enrollButton.addEventListener('click', function() { if (!isLoggedIn) { alert('Please log in or sign up to enroll.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); return; } if (isPaidCourse) { console.log("Paid course - Add payment flow here."); } const courseSlug = this.dataset.courseSlug; const button = this; button.disabled = true; if (enrollButtonText) enrollButtonText.textContent = 'Enrolling...'; else button.textContent = 'Enrolling...'; if (enrollButtonSpinner) enrollButtonSpinner.classList.remove('hidden'); button.classList.add('opacity-60', 'cursor-wait'); const formData = new FormData(); formData.append('course_slug', courseSlug);
                fetch('enroll_course.php', { method: 'POST', body: formData })
                .then(response => { if (!response.ok) { return response.text().then(text => { throw new Error(`Server responded with status ${response.status}. Response: ${text || '(empty)'}`); }); } const contentType = response.headers.get("content-type"); if (contentType && contentType.indexOf("application/json") !== -1) { return response.json(); } else { return response.text().then(text => { throw new Error(`Unexpected response format. Expected JSON, got: ${text || '(empty)'}`); }); } })
                .then(data => { if (data.success) { console.log("Enrollment successful:", data.message); enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md"><i class="fas fa-play-circle mr-2"></i>Continue Learning</a> <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled><i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span></button>`; } else { if (data.action === 'redirect_login') { alert('Session issue. Please log in again.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); } else if (data.message && data.message.includes('already enrolled')) { console.warn("Already enrolled message:", data.message); enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md"><i class="fas fa-play-circle mr-2"></i>Continue Learning</a> <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled><i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span></button>`; } else { alert('Enrollment failed: ' + (data.message || 'Unknown error.')); button.disabled = false; if (enrollButtonText) enrollButtonText.textContent = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); else button.textContent = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); } } })
                .catch(error => { console.error('Enrollment fetch error:', error); alert('An error occurred while enrolling. Please try again.\nError: ' + error.message); button.disabled = false; if (enrollButtonText) enrollButtonText.textContent = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); else button.textContent = 'Enroll Now' + (isPaidCourse ? '' : ' - Free'); if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); }); });
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