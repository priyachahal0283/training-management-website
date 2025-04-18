<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require database configuration (this sets $mysqli)
require_once 'config.php'; // <--- Sets up $mysqli

// --- Course Specific Variables ---
// *** IMPORTANT: Unique Slug for this course ***
$course_slug = 'javascript-mastery-free'; // Slug from original code
$course_title_display = 'Modern JavaScript Mastery'; // Title from original code
$is_paid_course = false; // This is a FREE course

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
            $db_connection_error = true; // Flag error on statement prepare fail
        }
    } else {
        // === Use $mysqli ===
        $db_error_msg = isset($mysqli) && $mysqli instanceof mysqli ? $mysqli->connect_error : '$mysqli variable not set or not a mysqli object';
        error_log("Database connection error in page for course '$course_slug': " . $db_error_msg);
        $db_connection_error = true; // Flag error on connection fail
    }
}

// --- Determine Button State based on logic ---
$button_text = $is_paid_course ? 'Enroll Now' : 'Start Free Course'; // Default Text
$button_disabled = false; // Default state
$button_classes_extra = 'hover:bg-primary-700'; // Default hover effect

if ($is_enrolled) {
    // User is already enrolled
    $button_text = $is_paid_course ? '<i class="fas fa-check mr-2"></i>Enrolled' : '<i class="fas fa-play mr-2"></i>Continue Learning';
    $button_disabled = true;
    $button_classes_extra = 'opacity-75 cursor-not-allowed bg-green-600'; // Styling for disabled/enrolled state (using green for continue)
} elseif ($db_connection_error) {
    // DB connection error prevents enrollment
     $button_text = 'Service Unavailable'; // More specific error
     $button_disabled = true;
     $button_classes_extra = 'opacity-50 cursor-not-allowed bg-gray-500'; // Visually indicate unavailability
} elseif (!$user_logged_in && !$is_paid_course) {
    // User not logged in, but it's a free course
    $button_text = 'Start Free Course'; // Keep original text
    // $button_disabled remains false, JS handles login redirect
    // $button_classes_extra remains default hover
} elseif (!$user_logged_in && $is_paid_course) {
     // User not logged in for a paid course
     $button_text = 'Enroll Now';
     // $button_disabled remains false, JS handles login redirect
}
// --- END: PHP Logic ---


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
        .lesson-checkbox:checked + label .fa-check { display: inline-block !important; } /* Corrected */
        .progress-bar { transition: width 0.5s ease; }
        .tooltip { opacity: 0; transition: opacity 0.2s ease; pointer-events: none; position: absolute; z-index: 10; background-color: #1f2937; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; white-space: nowrap; bottom: 100%; left: 50%; transform: translateX(-50%) translateY(-5px); }
        .has-tooltip:hover .tooltip { opacity: 1; }
        .spinner { display: inline-block; border: 3px solid rgba(255,255,255,.3); border-left-color: #fff; border-radius: 50%; width: 1rem; height: 1rem; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner.hidden { display: none; } /* Ensure hidden class works */
        button:disabled, a.disabled { opacity: 0.6; cursor: not-allowed; }
        #curriculum-section { scroll-margin-top: 90px; } /* Added scroll margin */
         .mobile-menu a { display: block; padding: 0.75rem 1rem; border-bottom: 1px solid #374151; }
         .mobile-menu a:last-child { border-bottom: none; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700 py-4 sticky top-0 z-50">
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
                        <span class="tooltip">Search courses</span>
                    </button>
                    <button class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip">
                        <i class="fas fa-bell text-gray-400"></i>
                        <span class="tooltip">Notifications</span>
                    </button>

                    <!-- === LOGIN/SIGNUP/PROFILE AREA START === -->
                    <div class="relative flex items-center">
                         <?php if($user_logged_in): ?>
                            <a href="Dashboard.php" class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-1 rounded-full transition relative" title="Go to Dashboard">
                                <img src="https://randomuser.me/api/portraits/men/46.jpg" class="w-8 h-8 rounded-full">
                                <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                             </a>
                              <a href="logout.php" class="ml-3 p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip" title="Logout">
                                 <i class="fas fa-sign-out-alt text-gray-400"></i>
                                 <span class="tooltip">Logout</span>
                              </a>
                         <?php else: ?>
                             <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Log In</a>
                             <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="ml-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">Sign Up</a>
                         <?php endif; ?>
                     </div>
                     <!-- === LOGIN/SIGNUP/PROFILE AREA END === -->

                      <!-- Mobile Menu Button -->
                      <div class="md:hidden">
                          <button class="mobile-menu-button p-2 rounded-md hover:bg-gray-700 focus:outline-none">
                              <i class="fas fa-bars text-xl text-gray-300"></i>
                          </button>
                     </div>
                </div>
            </div>
            <!-- Mobile Menu Content -->
             <div class="mobile-menu hidden md:hidden bg-gray-800 border-t border-gray-700 mt-2">
                 <a href="courses_list.php" class="text-gray-300 hover:text-white">Courses</a>
                
                 <a href="resources.html" class="text-gray-300 hover:text-white">Resources</a>
                 <hr class="border-gray-700 my-1">
                 <?php if($user_logged_in): ?>
                      <a href="Dashboard.php" class="text-gray-300 hover:text-white">Dashboard</a>
                      <a href="logout.php" class="text-gray-300 hover:text-white">Logout</a>
                  <?php else: ?>
                      <a href="Login.php" class="text-gray-300 hover:text-white">Log in</a>
                      <a href="Signup.php" class="block text-center text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-md mx-4 my-2 py-2">Sign up</a>
                  <?php endif; ?>
             </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Course Header -->
        <div class="bg-gray-800 rounded-xl p-6 md:p-8 mb-8 border border-gray-700">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="md:w-2/3">
                    <span class="inline-block bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-semibold mb-4 uppercase tracking-wider">
                         <?php echo $is_paid_course ? 'Professional Certificate' : 'Free Course'; ?>
                    </span>
                     <!-- Dynamic Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mb-4"><?php safe_echo($course_title_display); ?></h1>
                    <p class="text-lg text-gray-300 mb-6">Master modern JavaScript (ES6+) features, frameworks, and best practices to build powerful web applications.</p>

                    <!-- Ratings/Info (Original) -->
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-6 text-sm"> <div class="flex items-center"> <div class="flex items-center text-yellow-400 mr-1"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="text-gray-300">4.8</span> <span class="text-gray-400 ml-1">(3,450 ratings)</span></div><span class="text-gray-400 hidden sm:inline">•</span><span class="text-gray-400"><i class="fas fa-users mr-1"></i>42,000+ students enrolled</span><span class="text-gray-400 hidden sm:inline">•</span><span class="text-gray-400"><i class="fas fa-sync-alt mr-1"></i>Updated February 2024</span></div>

                    <!-- === Enrollment Action Area START === -->
                    <div class="flex items-center space-x-4" id="enrollment-action-area">
                         <button id="enroll-btn"
                                 data-course-slug="<?php safe_echo($course_slug); ?>"
                                 class="bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition duration-200 ease-in-out flex items-center justify-center <?php echo $button_classes_extra; ?>"
                                 <?php echo $button_disabled ? 'disabled' : ''; ?>>
                            <span class="btn-text"><?php echo $button_text; ?></span> <!-- PHP sets text and icon -->
                            <span class="spinner hidden ml-2"></span>
                        </button>
                         <button class="flex items-center text-gray-300 hover:text-white transition relative has-tooltip">
                            <i class="far fa-heart mr-2"></i> Save
                            <span class="tooltip">Save for later</span>
                        </button>
                    </div>
                     <?php if (!$is_paid_course && !$is_enrolled && !$user_logged_in && !$db_connection_error): ?>
                       <p class="text-xs text-gray-400 mt-2">Log in or sign up to start this free course.</p>
                    <?php elseif ($db_connection_error): ?>
                        <p class="text-xs text-red-400 mt-2">Could not connect to enrollment service. Please try again later.</p>
                    <?php endif; ?>
                     <!-- === Enrollment Action Area END === -->

                </div>
                 <!-- Course Image (Original) -->
                <div class="hidden md:block md:w-1/3 mt-6 md:mt-0 md:pl-6">
                    <div class="relative overflow-hidden rounded-lg course-card border-none">
                        <img src="https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="<?php safe_echo($course_title_display); ?>"
                             class="w-full h-64 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end p-4">
                            <button class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full hover:bg-white/30 transition relative has-tooltip">
                                <a href="https://www.youtube.com/watch?v=lkIFF4maKMU"><i class="fas fa-play text-white text-xl"></i></a>
                                <span class="tooltip">Watch Preview</span>
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
                <!-- Adjust total lessons (3+2+1 = 6) -->
                <span id="progress-text" class="text-sm text-gray-300">0/6 lessons completed</span>
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

                    <!-- Module 1 (Original - 3 lessons) -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-primary-500">Module 1: Modern JavaScript Fundamentals</h3> <span class="text-sm text-gray-400">3 lessons</span> </div>
                        <div class="space-y-3">
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600 hover:border-primary-600/50 transition"> <div class="flex items-center flex-1 mr-3"> <input type="checkbox" id="lesson-1-js" class="lesson-checkbox hidden"> <label for="lesson-1-js" class="flex items-center cursor-pointer text-gray-200 hover:text-white"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center flex-shrink-0 transition duration-200"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>ES6+ Features and Syntax</span> </label> </div><div class="flex items-center space-x-3 flex-shrink-0"> <span class="text-sm text-gray-400">25 min</span> <a href="https://www.youtube.com/watch?v=NCwa_xi0Uuc" class="text-primary-600 hover:text-primary-500 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600 hover:border-primary-600/50 transition"> <div class="flex items-center flex-1 mr-3"> <input type="checkbox" id="lesson-2-js" class="lesson-checkbox hidden"> <label for="lesson-2-js" class="flex items-center cursor-pointer text-gray-200 hover:text-white"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center flex-shrink-0 transition duration-200"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Asynchronous JavaScript</span> </label> </div><div class="flex items-center space-x-3 flex-shrink-0"> <span class="text-sm text-gray-400">40 min</span> <a href="https://www.youtube.com/watch?v=Coyy79wRz_s" class="text-primary-600 hover:text-primary-500 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a> </div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600 hover:border-primary-600/50 transition"> <div class="flex items-center flex-1 mr-3"> <input type="checkbox" id="lesson-3-js" class="lesson-checkbox hidden"> <label for="lesson-3-js" class="flex items-center cursor-pointer text-gray-200 hover:text-white"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center flex-shrink-0 transition duration-200"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Functional Programming Concepts</span> </label> </div><div class="flex items-center space-x-3 flex-shrink-0"> <span class="text-sm text-gray-400">35 min</span> <a href="https://www.youtube.com/watch?v=dAPL7MQGjyM" class="text-primary-600 hover:text-primary-500 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a> </div></div>
                        </div>
                    </div>

                    <!-- Module 2 (Original - 2 lessons) -->
                    <div class="mb-8">
                          <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-purple-400">Module 2: Advanced JavaScript Patterns</h3> <span class="text-sm text-gray-400">2 lessons</span> </div>
                         <div class="space-y-3">
                              <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600 hover:border-primary-600/50 transition"> <div class="flex items-center flex-1 mr-3"> <input type="checkbox" id="lesson-4-js" class="lesson-checkbox hidden"> <label for="lesson-4-js" class="flex items-center cursor-pointer text-gray-200 hover:text-white"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center flex-shrink-0 transition duration-200"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Design Patterns in JavaScript</span> </label> </div><div class="flex items-center space-x-3 flex-shrink-0"> <span class="text-sm text-gray-400">45 min</span> <a href="https://www.youtube.com/watch?v=tv-_1er1mWI" class="text-primary-600 hover:text-primary-500 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a> </div></div>
                              <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600 hover:border-primary-600/50 transition"> <div class="flex items-center flex-1 mr-3"> <input type="checkbox" id="lesson-5-js" class="lesson-checkbox hidden"> <label for="lesson-5-js" class="flex items-center cursor-pointer text-gray-200 hover:text-white"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center flex-shrink-0 transition duration-200"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>Memory Management & Performance</span> </label> </div><div class="flex items-center space-x-3 flex-shrink-0"> <span class="text-sm text-gray-400">50 min</span> <a href="https://www.youtube.com/watch?v=vz6vSZRuS2M" class="text-primary-600 hover:text-primary-500 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a> </div></div>
                          </div>
                      </div>

                    <!-- Module 3 (Original - 1 lesson) -->
                     <div class="mb-8">
                          <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-green-400">Module 3: JavaScript in the Browser</h3> <span class="text-sm text-gray-400">1 lesson</span> </div>
                          <div class="space-y-3">
                              <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600 hover:border-primary-600/50 transition"> <div class="flex items-center flex-1 mr-3"> <input type="checkbox" id="lesson-6-js" class="lesson-checkbox hidden"> <label for="lesson-6-js" class="flex items-center cursor-pointer text-gray-200 hover:text-white"> <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center flex-shrink-0 transition duration-200"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span> <span>DOM Manipulation & Events</span> </label> </div><div class="flex items-center space-x-3 flex-shrink-0"> <span class="text-sm text-gray-400">30 min</span> <a href="https://www.youtube.com/watch?v=y17RuWkWdn8" class="text-primary-600 hover:text-primary-500 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a> </div></div>
                          </div>
                     </div>

                    <!-- Original Show All Button -->
                    <div class="text-center mt-8">
                        <button class="text-primary-600 hover:text-primary-500 font-medium"> Show all 5 modules <i class="fas fa-chevron-down ml-1 text-xs"></i> </button>
                    </div>
                </div>

                <!-- Instructor Section (Original) -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                      <h2 class="text-2xl font-bold mb-6">About the Instructor</h2>
                      <div class="flex flex-col md:flex-row items-start"> <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-24 h-24 rounded-full object-cover mb-4 md:mb-0 md:mr-6 border-4 border-primary-600/30"> <div> <h3 class="text-xl font-bold mb-1">Alex Rivera</h3> <p class="text-primary-500 mb-3">Senior JavaScript Developer | Framework Core Contributor</p><p class="text-gray-300 mb-4 text-sm"> With 10+ years of experience building complex web applications...</p><div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-400"> <div class="flex items-center"><i class="fas fa-star text-yellow-400 mr-1"></i><span>4.9 Instructor Rating</span></div><div class="flex items-center"><i class="fas fa-user-graduate mr-1"></i><span>15,800 Students</span></div><div class="flex items-center"><i class="fas fa-play-circle mr-1"></i><span>5 Courses</span></div></div></div></div>
                </div>
            </div>

            <!-- Sidebar (Original) -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6"> <!-- Adjust top value -->
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                        <h3 class="text-lg font-bold mb-4">This Course Includes:</h3>
                        <ul class="space-y-3 text-gray-300 text-sm">
                             <li class="flex items-center"><i class="fas fa-video text-primary-600 mr-3 w-4 text-center"></i>40 hours on-demand video</li>
                             <li class="flex items-center"><i class="fas fa-file-alt text-primary-600 mr-3 w-4 text-center"></i>30 downloadable resources</li>
                             <li class="flex items-center"><i class="fas fa-laptop-code text-primary-600 mr-3 w-4 text-center"></i>20 coding exercises</li>
                             <li class="flex items-center"><i class="fas fa-certificate text-primary-600 mr-3 w-4 text-center"></i>Certificate of completion</li>
                             <li class="flex items-center"><i class="fas fa-infinity text-primary-600 mr-3 w-4 text-center"></i>Full lifetime access</li>
                        </ul>
                    </div>

                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                         <h3 class="text-lg font-bold mb-4">Resources</h3>
                         <div class="space-y-4">
                             <a href="https://htmlcheatsheet.com/js/" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition group"> <div class="bg-primary-600/20 text-primary-600 group-hover:bg-primary-600/30 p-2 rounded-lg mr-3 transition flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-file-pdf"></i></div><div><h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm">JavaScript Cheat Sheet</h4><p class="text-xs text-gray-400">PDF • 1.8MB</p></div></a>
                             <a href="https://htmlcheatsheet.com/js/" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition group"> <div class="bg-purple-600/20 text-purple-500 group-hover:bg-purple-600/30 p-2 rounded-lg mr-3 transition flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-code"></i></div><div><h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm">Starter Code Files</h4><p class="text-xs text-gray-400">ZIP • 3.2MB</p></div></a>
                             <a href="https://chat.whatsapp.com/CqW46y9Fkgh6RT09xO8ylV" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition group"> <div class="bg-green-600/20 text-green-500 group-hover:bg-green-600/30 p-2 rounded-lg mr-3 transition flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-users"></i></div><div><h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm">Join JS Community</h4><p class="text-xs text-gray-400">Discord • 18K+ members</p></div></a>
                         </div>
                    </div>

                     <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                         <h3 class="text-lg font-bold mb-4">Students Also Viewed</h3>
                         <div class="space-y-4">
                             <a href="Cybersecurity Fundamentals free.php" class="flex items-center hover:bg-gray-700 p-3 -m-3 rounded-lg transition group"> <img src="https://images.unsplash.com/photo-1626785774573-4b799315345d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" class="w-16 h-12 rounded-lg object-cover mr-3 flex-shrink-0"> <div><h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm leading-tight">Cybersecurity Fundamentals</h4><div class="flex items-center text-xs text-gray-400 mt-1"><i class="fas fa-star text-yellow-400 mr-1 text-xs"></i><span>4.7 • 6.5k students</span></div></div></a>
                             <a href="Data Science free.php" class="flex items-center hover:bg-gray-700 p-3 -m-3 rounded-lg transition group"> <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" class="w-16 h-12 rounded-lg object-cover mr-3 flex-shrink-0"> <div><h4 class="font-medium text-gray-200 group-hover:text-white transition text-sm leading-tight">Data Science</h4><div class="flex items-center text-xs text-gray-400 mt-1"><i class="fas fa-star text-yellow-400 mr-1 text-xs"></i><span>4.9 • 8.2k students</span></div></div></a>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer (Original) -->
    <footer class="bg-gray-800 border-t border-gray-700 py-12">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"> <div class="grid grid-cols-1 md:grid-cols-4 gap-8"> <div> <div class="flex items-center mb-4"><i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i><span class="text-xl font-bold text-gray-100">EduPro</span></div><p class="text-gray-400 mb-4 text-sm">Advancing careers through world-class digital education.</p><div class="flex space-x-4"><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-youtube"></i></a></div></div><div><h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Company</h3><ul class="space-y-2"><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">About Us</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Careers</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Blog</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Press</a></li></ul></div><div><h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3><ul class="space-y-2"><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Help Center</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Contact Us</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Feedback</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Accessibility</a></li></ul></div><div><h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3><ul class="space-y-2"><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Terms of Service</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Privacy Policy</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">Cookie Policy</a></li><li><a href="#" class="text-gray-400 hover:text-white transition text-sm">GDPR</a></li></ul></div></div><div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center"> <p class="text-gray-500 text-sm">© <?php echo date('Y'); ?> EduPro, Inc. All rights reserved.</p><div class="mt-4 md:mt-0 flex space-x-6"><a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Sitemap</a><a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Trademark</a><a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Policies</a></div></div></div>
    </footer>

    <script>
        // --- JAVASCRIPT (Combined) ---
        document.addEventListener('DOMContentLoaded', function() {

             // --- Mobile menu toggle ---
             const mobileMenuButton = document.querySelector('.mobile-menu-button');
             const mobileMenu = document.querySelector('.mobile-menu');
             if (mobileMenuButton && mobileMenu) { /* ... Same logic ... */ mobileMenuButton.addEventListener('click', function() { mobileMenu.classList.toggle('hidden'); const icon = mobileMenuButton.querySelector('i'); if (icon) { icon.classList.toggle('fa-bars'); icon.classList.toggle('fa-times'); } }); }

             // --- Accordion functionality (if needed) ---
             document.querySelectorAll('.accordion-toggle').forEach(toggle => { /* ... Same logic ... */ toggle.addEventListener('change', function() { const icon = this.closest('.accordion-item').querySelector('.accordion-icon'); if (icon) { icon.classList.toggle('rotate-180', this.checked); } }); });

             // --- Smooth scrolling (if needed) ---
             document.querySelectorAll('a[href^="#"]').forEach(anchor => { /* ... Same logic ... */ anchor.addEventListener('click', function(e) { const targetId = this.getAttribute('href'); const targetElement = document.querySelector(targetId); if (targetElement) { e.preventDefault(); targetElement.scrollIntoView({ behavior: 'smooth' }); } }); });


            // --- Progress & Enrollment Logic ---
             const checkboxes = document.querySelectorAll('.lesson-checkbox'); // Add these to HTML
             const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 6; // Adjusted fallback (3+2+1)

             function updateProgress() { /* ... Same progress logic ... */ const progressBar = document.getElementById('progress-bar'); const progressText = document.getElementById('progress-text'); if (!progressBar || !progressText) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; const progress = totalLessonsForProgress > 0 ? Math.round((completed / totalLessonsForProgress) * 100) : 0; progressBar.style.width = progress + '%'; progressText.textContent = `${completed}/${totalLessonsForProgress} lessons completed`; progressBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-primary-600'); if (progress < 30) { progressBar.classList.add('bg-red-500'); } else if (progress < 70) { progressBar.classList.add('bg-yellow-500'); } else if (progress >= 100) { progressBar.classList.add('bg-green-500'); } else { progressBar.classList.add('bg-primary-600'); } }
             function updateCompletionBadge() { /* ... Same badge logic ... */ const completionBadge = document.getElementById('completion-badge'); if (!completionBadge) return; const completed = [...checkboxes].filter(checkbox => checkbox.checked).length; if (completed > 0) { completionBadge.textContent = completed; completionBadge.classList.remove('hidden'); } else { completionBadge.classList.add('hidden'); } }
             checkboxes.forEach(checkbox => { /* ... Same checkbox logic ... */ const lessonId = checkbox.id; const storageKey = lessonId + '_<?php safe_echo($course_slug); ?>'; const savedState = localStorage.getItem(storageKey); checkbox.checked = (savedState === 'true'); const label = checkbox.nextElementSibling; const checkIcon = label ? label.querySelector('.fa-check') : null; if (checkbox.checked) { if (checkIcon) checkIcon.classList.remove('hidden'); if (label) label.classList.add('line-through', 'text-gray-500'); } else { if (checkIcon) checkIcon.classList.add('hidden'); if (label) label.classList.remove('line-through', 'text-gray-500'); } checkbox.addEventListener('change', function() { localStorage.setItem(storageKey, this.checked); const label = this.nextElementSibling; const checkIcon = label ? label.querySelector('.fa-check') : null; if (this.checked) { if (checkIcon) checkIcon.classList.remove('hidden'); if (label) label.classList.add('line-through', 'text-gray-500'); } else { if (checkIcon) checkIcon.classList.add('hidden'); if (label) label.classList.remove('line-through', 'text-gray-500'); } updateProgress(); updateCompletionBadge(); }); });
             updateProgress(); updateCompletionBadge();


            // --- Enrollment Button Logic ---
            const enrollButton = document.getElementById('enroll-btn');
            const enrollmentActionArea = document.getElementById('enrollment-action-area'); // This is the button's container div
            const isLoggedIn = <?php echo json_encode($user_logged_in); ?>;
            const isPaidCourse = <?php echo json_encode($is_paid_course); ?>;

            if (enrollButton && enrollmentActionArea) { /* ... Same enrollment fetch logic ... */
                const enrollButtonText = enrollButton.querySelector('.btn-text'); const enrollButtonSpinner = enrollButton.querySelector('.spinner');
                enrollButton.addEventListener('click', function() { if (!isLoggedIn) { alert('Please log in or sign up to start this free course.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); return; } /* No payment check needed for free course */ const courseSlug = this.dataset.courseSlug; const button = this; button.disabled = true; if (enrollButtonText) enrollButtonText.textContent = 'Processing...'; else button.innerHTML = 'Processing...<span class="spinner ml-2"></span>'; if (enrollButtonSpinner) enrollButtonSpinner.classList.remove('hidden'); button.classList.add('opacity-60', 'cursor-wait'); const formData = new FormData(); formData.append('course_slug', courseSlug);
                fetch('enroll_course.php', { method: 'POST', body: formData })
                .then(response => { if (!response.ok) { return response.text().then(text => { throw new Error(`Server responded with status ${response.status}. Response: ${text || '(empty)'}`); }); } const contentType = response.headers.get("content-type"); if (contentType && contentType.indexOf("application/json") !== -1) { return response.json(); } else { return response.text().then(text => { throw new Error(`Unexpected response format. Expected JSON, got: ${text || '(empty)'}`); }); } })
                .then(data => { if (data.success) { console.log("Enrollment successful:", data.message); /* Update UI */ enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md"><i class="fas fa-play mr-2"></i>Continue Learning</a> <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled><i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span></button>`; } else { if (data.action === 'redirect_login') { alert('Session issue. Please log in again.'); window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); } else if (data.message && data.message.includes('already enrolled')) { console.warn("Already enrolled message:", data.message); enrollmentActionArea.innerHTML = `<a href="#curriculum-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md"><i class="fas fa-play mr-2"></i>Continue Learning</a> <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled><i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span></button>`; } else { alert('Enrollment failed: ' + (data.message || 'Unknown error.')); button.disabled = false; if (enrollButtonText) enrollButtonText.innerHTML = 'Start Free Course'; else button.innerHTML = 'Start Free Course'; if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); } } })
                .catch(error => { console.error('Enrollment fetch error:', error); alert('An error occurred during enrollment. Please try again.\nError: ' + error.message); button.disabled = false; if (enrollButtonText) enrollButtonText.innerHTML = 'Start Free Course'; else button.innerHTML = 'Start Free Course'; if (enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden'); button.classList.remove('opacity-60', 'cursor-wait'); }); });
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