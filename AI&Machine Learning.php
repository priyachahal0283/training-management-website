<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require database configuration (this sets $mysqli)
require_once 'config.php'; // <--- Sets up $mysqli

// --- Course Specific Variables ---
// *** IMPORTANT: Unique Slug for this course ***
$course_slug = 'ai-ml-bootcamp-2024'; // Example unique slug
$course_title_display = 'AI & Machine Learning Bootcamp 2024'; // Title from the page
$is_paid_course = true; // Assume this is paid based on price shown

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
        // No need to close $mysqli here
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
    <!-- Use PHP variable for title -->
    <title><?php safe_echo($course_title_display); ?> | EduPro</title>
    <meta name="description" content="Master AI & ML fundamentals, build real-world projects, and launch your career in artificial intelligence. Learn Python, TensorFlow, PyTorch, and more.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Tailwind configuration (Original)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { // Keep original theme for this page
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6'
                        },
                        secondary: {
                            400: '#60a5fa',
                            500: '#3b82f6'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite'
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
        /* Custom CSS styles (Original + Additions) */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .accordion-toggle:checked ~ .accordion-content {
            max-height: 1000px; /* Ensure enough height */
        }
        /* Add styles from previous template for consistency if needed */
        .lesson-checkbox:checked + label {
            text-decoration: line-through;
            color: #9CA3AF;
        }
        .lesson-checkbox:checked + label .fa-check {
            display: inline-block !important;
        }
        .progress-bar {
            transition: width 0.5s ease;
        }
        .tooltip {
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
            position: absolute;
            z-index: 10;
        }
        .has-tooltip:hover .tooltip {
            opacity: 1;
        }
        .spinner { display: inline-block; border: 3px solid rgba(255,255,255,.3); border-left-color: #fff; border-radius: 50%; width: 1rem; height: 1rem; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
         button:disabled, a.disabled { opacity: 0.6; cursor: not-allowed; }
         /* Add scroll margin for curriculum if needed */
         #curriculum-section { scroll-margin-top: 90px; }
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
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i>
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
                             <!-- Profile Icon Link -->
                             <div class="relative">
                                <a href="Dashboard.php" class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-2 rounded-full transition" title="Go to Dashboard">
                                     <!-- Use a generic icon or fetch user specific later -->
                                     <span class="inline-block h-8 w-8 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center">
                                          <i class="fas fa-user text-gray-300"></i>
                                     </span>
                                    <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-secondary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                                </a>
                             </div>
                        <?php else: ?>
                             <!-- Login/Signup Buttons -->
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
                           <span class="inline-block h-8 w-8 mr-3 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center">
                               <i class="fas fa-user text-gray-300"></i>
                           </span>
                           My Dashboard
                      </a>
                      <!-- Maybe add logout here too? -->
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
                    <span class="inline-block bg-green-500 text-black px-2 py-1 rounded-md text-xs font-bold">BESTSELLER</span>
                    <!-- Dynamic Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mt-3"><?php safe_echo($course_title_display); ?></h1>
                    <p class="text-gray-400 mt-2 text-lg">Master Python, TensorFlow, Neural Networks & More | Build Real-World AI Projects | Career-Focused Curriculum</p>

                    <!-- Ratings/Info (Original) -->
                    <div class="flex flex-wrap items-center mt-4 gap-2">
                        <div class="flex items-center">
                            <div class="flex items-center text-yellow-400">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="ml-1 text-sm">4.8 (45,300 ratings)</span>
                        </div>
                        <span class="text-gray-400 text-sm">•</span>
                        <span class="text-sm">220,000+ learners</span>
                        <span class="text-gray-400 text-sm">•</span>
                        <span class="text-sm">Last updated 4/2024</span>
                    </div>
                    <div class="flex items-center mt-3">
                        <span class="text-sm">Created by </span>
                        <a href="#" class="text-secondary-400 hover:underline ml-1 text-sm">Dr. Sarah Zhang</a><span class="text-sm mx-1">,</span>
                        <a href="#" class="text-secondary-400 hover:underline text-sm">Raj Patel</a>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-center"><i class="fas fa-globe text-gray-400 mr-2"></i><span class="text-sm">English</span></div>
                        <div class="flex items-center mt-2"><i class="fas fa-closed-captioning text-gray-400 mr-2"></i><span class="text-sm">English [Auto], Spanish, Hindi</span></div>
                    </div>
                    <!-- NOTE: Enrollment button moved to sidebar -->
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
                   <!-- ... What you'll learn content ... -->
                   <h2 class="text-2xl font-bold">What you'll learn</h2>
                   <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                       <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Master Python programming for AI/ML and data science</span></div>
                       <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Build and train neural networks with TensorFlow & PyTorch</span></div>
                       <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Implement machine learning algorithms from scratch</span></div>
                       <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Work with real datasets and solve business problems</span></div>
                       <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Deploy ML models to production environments</span></div>
                       <div class="flex items-start"><i class="fas fa-check text-green-500 mt-1 mr-2 flex-shrink-0"></i><span>Understand ethical considerations in AI development</span></div>
                   </div>
                </div>

                <!-- Course Content Accordion (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl shadow-lg overflow-hidden" id="curriculum-section">
                    <h2 class="text-2xl font-bold p-6">Course content</h2>
                    <div class="divide-y divide-gray-700">
                        <!-- Module 1 -->
                        <div class="accordion-item">
                            <input type="checkbox" id="module1" class="accordion-toggle hidden">
                            <label for="module1" class="flex justify-between items-center p-4 hover:bg-gray-700 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-down accordion-icon transform transition-transform mr-3"></i>
                                    <span class="font-medium">Module 1: Python for AI & Data Science</span>
                                </div>
                                <span class="text-sm text-gray-400">7 lectures • 4h 30m</span>
                            </label>
                            <div class="accordion-content">
                                <div class="pl-12 pr-4 pb-4 text-sm"> <!-- Adjusted padding/size -->
                                    <!-- Lesson list (Original) -->
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Python Programming Fundamentals</span></div><span class="text-xs text-gray-400">45m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>NumPy for Numerical Computing</span></div><span class="text-xs text-gray-400">55m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Pandas for Data Manipulation</span></div><span class="text-xs text-gray-400">1h 10m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Data Visualization with Matplotlib & Seaborn</span></div><span class="text-xs text-gray-400">50m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Working with APIs and Web Data</span></div><span class="text-xs text-gray-400">40m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Quiz: Python Basics</span></div><span class="text-xs text-gray-400">10 questions</span></div>
                                     <div class="flex justify-between items-center py-2"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Project: Data Analysis with Python</span></div><span class="text-xs text-gray-400">1h 30m</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Module 2 -->
                        <div class="accordion-item">
                            <input type="checkbox" id="module2" class="accordion-toggle hidden">
                            <label for="module2" class="flex justify-between items-center p-4 hover:bg-gray-700 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-down accordion-icon transform transition-transform mr-3"></i>
                                    <span class="font-medium">Module 2: Machine Learning Fundamentals</span>
                                </div>
                                <span class="text-sm text-gray-400">8 lectures • 5h 15m</span>
                            </label>
                            <div class="accordion-content">
                                <div class="pl-12 pr-4 pb-4 text-sm">
                                    <!-- Lesson list (Original) -->
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Introduction to Machine Learning</span></div><span class="text-xs text-gray-400">35m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Supervised vs Unsupervised Learning</span></div><span class="text-xs text-gray-400">45m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Linear & Logistic Regression</span></div><span class="text-xs text-gray-400">1h 10m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Decision Trees & Random Forests</span></div><span class="text-xs text-gray-400">1h 5m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Clustering with K-Means</span></div><span class="text-xs text-gray-400">40m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-play-circle text-gray-400 mr-3 w-5 text-center"></i><span>Model Evaluation & Hyperparameter Tuning</span></div><span class="text-xs text-gray-400">50m</span></div>
                                     <div class="flex justify-between items-center py-2 border-b border-gray-700/50"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Quiz: ML Concepts</span></div><span class="text-xs text-gray-400">15 questions</span></div>
                                     <div class="flex justify-between items-center py-2"><div class="flex items-center text-gray-300"><i class="fas fa-laptop-code text-gray-400 mr-3 w-5 text-center"></i><span>Project: Predicting Housing Prices</span></div><span class="text-xs text-gray-400">1h 30m</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Add more modules as needed -->
                    </div>
                    <div class="p-4 text-center border-t border-gray-700">
                        <button class="text-primary-400 hover:underline text-sm">Show all 12 modules</button>
                    </div>
                </div>

                <!-- Requirements (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                   <h2 class="text-2xl font-bold">Requirements</h2>
                   <ul class="list-disc list-inside text-gray-300 mt-4 space-y-2 text-sm">
                       <li>Basic high school math (algebra, statistics)</li>
                       <li>No prior programming experience required (we teach Python from scratch)</li>
                       <li>Computer with 8GB+ RAM (Windows/Mac/Linux)</li>
                       <li>Internet connection for downloading software and datasets</li>
                   </ul>
                </div>

                <!-- Description (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                    <!-- ... Description Content ... -->
                    <h2 class="text-2xl font-bold">Description</h2>
                    <div class="mt-4 text-gray-300 space-y-4 text-sm">
                        <p>Welcome to the <span class="font-semibold text-primary-400">AI & Machine Learning Bootcamp 2024</span>, the most comprehensive, project-based AI course available online...</p>
                        <p>This program takes you from absolute beginner to job-ready AI practitioner...</p>
                        <h3 class="text-xl font-semibold mt-4">Why This Course?</h3>
                        <ul class="list-disc list-inside space-y-2 ml-4">
                           <li><span class="font-medium">Project-based</span> - Build portfolio-worthy projects...</li>
                           <li><span class="font-medium">Comprehensive</span> - Covers Python, ML, DL...</li>
                           <li><span class="font-medium">Up-to-date</span> - Includes latest AI advancements...</li>
                           <li><span class="font-medium">Career-focused</span> - Resume and interview prep included</li>
                        </ul>
                        <h3 class="text-xl font-semibold mt-4">What's Included?</h3>
                        <ul class="list-disc list-inside space-y-2 ml-4">
                            <li>Lifetime access...</li><li>Downloadable Python code...</li><li>Certificate of completion</li><li>Access to our AI community</li><li>Career resources...</li><li>Regular updates...</li>
                        </ul>
                        <p class="mt-4">By the end of this bootcamp, you'll have built 10+ real-world AI projects...</p>
                        <div class="bg-gray-700 p-4 rounded-lg mt-6"><h4 class="font-bold text-lg">30-Day Money-Back Guarantee</h4><p class="mt-2 text-xs">We're confident you'll love this course...</p></div>
                    </div>
                </div>

                <!-- Instructor Section (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                   <!-- ... Instructor Content ... -->
                   <h2 class="text-2xl font-bold">Instructors</h2>
                   <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                       <!-- Instructor 1 -->
                       <div class="flex items-start"> <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Dr. Sarah Zhang" class="w-20 h-20 rounded-full object-cover border-2 border-primary-600"> <div class="ml-4"> <h3 class="text-xl font-bold">Dr. Sarah Zhang</h3> <p class="text-gray-400 text-sm">AI Researcher & Former Google Brain Engineer</p> <!-- Ratings/Info --> <div class="flex items-center mt-2"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span class="ml-2 text-xs">4.9 Instructor Rating</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-user-graduate text-gray-400 mr-2 w-4 text-center"></i><span>200,000+ Students</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>10 Courses</span></div><p class="mt-3 text-gray-300 text-xs">Sarah holds a PhD... accessible.</p><div class="flex space-x-3 mt-3"><a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-linkedin"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fas fa-globe"></i></a></div></div></div>
                       <!-- Instructor 2 -->
                       <div class="flex items-start"> <img src="https://randomuser.me/api/portraits/men/55.jpg" alt="Raj Patel" class="w-20 h-20 rounded-full object-cover border-2 border-primary-600"> <div class="ml-4"> <h3 class="text-xl font-bold">Raj Patel</h3> <p class="text-gray-400 text-sm">ML Engineer & Founder of AI Startup</p> <!-- Ratings/Info --> <div class="flex items-center mt-2"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs">4.8 Instructor Rating</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-user-graduate text-gray-400 mr-2 w-4 text-center"></i><span>150,000+ Students</span></div><div class="flex items-center mt-1 text-xs"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>7 Courses</span></div><p class="mt-3 text-gray-300 text-xs">Raj has built ML systems... deployment.</p><div class="flex space-x-3 mt-3"><a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-linkedin"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-secondary-400 text-lg"><i class="fas fa-globe"></i></a></div></div></div>
                   </div>
                </div>

                <!-- Student Feedback (Original) -->
                <div class="mt-6 bg-gray-800 rounded-xl p-6 shadow-lg">
                   <!-- ... Feedback Content ... -->
                   <h2 class="text-2xl font-bold">Student feedback</h2>
                    <div class="mt-4 flex flex-col md:flex-row items-center"> <div class="text-center md:text-left md:mr-8"> <div class="text-5xl font-bold text-primary-400">4.8</div><div class="flex justify-center md:justify-start mt-1"><div class="flex items-center text-yellow-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div></div><div class="text-sm mt-1 text-gray-400">Course Rating</div></div><div class="w-full mt-4 md:mt-0"> <div class="flex items-center text-xs"><span class="w-10">5 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 75%"></div></div><span class="w-10 text-right">75%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">4 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 18%"></div></div><span class="w-10 text-right">18%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">3 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 5%"></div></div><span class="w-10 text-right">5%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">2 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 1%"></div></div><span class="w-10 text-right">1%</span></div><div class="flex items-center mt-1 text-xs"><span class="w-10">1 star</span><div class="flex-1 mx-2 h-2 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400" style="width: 1%"></div></div><span class="w-10 text-right">1%</span></div></div></div>
                    <div class="mt-8"> <h3 class="text-lg font-semibold">Reviews</h3> <div class="space-y-6 mt-4"> <!-- Review 1 --> <div class="border-b border-gray-700 pb-6"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/men/42.jpg" alt="David K." class="w-10 h-10 rounded-full"> <div class="ml-3"> <div class="font-medium text-sm">David K.</div><div class="flex items-center text-xs text-gray-400"><div class="flex items-center text-yellow-400 mr-1 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span>1 month ago</span></div></div></div><div class="mt-3 text-sm"> <h4 class="font-semibold text-base">Changed my career trajectory</h4> <p class="text-gray-300 mt-1">This bootcamp gave me the skills...</p></div></div><!-- Review 2 --> <div class="border-b border-gray-700 pb-6"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/women/38.jpg" alt="Priya M." class="w-10 h-10 rounded-full"> <div class="ml-3"> <div class="font-medium text-sm">Priya M.</div><div class="flex items-center text-xs text-gray-400"><div class="flex items-center text-yellow-400 mr-1 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span>2 weeks ago</span></div></div></div><div class="mt-3 text-sm"> <h4 class="font-semibold text-base">Perfect balance of theory and practice</h4> <p class="text-gray-300 mt-1">As a data analyst looking to upskill...</p></div></div></div> <button class="mt-6 text-primary-400 hover:underline text-sm">See all 12,450 reviews</button> </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-20"> <!-- Adjusted top value -->
                    <!-- Course Card -->
                    <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-700">
                        <div class="relative">
                            <img src="https://images.unsplash.com/photo-1620712943543-bcc4688e7485?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="<?php safe_echo($course_title_display); ?>" class="w-full h-48 object-cover">
                            <div class="absolute top-3 left-3 bg-black/70 text-white px-2 py-1 rounded-md text-xs">
                                <i class="fas fa-play-circle mr-1"></i>
                                <span>Preview available</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <p class="text-2xl font-bold">₹899</p> <!-- Static Price -->
                                <div class="text-right">
                                    <p class="text-gray-400 line-through">₹4,499</p>
                                    <p class="text-red-500 font-bold">80% off</p>
                                </div>
                            </div>
                            <p class="text-red-500 text-sm mt-1 flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                <span id="countdown-timer">3 hours left at this price!</span>
                            </p>

                            <!-- === Dynamic Enrollment Buttons START === -->
                            <div class="mt-6 space-y-3" id="enrollment-action-area">
                                <?php if ($is_enrolled): ?>
                                     <a href="#curriculum-section" class="w-full flex items-center justify-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-medium text-base shadow-md">
                                         <i class="fas fa-play-circle mr-2"></i>Continue Learning
                                     </a>
                                     <!-- Optionally add a disabled 'Enrolled' state text -->
                                     <p class="text-center text-sm text-green-400 mt-2">You are enrolled in this course.</p>
                                <?php else: ?>
                                     <button id="enroll-btn" data-course-slug="<?php safe_echo($course_slug); ?>" class="w-full flex items-center justify-center bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition font-medium text-base shadow-md">
                                         <span class="btn-text">Enroll Now</span>
                                         <span class="spinner hidden ml-2"></span>
                                     </button>
                                     <!-- Placeholder for 'Buy Now' or other actions if needed -->
                                     <!-- <button class="w-full bg-gray-700 text-white py-3 rounded-lg hover:bg-gray-600 transition font-medium">Buy Now (Placeholder)</button> -->
                                <?php endif; ?>
                            </div>
                            <?php if (!$is_enrolled && !$user_logged_in): ?>
                                <p class="text-xs text-center text-gray-400 mt-3">
                                   <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-400 hover:underline">Log in</a> or
                                   <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-400 hover:underline">sign up</a>
                                   to enroll.
                                </p>
                            <?php endif; ?>
                            <!-- === Dynamic Enrollment Buttons END === -->

                            <p class="text-center text-sm mt-4 text-gray-400">30-Day Money-Back Guarantee</p>

                            <div class="mt-6">
                                <h3 class="font-bold text-sm">This course includes:</h3>
                                <ul class="mt-3 space-y-2 text-sm">
                                    <!-- List items (Original) -->
                                    <li class="flex items-center"><i class="fas fa-play-circle text-gray-400 mr-2 w-4 text-center"></i><span>60 hours on-demand video</span></li>
                                    <li class="flex items-center"><i class="fas fa-file-alt text-gray-400 mr-2 w-4 text-center"></i><span>40 articles</span></li>
                                    <li class="flex items-center"><i class="fas fa-download text-gray-400 mr-2 w-4 text-center"></i><span>30 downloadable resources</span></li>
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
                        <h3 class="font-bold">Training 5 or more people?</h3>
                        <p class="text-gray-400 text-sm mt-2">Get your team access to 5,000+ top courses anytime, anywhere.</p>
                        <button class="w-full mt-4 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition text-sm">
                            Learn about team plans
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Related Courses (Original) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
       <!-- ... Related Courses HTML ... -->
       <h2 class="text-2xl font-bold mb-8">Students also bought</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Course 1 --><div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1629904853893-c2c8981a1dc5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Deep Learning Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">Deep Learning with TensorFlow</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><span class="ml-2 text-xs text-gray-400">(18,750)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹999</span><span class="text-xs text-gray-400 line-through">₹4,999</span></div></div></div>
            <!-- Course 2 --><div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Data Science Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">Data Science & Python Bootcamp</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs text-gray-400">(22,300)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹799</span><span class="text-xs text-gray-400 line-through">₹3,999</span></div></div></div>
            <!-- Course 3 --><div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden course-card transition duration-300"><img src="https://images.unsplash.com/photo-1617791160536-598cf32026fb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="NLP Course" class="w-full h-40 object-cover"><div class="p-4"> <h3 class="font-bold text-base">Natural Language Processing (NLP)</h3> <div class="flex items-center mt-1"><div class="flex items-center text-yellow-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="ml-2 text-xs text-gray-400">(12,450)</span></div><div class="flex items-center justify-between mt-3"><span class="font-bold text-sm">₹899</span><span class="text-xs text-gray-400 line-through">₹4,499</span></div></div></div>
        </div>
    </div>

    <!-- FAQ Section (Original) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
       <!-- ... FAQ Content ... -->
       <h2 class="text-2xl font-bold mb-8">Frequently asked questions</h2>
       <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
           <div class="divide-y divide-gray-700">
               <!-- FAQ Item 1 --><div class="accordion-item"><input type="checkbox" id="faq1" class="accordion-toggle hidden"><label for="faq1" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">Do I need prior programming experience?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>No! This bootcamp teaches Python programming from scratch...</p></div></div></div>
               <!-- FAQ Item 2 --><div class="accordion-item"><input type="checkbox" id="faq2" class="accordion-toggle hidden"><label for="faq2" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">What kind of jobs can I get after this course?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>Graduates have landed roles as Machine Learning Engineers...</p></div></div></div>
               <!-- FAQ Item 3 --><div class="accordion-item"><input type="checkbox" id="faq3" class="accordion-toggle hidden"><label for="faq3" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">How does this compare to a university degree?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>While not equivalent to a 4-year degree...</p></div></div></div>
               <!-- FAQ Item 4 --><div class="accordion-item"><input type="checkbox" id="faq4" class="accordion-toggle hidden"><label for="faq4" class="flex justify-between items-center p-6 hover:bg-gray-700 cursor-pointer"><span class="font-medium text-sm">Are there any certificates provided?</span><i class="fas fa-chevron-down accordion-icon transform transition-transform text-sm"></i></label><div class="accordion-content"><div class="px-6 pb-6 text-gray-300 text-sm"><p>Yes! You'll receive a certificate of completion...</p></div></div></div>
           </div>
       </div>
    </div>

    <!-- Footer (Original) -->
    <footer class="bg-gray-800 border-t border-gray-700">
       <!-- ... Footer HTML ... -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12"> <div class="grid grid-cols-1 md:grid-cols-4 gap-8"> <div> <div class="flex items-center"> <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i> <span class="text-xl font-bold">EduPro</span> </div><p class="mt-4 text-gray-400 text-sm">Learn the latest skills to reach your professional goals.</p><div class="flex space-x-4 mt-4"> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a> <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-youtube"></i></a> </div></div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Company</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">About</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Careers</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Blog</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Press</a></li></ul> </div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Support</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">Contact Us</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Help Center</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Privacy Policy</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Terms of Service</a></li></ul> </div><div> <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Resources</h3> <ul class="mt-4 space-y-2"> <li><a href="#" class="text-sm text-gray-300 hover:text-white">Download App</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Documentation</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Guides</a></li><li><a href="#" class="text-sm text-gray-300 hover:text-white">Community</a></li></ul> </div></div><div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center"> <p class="text-gray-400 text-sm">© <?php echo date('Y'); ?> EduPro, Inc. All rights reserved.</p><div class="mt-4 md:mt-0 flex space-x-6"> <a href="#" class="text-gray-400 hover:text-white text-sm">Privacy</a> <a href="#" class="text-gray-400 hover:text-white text-sm">Terms</a> <a href="#" class="text-gray-400 hover:text-white text-sm">Sitemap</a> </div></div></div>
    </footer>

    <script>
        // Combine ALL JavaScript logic here
        document.addEventListener('DOMContentLoaded', function() {

            // --- Mobile menu toggle (From Original Template) ---
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                     // Optional: toggle icon
                     const icon = mobileMenuButton.querySelector('i');
                     if (icon) {
                        icon.classList.toggle('fa-bars');
                        icon.classList.toggle('fa-times');
                     }
                });
            }

            // --- Accordion functionality (From Original Template) ---
            document.querySelectorAll('.accordion-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    // Content handled by CSS sibling selector (:checked ~ .accordion-content)
                    // Icon rotation:
                    const icon = this.closest('.accordion-item').querySelector('.accordion-icon');
                    if (icon) {
                       icon.classList.toggle('rotate-180', this.checked);
                    }
                });
            });

            // --- Countdown timer (From Original Template) ---
            const countdownElement = document.getElementById('countdown-timer');
            if (countdownElement) {
                function updateCountdown() {
                    // Simple example: Set expiry 3 hours from now (adjust as needed)
                    const now = new Date();
                    const endTime = new Date(now.getTime() + 3 * 60 * 60 * 1000);

                    const timer = setInterval(function() {
                        const now = new Date();
                        const distance = endTime - now;

                        if (distance < 0) {
                            clearInterval(timer);
                            if(countdownElement) countdownElement.textContent = 'Discount expired!';
                            return;
                        }

                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        if(countdownElement) {
                           countdownElement.textContent =
                            `${hours}h ${minutes}m ${seconds}s left at this price!`;
                        } else {
                            clearInterval(timer); // Stop if element disappears
                        }
                    }, 1000);
                }
                updateCountdown(); // Start the countdown
            }

            // --- Smooth scrolling for anchor links (From Original Template) ---
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                         e.preventDefault();
                         targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                    // Allow normal behavior if target isn't on this page
                });
            });

            // --- Progress & Enrollment Logic (From ai_course.php) ---
            const checkboxes = document.querySelectorAll('.lesson-checkbox'); // NOTE: Your HTML doesn't have these yet! Add them if needed for progress.
            const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 18; // Adjust fallback number

            function updateProgress() {
                const progressBar = document.getElementById('progress-bar'); // Requires adding this element
                const progressText = document.getElementById('progress-text'); // Requires adding this element
                if (!progressBar || !progressText) return; // Exit if elements not found

                const completed = [...checkboxes].filter(checkbox => checkbox.checked).length;
                const progress = totalLessonsForProgress > 0 ? Math.round((completed / totalLessonsForProgress) * 100) : 0;

                progressBar.style.width = progress + '%';
                progressText.textContent = `${completed}/${totalLessonsForProgress} lessons completed`;

                 progressBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-primary-600');
                 if (progress < 30) { progressBar.classList.add('bg-red-500'); }
                 else if (progress < 70) { progressBar.classList.add('bg-yellow-500'); }
                 else if (progress >= 100) { progressBar.classList.add('bg-green-500'); }
                 else { progressBar.classList.add('bg-primary-600'); }
            }

            function updateCompletionBadge() {
                const completionBadge = document.getElementById('completion-badge');
                if (!completionBadge) return; // Exit if element not found

                const completed = [...checkboxes].filter(checkbox => checkbox.checked).length;
                if (completed > 0) {
                    completionBadge.textContent = completed;
                    completionBadge.classList.remove('hidden');
                } else {
                    completionBadge.classList.add('hidden');
                }
            }

            checkboxes.forEach(checkbox => {
                const lessonId = checkbox.id;
                const storageKey = lessonId + '_<?php safe_echo($course_slug); ?>'; // Use course slug
                const savedState = localStorage.getItem(storageKey);

                checkbox.checked = (savedState === 'true'); // Set initial state

                const label = checkbox.nextElementSibling;
                const checkIcon = label ? label.querySelector('.fa-check') : null;
                if (checkbox.checked) {
                   if (checkIcon) checkIcon.classList.remove('hidden');
                   if (label) label.classList.add('line-through', 'text-gray-500');
                } else {
                   if (checkIcon) checkIcon.classList.add('hidden');
                   if (label) label.classList.remove('line-through', 'text-gray-500');
                }

                checkbox.addEventListener('change', function() {
                    localStorage.setItem(storageKey, this.checked);
                    const label = this.nextElementSibling;
                    const checkIcon = label ? label.querySelector('.fa-check') : null;
                    if (this.checked) {
                        if (checkIcon) checkIcon.classList.remove('hidden');
                        if (label) label.classList.add('line-through', 'text-gray-500');
                    } else {
                        if (checkIcon) checkIcon.classList.add('hidden');
                        if (label) label.classList.remove('line-through', 'text-gray-500');
                    }
                    updateProgress();
                    updateCompletionBadge();
                });
            });

            // Initial update if progress elements exist
            updateProgress();
            updateCompletionBadge();

            // --- Enrollment Button Logic ---
            const enrollButton = document.getElementById('enroll-btn'); // Button ID must match
            const enrollmentActionArea = document.getElementById('enrollment-action-area'); // Area ID must match
            const isLoggedIn = <?php echo json_encode($user_logged_in); ?>;
            const isPaidCourse = <?php echo json_encode($is_paid_course); ?>;

            if (enrollButton && enrollmentActionArea) {
                const enrollButtonText = enrollButton.querySelector('.btn-text'); // Class must match
                const enrollButtonSpinner = enrollButton.querySelector('.spinner'); // Class must match

                enrollButton.addEventListener('click', function() {
                    if (!isLoggedIn) {
                        alert('Please log in or sign up to enroll.');
                        window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                        return;
                    }

                    // Optional: Add payment handling for paid courses before fetch
                    if (isPaidCourse) {
                        console.log("Paid course detected - Add payment flow here before fetch if needed.");
                        // Example: redirect or show modal
                        // For now, we proceed to fetch enrollment directly
                    }

                    const courseSlug = this.dataset.courseSlug;
                    const button = this;

                    button.disabled = true;
                    if (enrollButtonText) enrollButtonText.textContent = 'Enrolling...'; else button.textContent = 'Enrolling...'; // Fallback
                    if (enrollButtonSpinner) enrollButtonSpinner.classList.remove('hidden');
                    button.classList.add('opacity-60', 'cursor-wait');

                    const formData = new FormData();
                    formData.append('course_slug', courseSlug);

                    fetch('enroll_course.php', { method: 'POST', body: formData })
                    .then(response => {
                        if (!response.ok) {
                             return response.text().then(text => { throw new Error(`Server responded with status ${response.status}. Response: ${text || '(empty)'}`); });
                        }
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) { return response.json(); }
                        else { return response.text().then(text => { throw new Error(`Unexpected response format. Expected JSON, got: ${text || '(empty)'}`); }); }
                    })
                    .then(data => {
                        if (data.success) {
                            console.log("Enrollment successful:", data.message);
                            // Update UI in the sidebar card
                            enrollmentActionArea.innerHTML = `
                                <a href="#curriculum-section" class="w-full flex items-center justify-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-medium text-base shadow-md">
                                    <i class="fas fa-play-circle mr-2"></i>Continue Learning
                                </a>
                                <p class="text-center text-sm text-green-400 mt-2">You are enrolled in this course.</p>
                             `;
                        } else {
                            if (data.action === 'redirect_login') {
                                alert('Session issue. Please log in again.');
                                window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                            } else if (data.message && data.message.includes('already enrolled')) {
                                 console.warn("Already enrolled message:", data.message);
                                 enrollmentActionArea.innerHTML = `
                                    <a href="#curriculum-section" class="w-full flex items-center justify-center bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-medium text-base shadow-md"> <i class="fas fa-play-circle mr-2"></i>Continue Learning </a>
                                    <p class="text-center text-sm text-green-400 mt-2">You are enrolled in this course.</p> `;
                            } else {
                                alert('Enrollment failed: ' + (data.message || 'Unknown error.'));
                                button.disabled = false;
                                if (enrollButtonText) enrollButtonText.textContent = 'Enroll Now'; else button.textContent = 'Enroll Now'; // Reset text
                                if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden');
                                button.classList.remove('opacity-60', 'cursor-wait');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Enrollment fetch error:', error);
                        alert('An error occurred while enrolling. Please try again.\nError: ' + error.message);
                        button.disabled = false;
                        if (enrollButtonText) enrollButtonText.textContent = 'Enroll Now'; else button.textContent = 'Enroll Now'; // Reset text
                        if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden');
                        button.classList.remove('opacity-60', 'cursor-wait');
                    });
                });
            } else {
                 console.log("Enrollment button or action area not found.");
            }

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