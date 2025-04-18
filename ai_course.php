<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require database configuration (this sets $mysqli)
require_once 'config.php'; // <--- Sets up $mysqli

// --- Course Specific Variables ---
$course_slug = 'ai-fundamentals-free';
$course_title_display = 'Artificial Intelligence Fundamentals';
$is_paid_course = false;

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
        // No need to close $mysqli here, let the script end or other parts use it.
    } else {
        // === Use $mysqli ===
        $db_error = isset($mysqli) && $mysqli instanceof mysqli ? $mysqli->connect_error : '$mysqli variable not set or not a mysqli object';
        error_log("Database connection error in ai_course.php for enrollment check: " . $db_error);
    }
}

// --- Utility Function (Original) ---
function safe_echo($str) {
    echo htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        /* Custom CSS styles (Original) */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .course-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            border-color: rgba(59, 130, 246, 0.5);
        }

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
         #course-content-section { scroll-margin-top: 90px; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <!-- Left Side Nav -->
                <div class="flex items-center space-x-4">
                    <a href="Home.php" class="flex items-center">
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl"></i>
                        <span class="ml-2 text-xl font-bold">EduPro</span>
                    </a>
                    <div class="hidden md:flex space-x-6">
                        <a href="courses_list.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Courses</a>
        
                        <a href="resources.html" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Resources</a>
                    </div>
                </div>
                <!-- Right Side Nav -->
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip">
                        <i class="fas fa-search text-gray-400"></i>
                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                            Search courses
                        </span>
                    </button>
                    <button class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip">
                        <i class="fas fa-bell text-gray-400"></i>
                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                            Notifications
                        </span>
                    </button>

                    <!-- === PROFILE PICTURE/LINK AREA START === -->
                    <div class="relative">
                        <?php if ($user_logged_in): ?>
                            <a href="Dashboard.php" class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-2 rounded-full transition" title="Go to Dashboard">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-8 h-8 rounded-full">
                                <!-- Completion badge only shown if logged in -->
                                <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                            </a>
                        <?php else: ?>
                            <!-- If not logged in, link the same styled element to Login -->
                            <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-2 rounded-full transition" title="Login">
                                 <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-8 h-8 rounded-full">
                                 <!-- No completion badge -->
                            </a>
                        <?php endif; ?>
                    </div>
                    <!-- === PROFILE PICTURE/LINK AREA END === -->

                </div>
                 <!-- Mobile Menu Button -->
                 <div class="md:hidden">
                     <button class="mobile-menu-button p-2 rounded-md hover:bg-gray-700 focus:outline-none">
                         <i class="fas fa-bars text-white text-xl"></i>
                     </button>
                 </div>
            </div>
             <!-- Mobile Menu Content -->
             <div class="mobile-menu hidden md:hidden bg-gray-800 mt-2 pb-3 space-y-1">
                 <a href="courses_list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Courses</a>
                 <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Programs</a>
                 <!-- Add other mobile links as needed -->
             </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Course Header Section (Original) -->
        <div class="bg-gray-800 rounded-xl p-8 mb-8 border border-gray-700">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="md:w-2/3">
                    <span class="inline-block bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-semibold mb-4">
                        PROFESSIONAL CERTIFICATE
                    </span>
                    <h1 class="text-3xl md:text-4xl font-bold mb-4"><?php safe_echo($course_title_display); ?></h1>
                    <p class="text-lg text-gray-300 mb-6">Master the core concepts of AI and machine learning through comprehensive lessons, hands-on projects, and real-world applications.</p>

                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex items-center text-yellow-400 mr-1">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="text-sm">4.8 (3,120 ratings)</span>
                        </div>
                        <span class="text-gray-400">•</span>
                        <span class="text-sm">65,000+ students enrolled</span>
                        <span class="text-gray-400">•</span>
                        <span class="text-sm">Updated April 2024</span>
                    </div>

                    <!-- Enrollment Action Area START (Original) -->
                    <div class="flex items-center space-x-4" id="enrollment-action-area">
                        <?php if ($is_enrolled): ?>
                             <a href="#course-content-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md">
                                 <i class="fas fa-play-circle mr-2"></i>Continue Learning
                             </a>
                             <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled>
                                 <i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled
                                 <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span>
                             </button>
                        <?php else: ?>
                             <button id="enroll-btn" data-course-slug="<?php safe_echo($course_slug); ?>" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md">
                                 <span class="btn-text">Enroll Now</span>
                                 <span class="spinner hidden ml-2"></span>
                             </button>
                             <button class="flex items-center text-gray-300 hover:text-white transition group has-tooltip">
                                 <i class="far fa-heart mr-2"></i> Save
                                 <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Save (coming soon)</span>
                             </button>
                        <?php endif; ?>
                    </div>
                    <?php if (!$is_paid_course && !$is_enrolled && !$user_logged_in): ?>
                        <p class="text-xs text-gray-400 mt-3">
                           <a href="Login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-500 hover:underline">Log in</a> or
                           <a href="Signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-primary-500 hover:underline">sign up</a>
                           to enroll in this free course.
                        </p>
                    <?php endif; ?>
                    <!-- Enrollment Action Area END -->

                </div>
                <div class="hidden md:block md:w-1/3 mt-6 md:mt-0">
                    <div class="relative overflow-hidden rounded-lg">
                        <img src="https://images.unsplash.com/photo-1620712943543-bcc4688e7485?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="AI Course"
                             class="w-full h-64 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-4">
                            <button class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full hover:bg-white/30 transition">
                                <a href="https://www.youtube.com/watch?v=Yq0QkCxoTHM"><i class="fas fa-play text-white"></i></a>
                            </button>
                            <span class="ml-3 text-white font-medium">Course Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar (Original) -->
        <div class="bg-gray-800 rounded-xl p-6 mb-8 border border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold flex items-center">
                    <i class="fas fa-chart-line text-primary-600 mr-3"></i>
                    Your Learning Progress
                </h2>
                <span id="progress-text" class="text-sm text-gray-300">0/18 lessons completed</span>
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

        <!-- Main Content Grid (Original) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="lg:col-span-2">
                <!-- Course Curriculum Section (Original) -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8" id="course-content-section">
                    <h2 class="text-2xl font-bold mb-6">Course Curriculum</h2>

                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-primary-500">Module 1: Introduction to AI</h3>
                            <span class="text-sm text-gray-400">3 lessons</span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-1" class="lesson-checkbox hidden">
                                    <label for="lesson-1" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>What is Artificial Intelligence?</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">25 min</span>
                                    <a href="https://www.youtube.com/watch?v=ad79nYk2keg" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-2" class="lesson-checkbox hidden">
                                    <label for="lesson-2" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>History and Evolution of AI</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">30 min</span>
                                    <a href="https://www.youtube.com/watch?v=3qRJfUv7W_Y" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-3" class="lesson-checkbox hidden">
                                    <label for="lesson-3" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Types of AI: Narrow vs General</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">35 min</span>
                                    <a href="https://www.youtube.com/watch?v=hQBrL9UxBrM" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                         <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-purple-500">Module 2: Machine Learning Basics</h3>
                            <span class="text-sm text-gray-400">3 lessons</span>
                        </div>
                        <div class="space-y-3">
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-4" class="lesson-checkbox hidden">
                                    <label for="lesson-4" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Supervised vs Unsupervised Learning</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">40 min</span>
                                    <a href="https://www.youtube.com/watch?v=fM8XdC1EweU" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-5" class="lesson-checkbox hidden">
                                    <label for="lesson-5" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Regression Models</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">45 min</span>
                                    <a href="https://www.youtube.com/watch?v=cHT-qLnRm0E&t=101s" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-6" class="lesson-checkbox hidden">
                                    <label for="lesson-6" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Classification Algorithms</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">50 min</span>
                                    <a href="https://www.youtube.com/watch?v=O1nWXTXcCwI&t=56s" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="mb-8">
                         <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-green-500">Module 3: Deep Learning</h3>
                             <span class="text-sm text-gray-400">12 lessons</span>
                        </div>
                        <div class="space-y-3">
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-7" class="lesson-checkbox hidden">
                                    <label for="lesson-7" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Neural Networks Fundamentals</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">55 min</span>
                                    <a href="https://www.youtube.com/watch?v=EYeF2e2IKEo" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-8" class="lesson-checkbox hidden">
                                    <label for="lesson-8" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Convolutional Neural Networks</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">60 min</span>
                                    <a href="https://www.youtube.com/watch?v=QzY57FaENXg" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                             <!-- Placeholder Lessons 9-18 (Original) -->
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"><input type="checkbox" id="lesson-9" class="lesson-checkbox hidden"><label for="lesson-9" class="flex items-center cursor-pointer"><span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span><span>Recurrent Neural Networks (RNNs)</span></label></div><div class="flex items-center space-x-3"><span class="text-sm text-gray-400">45 min</span><a href="#" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start</span></a></div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"><input type="checkbox" id="lesson-10" class="lesson-checkbox hidden"><label for="lesson-10" class="flex items-center cursor-pointer"><span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span><span>Introduction to NLP</span></label></div><div class="flex items-center space-x-3"><span class="text-sm text-gray-400">50 min</span><a href="#" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start</span></a></div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"><input type="checkbox" id="lesson-11" class="lesson-checkbox hidden"><label for="lesson-11" class="flex items-center cursor-pointer"><span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span><span>Computer Vision Basics</span></label></div><div class="flex items-center space-x-3"><span class="text-sm text-gray-400">55 min</span><a href="#" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start</span></a></div></div>
                             <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600"> <div class="flex items-center"><input type="checkbox" id="lesson-12" class="lesson-checkbox hidden"><label for="lesson-12" class="flex items-center cursor-pointer"><span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition"><i class="fas fa-check text-xs text-primary-600 hidden"></i></span><span>Generative AI Overview</span></label></div><div class="flex items-center space-x-3"><span class="text-sm text-gray-400">40 min</span><a href="#" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip relative"><i class="fas fa-play"></i><span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">Start</span></a></div></div>
                             
                        </div>
                    </div>

                    <div class="text-center mt-8">
                        <button class="text-primary-600 hover:text-primary-500 font-medium">
                            Show all 6 modules <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- About the Instructor Section (Original) -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                    <h2 class="text-2xl font-bold mb-6">About the Instructor</h2>

                    <div class="flex flex-col md:flex-row items-start">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" class="w-24 h-24 rounded-full object-cover mb-4 md:mb-0 md:mr-6 border-4 border-primary-600/30 flex-shrink-0">
                        <div>
                            <h3 class="text-xl font-bold mb-1">Dr. Sarah Johnson</h3>
                            <p class="text-primary-500 mb-3">AI Research Scientist | Former Google Brain</p>
                            <p class="text-gray-300 mb-4">
                                With a PhD in Machine Learning from Stanford and 10+ years of industry experience, Dr. Johnson has published over 50 papers in top AI conferences.
                                She specializes in making complex AI concepts accessible to beginners while providing depth for advanced learners.
                            </p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-400">
                                <div class="flex items-center">
                                    <i class="fas fa-star text-yellow-400 mr-1"></i>
                                    <span>4.9 Instructor Rating</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>18,200 Students</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-play-circle mr-1"></i>
                                    <span>6 Courses</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Sidebar (Original) -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                        <h3 class="text-lg font-bold mb-4">This Course Includes:</h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-center">
                                <i class="fas fa-video text-primary-600 mr-3 w-5 text-center"></i>
                                <span>60 hours on-demand video</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-file-alt text-primary-600 mr-3 w-5 text-center"></i>
                                <span>45 downloadable resources</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-laptop-code text-primary-600 mr-3 w-5 text-center"></i>
                                <span>25 coding exercises</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-certificate text-primary-600 mr-3 w-5 text-center"></i>
                                <span>Certificate of completion</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-infinity text-primary-600 mr-3 w-5 text-center"></i>
                                <span>Full lifetime access</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                         <h3 class="text-lg font-bold mb-4">Resources</h3>
                        <div class="space-y-3">
                            <a href="https://sabiod.lis-lab.fr/pub/machinelearningAIDeep_resume.pdf" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition">
                                <div class="bg-primary-600/20 text-primary-600 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm">AI Cheat Sheets</h4>
                                    <p class="text-xs text-gray-400">PDF • 2.5MB</p>
                                </div>
                            </a>
                             <a href="https://sabiod.lis-lab.fr/pub/machinelearningAIDeep_resume.pdf" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition">
                                <div class="bg-purple-600/20 text-purple-600 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm">Sample Datasets</h4>
                                    <p class="text-xs text-gray-400">ZIP • 15.2MB</p>
                                </div>
                            </a>
                             <a href="https://chat.whatsapp.com/CqW46y9Fkgh6RT09xO8ylV" class="flex items-center p-3 -m-3 hover:bg-gray-700 rounded-lg transition">
                                <div class="bg-green-600/20 text-green-600 p-2 rounded-lg mr-3 flex-shrink-0 w-10 h-10 flex items-center justify-center">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm">Join AI Community</h4>
                                    <p class="text-xs text-gray-400">Discord • 22K+ members</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
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


    <!-- Footer (Original) -->
    <footer class="bg-gray-800 border-t border-gray-700 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduPro</span>
                    </div>
                    <p class="text-gray-400 text-sm mb-4">Advancing careers through world-class digital education.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Press</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Contact Us</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Feedback</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Accessibility</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">Cookie Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition">GDPR</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">© <?php echo date('Y'); ?> EduPro, Inc. All rights reserved.</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Sitemap</a>
                    <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Trademark</a>
                    <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Policies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // --- JAVASCRIPT (Original structure, using fetch) ---
        document.addEventListener('DOMContentLoaded', function() {

            const checkboxes = document.querySelectorAll('.lesson-checkbox');
            const totalLessonsForProgress = checkboxes.length > 0 ? checkboxes.length : 18; // Fallback to 18

            // --- Progress & Checkbox Handling (Original Logic) ---
            function updateProgress() {
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');
                const completed = [...checkboxes].filter(checkbox => checkbox.checked).length;
                const progress = totalLessonsForProgress > 0 ? Math.round((completed / totalLessonsForProgress) * 100) : 0;

                if (progressBar) progressBar.style.width = progress + '%';
                if (progressText) progressText.textContent = `${completed}/${totalLessonsForProgress} lessons completed`;

                 if (progressBar) {
                     progressBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-primary-600');
                     if (progress < 30) { progressBar.classList.add('bg-red-500'); }
                     else if (progress < 70) { progressBar.classList.add('bg-yellow-500'); }
                     else if (progress >= 100) { progressBar.classList.add('bg-green-500'); }
                     else { progressBar.classList.add('bg-primary-600'); }
                 }
            }

            function updateCompletionBadge() {
                const completed = [...checkboxes].filter(checkbox => checkbox.checked).length;
                const completionBadge = document.getElementById('completion-badge');

                // Only attempt to update if the badge exists (it might not if user is not logged in)
                if (completionBadge) {
                    if (completed > 0) {
                        completionBadge.textContent = completed;
                        completionBadge.classList.remove('hidden');
                    } else {
                        completionBadge.classList.add('hidden');
                    }
                }
            }

            checkboxes.forEach(checkbox => {
                const lessonId = checkbox.id;
                const storageKey = lessonId + '_<?php safe_echo($course_slug); ?>';
                const savedState = localStorage.getItem(storageKey);

                if (savedState === 'true') { checkbox.checked = true; }
                else { checkbox.checked = false; }

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

            updateProgress();
            updateCompletionBadge(); // Update badge on initial load


            // --- Enrollment Button Logic (Original structure, using fetch) ---
            const enrollButton = document.getElementById('enroll-btn');
            const enrollmentActionArea = document.getElementById('enrollment-action-area');
            const isLoggedIn = <?php echo json_encode($user_logged_in); ?>;

            if (enrollButton && enrollmentActionArea) {
                const enrollButtonText = enrollButton.querySelector('.btn-text');
                const enrollButtonSpinner = enrollButton.querySelector('.spinner');

                enrollButton.addEventListener('click', function() {
                    // Check login status again just before fetch
                    if (!isLoggedIn) {
                        alert('Please log in or sign up to enroll in this course.');
                        window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                        return;
                    }
                    const courseSlug = this.dataset.courseSlug;
                    const button = this;

                    button.disabled = true;
                    enrollButtonText.textContent = 'Enrolling...';
                    if(enrollButtonSpinner) enrollButtonSpinner.classList.remove('hidden');
                    button.classList.add('opacity-60', 'cursor-wait');

                    const formData = new FormData();
                    formData.append('course_slug', courseSlug);

                    fetch('enroll_course.php', { method: 'POST', body: formData })
                    .then(response => {
                        // More robust checking of response
                        if (!response.ok) {
                             // Try to get text even for errors, helps debugging
                             return response.text().then(text => { throw new Error(`Server responded with status ${response.status}. Response: ${text || '(empty)'}`); });
                        }
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return response.json();
                        } else {
                            // If backend doesn't send JSON header correctly, we still get an error
                             return response.text().then(text => { throw new Error(`Unexpected response format. Expected JSON, got: ${text || '(empty)'}`); });
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            console.log("Enrollment successful:", data.message);
                            enrollmentActionArea.innerHTML = `
                                <a href="#course-content-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md">
                                    <i class="fas fa-play-circle mr-2"></i>Continue Learning
                                </a>
                                 <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled>
                                     <i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled
                                     <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are now enrolled</span>
                                 </button>
                             `;
                        } else {
                            // Handle specific backend failures
                            if (data.action === 'redirect_login') {
                                alert('Session issue. Please log in again.');
                                window.location.href = 'Login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                            } else if (data.message && data.message.includes('already enrolled')) {
                                 console.warn("Already enrolled message:", data.message);
                                  enrollmentActionArea.innerHTML = `
                                    <a href="#course-content-section" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center text-base shadow-md"> <i class="fas fa-play-circle mr-2"></i>Continue Learning </a>
                                    <button class="flex items-center text-gray-300 transition group has-tooltip disabled" disabled> <i class="fas fa-check-circle mr-2 text-green-500"></i> Enrolled <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">You are enrolled</span> </button> `;
                            } else {
                                // General failure message from backend
                                alert('Enrollment failed: ' + (data.message || 'Unknown error.'));
                                button.disabled = false;
                                enrollButtonText.textContent = 'Enroll Now'; // Reset button text
                                if(enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden');
                                button.classList.remove('opacity-60', 'cursor-wait');
                            }
                        }
                    })
                    .catch(error => {
                        // Handle network/fetch errors or parsing errors
                        console.error('Enrollment fetch error:', error); // Log the actual error object
                        alert('An error occurred while enrolling. Please try again.\nError: ' + error.message);
                        button.disabled = false;
                        enrollButtonText.textContent = 'Enroll Now'; // Reset button text
                        if(enrollButtonSpinner) enrollButtonSpinner.classList.add('hidden');
                        button.classList.remove('opacity-60', 'cursor-wait');
                    });
                });
            } else {
                 console.log("Enrollment button or action area not found.");
            }


            // --- Mobile Menu Toggle (Original) ---
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');
            if (mobileMenuButton && mobileMenu) {
              mobileMenuButton.addEventListener('click', function() {
                  mobileMenu.classList.toggle('hidden');
                  const icon = mobileMenuButton.querySelector('i');
                  if (icon) {
                      icon.classList.toggle('fa-bars');
                      icon.classList.toggle('fa-times');
                  }
              });
            }

        });
    </script>

</body>
</html>
<?php
// Close the database connection if it was opened and is still open
// === Use $mysqli ===
if (isset($mysqli) && $mysqli instanceof mysqli && !$mysqli->connect_error && $mysqli->thread_id) {
   mysqli_close($mysqli);
}
?>