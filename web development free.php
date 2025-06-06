<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require database configuration (this sets $mysqli)
require_once 'config.php'; // <--- Sets up $mysqli

// --- Course Specific Variables ---
// *** IMPORTANT: Unique Slug for this course ***
$course_slug = 'web-dev-mastery'; // Example unique slug
$course_title_display = 'Web Development Mastery'; // Title from the page
$is_paid_course = true; // Assume paid ("Professional Certificate")

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
$button_classes_extra = 'hover:bg-primary-700'; // Default hover effect

if ($is_enrolled) {
    // User is already enrolled
    $button_text = $is_paid_course ? '<i class="fas fa-check mr-2"></i>Enrolled' : '<i class="fas fa-play mr-2"></i>Continue Learning';
    $button_disabled = true;
    $button_classes_extra = 'opacity-75 cursor-not-allowed bg-green-600'; // Styling for disabled/enrolled state
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
        .lesson-checkbox + label .fa-check { display: none; } /* Hide by default */
        .progress-bar { transition: width 0.5s ease; }
        .tooltip { opacity: 0; transition: opacity 0.2s ease; pointer-events: none; position: absolute; z-index: 10; background-color: #1f2937; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; white-space: nowrap; bottom: 100%; left: 50%; transform: translateX(-50%) translateY(-5px);}
        .has-tooltip:hover .tooltip { opacity: 1; }
        .spinner { display: inline-block; border: 3px solid rgba(255,255,255,.3); border-left-color: #fff; border-radius: 50%; width: 1rem; height: 1rem; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner.hidden { display: none; }
        button:disabled, a.disabled { opacity: 0.6; cursor: not-allowed; }
        #curriculum-section { scroll-margin-top: 90px; }
        /* Dropdown styles (for potential notification/user menu) */
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

                    <!-- Notification Button with Dropdown -->
                    <div class="dropdown">
                        <button onclick="toggleDropdown('notification-dropdown')" class="p-2 rounded-full hover:bg-gray-700 transition relative">
                            <i class="fas fa-bell text-gray-400"></i>
                            <span id="notification-badge" class="notification-badge hidden">0</span> <!-- Start hidden, update with JS -->
                        </button>
                        <div id="notification-dropdown" class="dropdown-content">
                             <!-- Notification items will be loaded here dynamically or statically -->
                             <div class="p-4 border-b border-gray-700"><h3 class="font-semibold">Notifications</h3></div>
                             <div class="max-h-64 overflow-y-auto"> <p class="p-4 text-sm text-gray-400">No new notifications.</p> </div>
                             <div class="p-3 text-center border-t border-gray-700"><a href="#" class="text-sm text-primary-500 hover:underline">View all</a></div>
                        </div>
                    </div>

                    <!-- === LOGIN/SIGNUP/PROFILE AREA START === -->
                    <div class="relative flex items-center">
                         <?php if($user_logged_in): ?>
                            <button onclick="toggleDropdown('user-dashboard')" class="flex items-center space-x-2 hover:bg-gray-700 px-1 py-1 rounded-full transition relative">
                                <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" class="w-8 h-8 rounded-full">
                                <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-gray-800">0</span>
                             </button>
                             <!-- User Dashboard Dropdown -->
                             <div id="user-dashboard" class="user-dashboard dropdown-content">
                                <div class="user-header">
                                    <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" class="user-avatar">
                                    <div>
                                        <!-- Fetch actual user name/email later -->
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
                 <a href="resources.html" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Resources</a>
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
        <div class="bg-gray-800 rounded-xl p-8 mb-8 border border-gray-700">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="md:w-2/3">
                    <span class="inline-block bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-semibold mb-4">
                         <?php echo $is_paid_course ? 'PROFESSIONAL CERTIFICATE' : 'FREE COURSE'; ?>
                    </span>
                     <!-- Dynamic Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mb-4"><?php safe_echo($course_title_display); ?></h1>
                    <p class="text-lg text-gray-300 mb-6">Master modern web development with HTML, CSS, JavaScript, and popular frameworks through comprehensive lessons and real-world projects.</p>

                    <!-- Ratings/Info (Original) -->
                     <div class="flex flex-wrap items-center gap-4 mb-6"> <div class="flex items-center"> <div class="flex items-center text-yellow-400 mr-1"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div><span class="text-sm">4.7 (2,450 ratings)</span></div><span class="text-gray-400">•</span><span class="text-sm">50,000+ students enrolled</span><span class="text-gray-400">•</span><span class="text-sm">Updated March 2024</span></div>

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
                     <?php if (!$is_enrolled && !$user_logged_in && !$db_connection_error): ?>
                         <p class="text-xs text-gray-400 mt-2">
                            <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-600 hover:underline">Log in</a> or
                            <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-600 hover:underline">sign up</a>
                            to enroll<?php echo $is_paid_course ? ' in this course' : ' in this free course'; ?>.
                         </p>
                     <?php elseif ($db_connection_error): ?>
                        <p class="text-xs text-red-400 mt-2">Could not connect to enrollment service. Please try again later.</p>
                     <?php endif; ?>
                     <!-- === Enrollment Action Area END === -->

                </div>
                 <!-- Course Image (Original) -->
                 <div class="hidden md:block md:w-1/3 mt-6 md:mt-0 md:pl-6">
                    <div class="relative overflow-hidden rounded-lg shadow-lg">
                        <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="<?php safe_echo($course_title_display); ?>"
                             class="w-full h-64 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-4">
                            <button class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full hover:bg-white/30 transition relative has-tooltip">
                                <i class="fas fa-play text-white text-xl"></i>
                                 <span class="tooltip">Watch Preview</span>
                            </button>
                            <span class="ml-3 text-white font-medium">Course Preview</span>
                        </div>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Progress Section -->
        <div class="bg-gray-800 rounded-xl p-6 mb-8 border border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold flex items-center">
                    <i class="fas fa-chart-line text-primary-600 mr-3"></i>
                    Your Learning Progress
                </h2>
                <!-- Adjust total lessons (3+4 = 7 shown, original had 16?) -->
                <span id="progress-text" class="text-sm text-gray-300">0/7 lessons completed</span>
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
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8" id="curriculum-section">
                    <h2 class="text-2xl font-bold mb-6">Course Curriculum</h2>

                    <!-- Module 1 -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-primary-500">Module 1: HTML & CSS Fundamentals</h3> <span class="text-sm text-gray-400">3 lessons</span> </div>
                        <div class="space-y-3">
                            <!-- Lesson 1 - Adapted Structure -->
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center flex-grow">
                                    <input type="checkbox" id="lesson-1-wdm" class="lesson-checkbox hidden">
                                    <label for="lesson-1-wdm" class="flex items-center cursor-pointer w-full">
                                        <span class="flex-shrink-0 w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600"></i></span>
                                        <span class="truncate">HTML Basics and Semantic Structure</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="text-sm text-gray-400">22 min</span>
                                    <a href="https://www.youtube.com/watch?v=fhoDRB53DwY" class="text-primary-600 hover:text-secondary-400 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                             <!-- Lesson 2 - Adapted Structure -->
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center flex-grow">
                                    <input type="checkbox" id="lesson-2-wdm" class="lesson-checkbox hidden">
                                    <label for="lesson-2-wdm" class="flex items-center cursor-pointer w-full">
                                        <span class="flex-shrink-0 w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600"></i></span>
                                        <span class="truncate">CSS Styling and Layout Techniques</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="text-sm text-gray-400">35 min</span>
                                    <a href="https://www.youtube.com/watch?v=i1FeOOhNnwU" class="text-primary-600 hover:text-secondary-400 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                            <!-- Lesson 3 - Adapted Structure -->
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center flex-grow">
                                    <input type="checkbox" id="lesson-3-wdm" class="lesson-checkbox hidden">
                                    <label for="lesson-3-wdm" class="flex items-center cursor-pointer w-full">
                                        <span class="flex-shrink-0 w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600"></i></span>
                                        <span class="truncate">Responsive Design with Flexbox and Grid</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="text-sm text-gray-400">45 min</span>
                                    <a href="https://www.youtube.com/watch?v=3T0gjtXRNC0" class="text-primary-600 hover:text-secondary-400 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module 2 -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4"> <h3 class="text-xl font-semibold text-purple-500">Module 2: JavaScript Essentials</h3> <span class="text-sm text-gray-400">4 lessons</span> </div>
                        <div class="space-y-3">
                            <!-- Lesson 4 - Adapted Structure -->
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center flex-grow">
                                    <input type="checkbox" id="lesson-4-wdm" class="lesson-checkbox hidden">
                                    <label for="lesson-4-wdm" class="flex items-center cursor-pointer w-full">
                                        <span class="flex-shrink-0 w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600"></i></span>
                                        <span class="truncate">JavaScript Fundamentals and Syntax</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="text-sm text-gray-400">40 min</span>
                                    <a href="https://www.youtube.com/watch?v=0vL_EhRMFN0&t=200s" class="text-primary-600 hover:text-secondary-400 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                             <!-- Lesson 5 - Added Dummy Lesson -->
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center flex-grow">
                                    <input type="checkbox" id="lesson-5-wdm" class="lesson-checkbox hidden">
                                    <label for="lesson-5-wdm" class="flex items-center cursor-pointer w-full">
                                        <span class="flex-shrink-0 w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600"></i></span>
                                        <span class="truncate">DOM Manipulation</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="text-sm text-gray-400">38 min</span>
                                    <a href="https://www.youtube.com/watch?v=y17RuWkWdn8" class="text-primary-600 hover:text-secondary-400 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                            <!-- Lesson 6 - Added Dummy Lesson -->
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center flex-grow">
                                    <input type="checkbox" id="lesson-6-wdm" class="lesson-checkbox hidden">
                                    <label for="lesson-6-wdm" class="flex items-center cursor-pointer w-full">
                                        <span class="flex-shrink-0 w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600"></i></span>
                                        <span class="truncate">Asynchronous JavaScript (Callbacks, Promises)</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="text-sm text-gray-400">52 min</span>
                                    <a href="https://www.youtube.com/watch?v=670f71LTWpM" class="text-primary-600 hover:text-secondary-400 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                            <!-- Lesson 7 - Added Dummy Lesson -->
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center flex-grow">
                                    <input type="checkbox" id="lesson-7-wdm" class="lesson-checkbox hidden">
                                    <label for="lesson-7-wdm" class="flex items-center cursor-pointer w-full">
                                        <span class="flex-shrink-0 w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600"></i></span>
                                        <span class="truncate">Modern ES6+ Features</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="text-sm text-gray-400">48 min</span>
                                    <a href="https://www.youtube.com/watch?v=nZ1DMMsyVyI" class="text-primary-600 hover:text-secondary-400 text-sm relative has-tooltip"><i class="fas fa-play"></i><span class="tooltip">Start Lesson</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add more modules if needed -->
                    <div class="text-center mt-8">
                        <button class="text-primary-600 hover:text-primary-500 font-medium"> Show all 6 modules <i class="fas fa-chevron-down ml-1 text-xs"></i> </button>
                    </div>
                </div>

                 <!-- Instructor Section (Original) -->
                 <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                       <h2 class="text-2xl font-bold mb-6">About the Instructor</h2>
                       <div class="flex flex-col md:flex-row items-start"> <img src="https://randomuser.me/api/portraits/men/42.jpg" class="w-24 h-24 rounded-full object-cover mb-4 md:mb-0 md:mr-6 border-4 border-primary-600/30"> <div> <h3 class="text-xl font-bold mb-1">Dr. Michael Chen</h3> <p class="text-primary-500 mb-3">Senior Web Architect | EduPro Lead Instructor</p><p class="text-gray-300 mb-4 text-sm"> With over 15 years of industry experience...</p><div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-400"> <div class="flex items-center"><i class="fas fa-star text-yellow-400 mr-1"></i><span>4.8 Instructor Rating</span></div><div class="flex items-center"><i class="fas fa-user-graduate mr-1"></i><span>12,500 Students</span></div><div class="flex items-center"><i class="fas fa-play-circle mr-1"></i><span>8 Courses</span></div></div></div></div>
                 </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-24"> <!-- Adjusted top offset -->
                     <!-- Course Features -->
                     <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6 shadow-lg">
                           <h3 class="text-lg font-bold mb-4">This Course Includes:</h3>
                           <ul class="space-y-3 text-sm">
                               <li class="flex items-center"><i class="fas fa-video text-primary-600 w-5 text-center mr-3"></i><span>50 hours on-demand video</span></li>
                               <li class="flex items-center"><i class="fas fa-file-alt text-primary-600 w-5 text-center mr-3"></i><span>35 downloadable resources</span></li>
                               <li class="flex items-center"><i class="fas fa-mobile-alt text-primary-600 w-5 text-center mr-3"></i><span>Access on mobile and TV</span></li>
                               <li class="flex items-center"><i class="fas fa-certificate text-primary-600 w-5 text-center mr-3"></i><span>Certificate of completion</span></li>
                               <li class="flex items-center"><i class="fas fa-infinity text-primary-600 w-5 text-center mr-3"></i><span>Full lifetime access</span></li>
                           </ul>
                     </div>
                     <!-- Resources -->
                     <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6 shadow-lg">
                            <h3 class="text-lg font-bold mb-4">Resources</h3>
                            <div class="space-y-4">
                                <a href="https://www.codecademy.com/resources/cheatsheets/subject/web-development" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition"> <div class="bg-primary-600/20 text-primary-600 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-file-pdf"></i></div><div><h4 class="font-medium text-sm">Course Syllabus</h4><p class="text-xs text-gray-400">PDF • 1.2MB</p></div></a>
                                <a href="https://www.codecademy.com/resources/cheatsheets/subject/web-development" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition"> <div class="bg-purple-600/20 text-purple-500 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-code"></i></div><div><h4 class="font-medium text-sm">Starter Code Files</h4><p class="text-xs text-gray-400">ZIP • 4.5MB</p></div></a>
                                <a href="https://chat.whatsapp.com/CqW46y9Fkgh6RT09xO8ylV" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition"> <div class="bg-green-600/20 text-green-500 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center"><i class="fas fa-users"></i></div><div><h4 class="font-medium text-sm">Join Student Community</h4><p class="text-xs text-gray-400">Discord • 15K+ members</p></div></a>
                            </div>
                     </div>
                      <!-- Related Courses -->
                     <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
                          <h3 class="text-lg font-bold mb-4">Students Also Viewed</h3>
                          <div class="space-y-4">
                          <a href="AI&Machine Learning.php" class="flex items-center hover:bg-gray-700 p-3 -m-3 rounded-lg transition">
                                <img src="https://images.unsplash.com/photo-1629904853893-c2c8981a1dc5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80"
                                     class="w-16 h-12 rounded-lg object-cover mr-3 flex-shrink-0">
                                <div>
                                    <h4 class="font-medium text-sm leading-tight">Master AI & ML fundamentals</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1">
                                        <i class="fas fa-star text-yellow-400 mr-1 text-xs"></i>
                                        <span>4.8 • 10.5k students</span>
                                    </div>
                                </div>
                            </a>
                             <a href="Data Science free.php" class="flex items-center hover:bg-gray-700 p-3 -m-3 rounded-lg transition">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80"
                                     class="w-16 h-12 rounded-lg object-cover mr-3 flex-shrink-0">
                                <div>
                                    <h4 class="font-medium text-sm leading-tight">Data Science Bootcamp</h4>
                                    <div class="flex items-center text-xs text-gray-400 mt-1">
                                        <i class="fas fa-star text-yellow-400 mr-1 text-xs"></i>
                                        <span>4.7 • 8.7k students</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 py-12">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"> <div class="grid grid-cols-1 md:grid-cols-4 gap-8"> <div> <div class="flex items-center mb-4"><i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i><span class="text-xl font-bold">EduPro</span></div><p class="text-gray-400 mb-4 text-sm">Advancing careers through world-class digital education.</p><div class="flex space-x-4"><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a><a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-youtube"></i></a></div></div><div><h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Company</h3><ul class="space-y-2"><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">About Us</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Careers</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Blog</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Press</a></li></ul></div><div><h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3><ul class="space-y-2"><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Help Center</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Contact Us</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Feedback</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Accessibility</a></li></ul></div><div><h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3><ul class="space-y-2"><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Terms of Service</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Privacy Policy</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">Cookie Policy</a></li><li><a href="#" class="text-gray-400 text-sm hover:text-white transition">GDPR</a></li></ul></div></div><div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center"> <p class="text-gray-500 text-sm">© <?php echo date("Y"); ?> EduPro, Inc. All rights reserved.</p><div class="mt-4 md:mt-0 flex space-x-6"><a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Sitemap</a><a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Trademark</a><a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Policies</a></div></div></div>
    </footer>

    <script>
        // --- JAVASCRIPT (Combined) ---
        document.addEventListener('DOMContentLoaded', function() {

             // --- Mobile menu toggle ---
             const mobileMenuButton = document.querySelector('.mobile-menu-button');
             const mobileMenu = document.querySelector('.mobile-menu');
             if (mobileMenuButton && mobileMenu) { /* ... Same logic ... */ mobileMenuButton.addEventListener('click', function() { mobileMenu.classList.toggle('hidden'); const icon = mobileMenuButton.querySelector('i'); if (icon) { icon.classList.toggle('fa-bars'); icon.classList.toggle('fa-times'); } }); }

             // --- Dropdown Toggles (Notifications & User) ---
             window.toggleDropdown = function(dropdownId) {
                 const dropdown = document.getElementById(dropdownId);
                 if (dropdown) {
                     // Close other dropdowns first
                     const otherDropdowns = document.querySelectorAll('.dropdown-content, .user-dashboard');
                     otherDropdowns.forEach(od => {
                         if (od.id !== dropdownId) {
                             od.classList.remove('show');
                         }
                     });
                     // Toggle the target dropdown
                     dropdown.classList.toggle('show');
                 }
                  // Hide notification badge if that dropdown is opened
                 if (dropdownId === 'notification-dropdown' && dropdown.classList.contains('show')) {
                    const badge = document.getElementById('notification-badge');
                    if(badge) badge.classList.add('hidden'); // Use hidden class
                 }
             }
             // Close dropdowns when clicking outside
             window.onclick = function(event) {
                 if (!event.target.closest('.dropdown') && !event.target.closest('.relative > button') ) { // Check if click is outside dropdown triggers
                     const dropdowns = document.querySelectorAll('.dropdown-content, .user-dashboard');
                     dropdowns.forEach(dropdown => {
                         dropdown.classList.remove('show');
                     });
                 }
             }

             // --- Accordion functionality (if needed) ---
             document.querySelectorAll('.accordion-toggle').forEach(toggle => { /* ... Same logic ... */ toggle.addEventListener('change', function() { const icon = this.closest('.accordion-item').querySelector('.accordion-icon'); if (icon) { icon.classList.toggle('rotate-180', this.checked); } }); });

             // --- Smooth scrolling (if needed) ---
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