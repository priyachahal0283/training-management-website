<?php
// File: Dashboard.php

// ---- SESSION START MUST BE ABSOLUTELY FIRST ----
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ---- END SESSION START ----

// ---- REDIRECT IF *NOT* LOGGED IN ----
// Check if the user is logged in, otherwise redirect to Login.php
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: Login.php"); // Redirect to login page
    exit; // Stop executing Dashboard.php code
}
// ---- END REDIRECT ----

// --- User IS logged in if script reaches here ---

// Include config file AFTER login check
require_once 'config.php';

// --- VERIFY DATABASE CONNECTION OBJECT ---
// Check if config.php successfully created the $mysqli object
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    // Log the error, don't show details to the user
    error_log("Dashboard Error: config.php did not create a valid \$mysqli object.");
    // Display a user-friendly error and stop execution
    die("Error: Database configuration issue. Please contact support.");
}
// --- END DB CONNECTION VERIFY ---


// --- Initialize Profile Variables with Sensible Defaults ---
$user_id = $_SESSION["id"] ?? 0;
$user_email = htmlspecialchars($_SESSION["email"] ?? 'N/A');
$profile_name = htmlspecialchars($_SESSION["name"] ?? $user_email); // Default to email if name isn't in session
$profile_title = 'Member';
$profile_phone = ''; // Use empty string instead of 'N/A' for easier editing in modal
$profile_address = '';
$profile_joined = date('F j, Y'); // Fallback if DB fetch fails
$profile_bio = '';
$profile_image_url = null;
$skills_array = []; // Initialize as empty array
$display_initial = '?'; // Default initial
if ($profile_name !== 'N/A' && !empty($profile_name)) {
    $display_initial = strtoupper(substr($profile_name, 0, 1));
}


// --- Fetch User Profile Data from Database ---
if ($user_id > 0) { // Proceed only if user ID is valid
    $sql_fetch = "SELECT name, title, phone, address, created_at, bio, skills_json, profile_image_path FROM users WHERE id = ?";
    if ($stmt_fetch = $mysqli->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $user_id);

        if ($stmt_fetch->execute()) {
            $stmt_fetch->bind_result($db_name, $db_title, $db_phone, $db_address, $db_created_at, $db_bio, $db_skills_json, $db_image_path);

            if ($stmt_fetch->fetch()) {
                // Update profile variables ONLY if DB value is not empty/null
                // Prioritize DB name over session name if available
                $profile_name = !empty($db_name) ? htmlspecialchars($db_name) : $profile_name;
                $profile_title = !empty($db_title) ? htmlspecialchars($db_title) : $profile_title;
                $profile_phone = !empty($db_phone) ? htmlspecialchars($db_phone) : ''; // Use empty string if null/empty
                $profile_address = !empty($db_address) ? htmlspecialchars($db_address) : ''; // Use empty string if null/empty
                $profile_joined = !empty($db_created_at) ? date('F j, Y', strtotime($db_created_at)) : $profile_joined;
                $profile_bio = !empty($db_bio) ? htmlspecialchars($db_bio) : ''; // Use empty string if null/empty

                // Safely decode skills JSON
                if (!empty($db_skills_json)) {
                    $decoded_skills = json_decode($db_skills_json, true);
                    if (is_array($decoded_skills)) {
                        $skills_array = $decoded_skills;
                    }
                }

                 // Update display initial based on potentially updated name
                 if ($profile_name !== 'N/A' && !empty($profile_name)) {
                    $display_initial = strtoupper(substr($profile_name, 0, 1));
                 }

                // Set image URL if path exists and the file is accessible
                if (!empty($db_image_path) && file_exists($db_image_path) && is_readable($db_image_path)) {
                    // Add timestamp to prevent browser caching issues after upload
                    $profile_image_url = htmlspecialchars($db_image_path) . '?t=' . filemtime($db_image_path);
                }
            }
        } else {
            error_log("Dashboard Error (Fetch Profile Execute): " . $stmt_fetch->error);
            // Don't die, just use default profile values initialized earlier
        }
        $stmt_fetch->close();
    } else {
        error_log("Dashboard Error (Fetch Profile Prepare): " . $mysqli->error);
        // Don't die, just use default profile values initialized earlier
    }
} else {
    error_log("Dashboard Error: Invalid user ID ($user_id) in session.");
    // Potentially redirect or show an error if ID is necessary and invalid
}


// --- Fetch Enrolled Courses ---
$enrolled_courses = [];
if ($user_id > 0) {
    // Ensure 'user_courses' table exists with 'user_id', 'course_slug', 'enrollment_date' columns
    $sql_get_courses = "SELECT course_slug, enrollment_date FROM user_courses WHERE user_id = ? ORDER BY enrollment_date DESC";
    if ($stmt_get_courses = $mysqli->prepare($sql_get_courses)) {
        $stmt_get_courses->bind_param("i", $user_id);
        if ($stmt_get_courses->execute()) {
            $result_courses = $stmt_get_courses->get_result();
            while ($row = $result_courses->fetch_assoc()) {
                $enrolled_courses[] = $row; // Add each enrolled course to the array
            }
            $result_courses->free();
        } else {
            error_log("Dashboard Error (Fetch Courses Execute): " . $stmt_get_courses->error);
        }
        $stmt_get_courses->close();
    } else {
        error_log("Dashboard Error (Fetch Courses Prepare): " . $mysqli->error);
    }
}

// --- Course Details Mapping (Ensure slugs and URLs are correct) ---
$course_details_map = [
    'react_masterclass' => ['title' => 'React.js Masterclass', 'image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 'url' => 'react_course_page.php'],
    'ai-fundamentals-free' => ['title' => 'Artificial Intelligence Fundamentals', 'image' => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 'url' => 'ai_course.php'],
    'javascript-mastery-free' => ['title' => 'Modern JavaScript Mastery', 'image' => 'https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 'url' => 'js_course.php'],
    'data-science-python-free' => ['title' => 'Data Science with Python', 'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 'url' => 'ds_py_course.php'],
    'web-development-bootcamp' => ['title' => 'The Complete Web Development Bootcamp', 'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 'url' => 'web_dev_course.php'],
    // Add ALL other potential course slugs and their details here
];

// Note: Database connection ($mysqli) is NOT explicitly closed here.
// PHP usually closes it automatically at the end of script execution.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $profile_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                         primary: { DEFAULT: '#2563eb', 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a', 950: '#172554'},
                         secondary: { DEFAULT: '#1d4ed8', 700:'#1d4ed8' }, // Adjusted secondary
                         accent: { DEFAULT: '#3b82f6', 500:'#3b82f6' },
                         dark: { DEFAULT: '#1e3a8a', 800: '#1e293b', 900: '#0f172a'}
                    }
                }
            }
        }
    </script>
    <style>
        #edit-modal ::-webkit-scrollbar { width: 6px; height: 6px; }
        #edit-modal ::-webkit-scrollbar-track { background:rgb(0, 0, 0); border-radius: 10px; }
        #edit-modal ::-webkit-scrollbar-thumb { background:rgb(23, 24, 26); border-radius: 10px; }
        #edit-modal ::-webkit-scrollbar-thumb:hover { background:rgb(27, 29, 31); }
        .spinner { border: 3px solid rgba(237, 222, 222, 0.72); border-left-color: #fff; border-radius: 50%; width: 1.1rem; height: 1.1rem; animation: spin 1s linear infinite; display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
        /* Hide image if src is invalid or empty */
        #profile-image[src=""], #profile-image-preview[src=""],
        #profile-image:not([src]), #profile-image-preview:not([src]) { display: none !important; }
        /* CSS to manage display based on PHP variable */
        .initials-display { <?php echo $profile_image_url ? 'display: none;' : 'display: flex;'; ?> }
        .image-display { <?php echo $profile_image_url ? 'display: block;' : 'display: none;'; ?> }
        /* Fade-in animation for skills */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <header class="flex flex-wrap justify-between items-center mb-8 gap-4 border-b pb-4 border-gray-200">
             <div>
                 <h1 class="text-2xl font-bold text-gray-800">User Dashboard</h1>
                 <nav class="flex text-sm text-gray-500 mt-1" aria-label="Breadcrumb">
                     <a href="Home.php" class="hover:text-primary-600">Home</a>
                     <span class="mx-2" aria-hidden="true">></span>
                     <span class="text-primary-600 font-medium" aria-current="page">Dashboard</span>
                 </nav>
             </div>
             <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition flex items-center text-sm shadow-sm hover:shadow">
                 <i class="fas fa-sign-out-alt mr-2"></i> Logout
             </a>
        </header>

        <!-- Main Content -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Column -->
            <div class="w-full lg:w-1/3 lg:sticky lg:top-8 self-start space-y-6">
                <!-- Profile Card -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="relative bg-gradient-to-r from-primary-600 to-accent-500 h-32 flex items-center justify-center">
                        <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                             <div class="relative group">
                                 <img id="profile-image" src="<?php echo $profile_image_url ?? ''; ?>" alt="Profile Image" class="w-24 h-24 rounded-full border-4 border-white object-cover shadow-lg bg-gray-300 image-display">
                                 <div id="profile-initial-alt" class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-primary-600 text-white flex items-center justify-center text-4xl font-bold initials-display">
                                     <?php echo $display_initial; ?>
                                 </div>
                                 <button id="change-image-btn" aria-label="Change profile picture" class="absolute bottom-0 right-0 bg-primary-600 text-white p-2 rounded-full hover:bg-secondary-700 transition shadow-md transform hover:scale-110 opacity-0 group-hover:opacity-100 focus:opacity-100"><i class="fas fa-camera text-xs"></i></button>
                                 <input type="file" id="profile-image-input" accept="image/png, image/jpeg, image/gif, image/webp" class="hidden">
                             </div>
                        </div>
                    </div>
                    <div class="pt-16 pb-6 px-6 text-center">
                        <h2 id="profile-name" class="text-xl font-semibold text-gray-800 break-words"><?php echo $profile_name; ?></h2>
                        <p id="profile-title" class="text-primary-600 text-sm font-medium mb-4"><?php echo $profile_title; ?></p>
                        <div class="flex justify-center space-x-6 mb-6 text-center">
                             <div><p class="text-2xl font-bold text-gray-800"><?php echo count($enrolled_courses); ?></p><p class="text-xs text-gray-500 uppercase tracking-wide">Courses</p></div>
                            <div><p class="text-2xl font-bold text-gray-800">0</p><p class="text-xs text-gray-500 uppercase tracking-wide">Points</p></div>
                            <div><p class="text-2xl font-bold text-gray-800">0</p><p class="text-xs text-gray-500 uppercase tracking-wide">Badges</p></div>
                        </div>
                        <button id="edit-profile-btn" class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-secondary-700 transition font-medium shadow-sm hover:shadow">Edit Profile</button>
                    </div>
                </div>

                 <!-- Contact Info -->
                 <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                     <h3 class="font-semibold text-gray-800 mb-4 flex items-center"><i class="fas fa-address-card mr-2 text-primary-600"></i> Contact Information</h3>
                     <div class="space-y-3 text-sm">
                         <div class="flex items-start"><i class="fas fa-envelope mt-1 mr-3 text-primary-600 w-4 text-center flex-shrink-0"></i><div><p class="text-xs text-gray-500">Email</p><p id="profile-email" class="text-gray-700 break-all"><?php echo $user_email; ?></p></div></div>
                         <div class="flex items-start"><i class="fas fa-phone mt-1 mr-3 text-primary-600 w-4 text-center flex-shrink-0"></i><div><p class="text-xs text-gray-500">Phone</p><p id="profile-phone" class="text-gray-700"><?php echo !empty($profile_phone) ? $profile_phone : '<span class="text-gray-400 italic">Not provided</span>'; ?></p></div></div>
                         <div class="flex items-start"><i class="fas fa-map-marker-alt mt-1 mr-3 text-primary-600 w-4 text-center flex-shrink-0"></i><div><p class="text-xs text-gray-500">Address</p><p id="profile-address" class="text-gray-700"><?php echo !empty($profile_address) ? $profile_address : '<span class="text-gray-400 italic">Not provided</span>'; ?></p></div></div>
                         <div class="flex items-start"><i class="fas fa-calendar-alt mt-1 mr-3 text-primary-600 w-4 text-center flex-shrink-0"></i><div><p class="text-xs text-gray-500">Joined Date</p><p id="profile-joined" class="text-gray-700"><?php echo $profile_joined; ?></p></div></div>
                     </div>
                 </div>

                 <!-- Skills Card -->
                 <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                     <h3 class="font-semibold text-gray-800 mb-4 flex items-center"><i class="fas fa-star mr-2 text-primary-600"></i> Skills & Expertise</h3>
                     <div class="flex flex-wrap gap-2 min-h-[24px]" id="skills-container"> <!-- Added min-height -->
                         <?php if (empty($skills_array)): ?>
                             <span class="text-gray-500 text-xs italic">Click 'Edit Profile' to add skills.</span>
                         <?php else: ?>
                             <?php foreach ($skills_array as $skill): ?>
                                 <span class="bg-blue-100 text-primary-700 text-xs font-medium px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($skill); ?></span>
                             <?php endforeach; ?>
                         <?php endif; ?>
                     </div>
                     <button id="add-skill-btn" class="mt-3 text-primary-600 text-sm font-medium hover:text-secondary-700 flex items-center"><i class="fas fa-plus mr-1 text-xs"></i> Add/Edit Skills</button>
                 </div>
            </div>

             <!-- Right Column -->
             <div class="w-full lg:w-2/3 space-y-6">
                <!-- About Me -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                     <div class="flex justify-between items-center mb-4">
                         <h2 class="text-lg font-semibold text-gray-800 flex items-center"><i class="fas fa-user-circle mr-2 text-primary-600"></i> About Me</h2>
                         <button class="text-primary-600 hover:text-secondary-700 text-sm" aria-label="Edit About section" onclick="document.getElementById('edit-profile-btn').click();"><i class="fas fa-edit"></i> Edit</button>
                     </div>
                     <div id="profile-bio" class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap prose prose-sm max-w-none">
                         <?php echo !empty($profile_bio) ? nl2br(htmlspecialchars($profile_bio)) : '<p class="text-gray-500 italic">No bio added yet. Click Edit Profile to add one!</p>'; ?>
                     </div>
                 </div>

                 <!-- My Courses -->
                 <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                     <div class="flex justify-between items-center mb-4">
                         <h2 class="text-lg font-semibold text-gray-800 flex items-center"><i class="fas fa-book-open mr-2 text-primary-600"></i> My Enrolled Courses</h2>
                         <a href="courses_list.php" class="text-primary-600 hover:text-secondary-700 text-sm font-medium">Browse More</a>
                     </div>
                     <div class="space-y-4">
                         <?php if (empty($enrolled_courses)): ?>
                             <p class="text-center text-gray-500 py-4 border border-dashed border-gray-300 rounded-lg">You haven't enrolled in any courses yet. <a href="courses_list.php" class="text-primary-600 font-medium hover:underline">Explore now!</a></p>
                         <?php else: ?>
                             <?php foreach ($enrolled_courses as $course): ?>
                                 <?php
                                     $slug = $course['course_slug'] ?? 'unknown-slug';
                                     $details = $course_details_map[$slug] ?? ['title' => 'Unknown Course', 'image' => '', 'url' => '#'];
                                     $fallback_image = 'https://via.placeholder.com/150/e2e8f0/94a3b8?text=EduPro';
                                     $image_url = !empty($details['image']) ? htmlspecialchars($details['image']) : $fallback_image;
                                     $course_url = !empty($details['url']) ? htmlspecialchars($details['url']) : '#';
                                     $course_title = htmlspecialchars($details['title']);
                                     $enrollment_date = !empty($course['enrollment_date']) ? date('M j, Y', strtotime($course['enrollment_date'])) : 'N/A';
                                 ?>
                                 <div class="flex flex-col sm:flex-row items-center p-3 hover:bg-gray-50 rounded-lg transition border border-gray-200">
                                     <img src="<?php echo $image_url; ?>" alt="<?php echo $course_title; ?>" class="w-full sm:w-20 h-20 rounded-lg object-cover mr-0 sm:mr-4 mb-3 sm:mb-0 flex-shrink-0 bg-gray-200">
                                     <div class="flex-1 text-center sm:text-left">
                                         <h3 class="font-medium text-gray-800 text-sm md:text-base leading-tight mb-1"><?php echo $course_title; ?></h3>
                                         <p class="text-gray-500 text-xs mb-2">Enrolled: <?php echo $enrollment_date; ?></p>
                                         <a href="<?php echo $course_url; ?>" class="text-xs bg-primary-600 text-white py-1 px-3 rounded-full hover:bg-secondary-700 transition inline-block shadow-sm">Go to Course</a>
                                     </div>
                                 </div>
                             <?php endforeach; ?>
                         <?php endif; ?>
                     </div>
                 </div>

                 <!-- My Documents Placeholder -->
                 <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                     <div class="flex justify-between items-center mb-4">
                         <h2 class="text-lg font-semibold text-gray-800 flex items-center"><i class="fas fa-file-alt mr-2 text-primary-600"></i> My Documents</h2>
                         <button class="text-gray-400 text-sm font-medium cursor-not-allowed" disabled><i class="fas fa-upload mr-1"></i> Upload (Coming Soon)</button>
                     </div>
                     <div class="space-y-3"><p class="text-center text-gray-400 italic py-4 border border-dashed border-gray-300 rounded-lg">Feature coming soon.</p></div>
                 </div>
             </div>
        </div>

        <!-- Edit Profile Modal (Check action URL) -->
        <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
             <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl">
                 <div class="flex justify-between items-center p-4 border-b border-gray-200 sticky top-0 bg-white rounded-t-xl z-10">
                     <h2 id="edit-modal-title" class="text-xl font-bold text-gray-800">Edit Profile Information</h2>
                     <button id="cancel-edit-btn" class="text-gray-400 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100" aria-label="Close"><i class="fas fa-times text-xl"></i></button>
                 </div>
                 <!-- ENSURE update_profile.php exists and handles the form -->
                 <form id="profile-form" class="space-y-4 p-6 overflow-y-auto flex-grow" action="update_profile.php" method="POST" enctype="multipart/form-data">
                     <div class="text-center mb-4">
                         <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                         <div class="relative inline-block group">
                             <img id="profile-image-preview" src="<?php echo $profile_image_url ?? ''; ?>" alt="Preview" class="w-24 h-24 rounded-full border-4 border-white object-cover shadow-md mx-auto bg-gray-300 image-display">
                             <div id="profile-initial-alt-modal" class="w-24 h-24 rounded-full border-4 border-white shadow-md mx-auto bg-primary-600 text-white flex items-center justify-center text-4xl font-bold initials-display"><?php echo $display_initial; ?></div>
                             <button type="button" id="change-image-modal-btn" aria-label="Change picture" class="absolute bottom-0 right-0 bg-primary-600 text-white p-2 rounded-full hover:bg-secondary-700 transition shadow-md transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"><i class="fas fa-camera text-xs"></i></button>
                             <input type="file" id="profile-image-input-modal" name="profile_image_file" accept="image/png, image/jpeg, image/gif, image/webp" class="hidden">
                         </div>
                         <p class="text-xs text-gray-500 mt-2">Max 5MB. JPG, PNG, GIF, WEBP.</p>
                     </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div> <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label> <input type="text" id="edit-name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" value="<?php echo htmlspecialchars($profile_name == $user_email ? '' : $profile_name); ?>" required> </div>
                         <div> <label for="edit-title" class="block text-sm font-medium text-gray-700 mb-1">Position/Title</label> <input type="text" id="edit-title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="e.g., Web Developer" value="<?php echo htmlspecialchars($profile_title == 'Member' ? '' : $profile_title); ?>"> </div>
                     </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div> <label for="edit-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-xs text-gray-500">(Read-only)</span></label> <input type="email" id="edit-email" value="<?php echo $user_email; ?>" readonly disabled class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-100 cursor-not-allowed text-gray-500"> </div>
                         <div> <label for="edit-phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label> <input type="tel" id="edit-phone" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="+1 (555) 123-4567" value="<?php echo htmlspecialchars($profile_phone); // Use value directly ?>"> </div>
                     </div>
                     <div> <label for="edit-address" class="block text-sm font-medium text-gray-700 mb-1">Address</label> <input type="text" id="edit-address" name="address" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="123 Main St, Anytown, USA" value="<?php echo htmlspecialchars($profile_address); // Use value directly ?>"> </div>
                     <div> <label for="edit-bio" class="block text-sm font-medium text-gray-700 mb-1">Bio/About</label> <textarea id="edit-bio" name="bio" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Tell us a little about yourself..."><?php echo htmlspecialchars($profile_bio); // Use value directly ?></textarea> </div>
                     <div>
                         <label class="block text-sm font-medium text-gray-700 mb-2">Skills</label>
                         <div class="flex flex-wrap gap-2 mb-2 min-h-[40px] p-2 border border-gray-200 rounded-lg bg-gray-50" id="edit-skills-container"></div>
                         <div class="flex">
                             <input type="text" id="new-skill-input" placeholder="Add skill (max 50 chars) & press Enter/Add" class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                             <button type="button" id="add-skill-modal-btn" class="bg-primary-600 text-white px-4 py-2 rounded-r-lg hover:bg-secondary-700 transition text-sm font-medium">Add</button>
                         </div>
                         <p class="text-xs text-gray-500 mt-1">Click a skill tag above to remove it.</p>
                         <input type="hidden" name="skills_list" id="skills-list-input">
                     </div>
                     <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 sticky bottom-0 bg-white rounded-b-xl pb-4 px-4 mt-auto">
                         <button type="button" id="cancel-edit-btn2" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 font-medium text-sm transition duration-150"> Cancel </button>
                         <button type="button" id="save-profile-btn" class="px-5 py-2 bg-primary-600 text-white rounded-lg hover:bg-secondary-700 font-medium text-sm flex items-center justify-center min-w-[130px] shadow-sm hover:shadow transition duration-150">
                             <span class="btn-text">Save Changes</span>
                             <span class="spinner hidden ml-2"></span>
                         </button>
                     </div>
                 </form>
             </div>
         </div>

        <!-- Toast Notification Placeholder -->
        <div id="toast-placeholder" class="fixed bottom-4 right-4 z-[60] space-y-2"></div>

    </div> <!-- End Container -->

    <!-- Full JavaScript (Keep existing script, ensure functions are correct) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Element Selection (Ensure all IDs exist in HTML) ---
            const editProfileBtn = document.getElementById('edit-profile-btn');
            const editModal = document.getElementById('edit-modal');
            const cancelEditBtns = document.querySelectorAll('[id^="cancel-edit-btn"]');
            const profileForm = document.getElementById('profile-form');
            const saveProfileBtn = document.getElementById('save-profile-btn');
            const saveProfileBtnText = saveProfileBtn?.querySelector('.btn-text');
            const saveProfileSpinner = saveProfileBtn?.querySelector('.spinner');
            const changeImageBtn = document.getElementById('change-image-btn');
            const changeImageModalBtn = document.getElementById('change-image-modal-btn');
            const profileImage = document.getElementById('profile-image');
            const profileInitialAlt = document.getElementById('profile-initial-alt');
            const profileImagePreview = document.getElementById('profile-image-preview');
            const profileInitialAltModal = document.getElementById('profile-initial-alt-modal');
            const profileImageInput = document.getElementById('profile-image-input');
            const profileImageInputModal = document.getElementById('profile-image-input-modal');
            const addSkillBtn = document.getElementById('add-skill-btn');
            const addSkillModalBtn = document.getElementById('add-skill-modal-btn');
            const newSkillInput = document.getElementById('new-skill-input');
            const skillsContainer = document.getElementById('skills-container'); // Display on card
            const editSkillsContainer = document.getElementById('edit-skills-container'); // Edit in modal
            const skillsListInput = document.getElementById('skills-list-input'); // Hidden input
            // Display elements
            const profileNameDisplay = document.getElementById('profile-name');
            const profileTitleDisplay = document.getElementById('profile-title');
            const profilePhoneDisplay = document.getElementById('profile-phone');
            const profileAddressDisplay = document.getElementById('profile-address');
            const profileBioDisplay = document.getElementById('profile-bio');
            // Modal input elements
            const editNameInput = document.getElementById('edit-name');
            const editTitleInput = document.getElementById('edit-title');
            const editPhoneInput = document.getElementById('edit-phone');
            const editAddressInput = document.getElementById('edit-address');
            const editBioInput = document.getElementById('edit-bio');
            const toastPlaceholder = document.getElementById('toast-placeholder');

            // Initial skills from PHP
            let currentSkills = <?php echo json_encode($skills_array); ?> || [];

            // --- Helper Functions ---
            function showToast(message, type = 'success') {
                if (!toastPlaceholder) return;
                const toast = document.createElement('div');
                const iconClass = type === 'success' ? 'fa-check-circle bg-green-500' : (type === 'error' ? 'fa-exclamation-triangle bg-red-500' : 'fa-info-circle bg-blue-500');
                toast.className = `flex items-center text-white text-sm font-medium px-4 py-3 rounded-md shadow-lg ${iconClass} transform translate-x-full opacity-0 transition-all duration-300 ease-out`;
                toast.innerHTML = `<i class="fas ${iconClass.split(' ')[0]} mr-2"></i><span>${htmlspecialchars(message)}</span>`;
                toastPlaceholder.prepend(toast); // Add new toasts at the top
                setTimeout(() => { toast.classList.remove('translate-x-full', 'opacity-0'); }, 50); // Animate in
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                     setTimeout(() => toast.remove(), 350); // Remove after animation
                }, 4000); // Hide after 4 seconds
            }

            function htmlspecialchars(str) { /* ... (keep existing) ... */ }
            function nl2br (str) { /* ... (keep existing) ... */ }

            function updateSkillsInput() {
                if (!editSkillsContainer || !skillsListInput) return;
                const skills = Array.from(editSkillsContainer.querySelectorAll('.skill-text')).map(el => el.textContent);
                skillsListInput.value = JSON.stringify(skills);
            }

             function addSkillToModal(skillText) {
                if (!editSkillsContainer || !newSkillInput) return;
                const trimmedSkill = skillText.trim().substring(0, 50); // Trim and limit length
                if (!trimmedSkill) return;

                const existingSkills = Array.from(editSkillsContainer.querySelectorAll('.skill-text')).map(el => el.textContent.toLowerCase());
                if (existingSkills.includes(trimmedSkill.toLowerCase())) {
                    showToast(`Skill "${htmlspecialchars(trimmedSkill)}" already added.`, 'info');
                    return;
                }

                const skillElement = document.createElement('div');
                skillElement.className = 'flex items-center bg-blue-100 text-primary-700 text-xs font-medium pl-3 pr-2 py-1 rounded-full animate-fade-in';
                skillElement.innerHTML = `<span class="skill-text mr-1">${htmlspecialchars(trimmedSkill)}</span><button type="button" class="ml-1.5 text-primary-700 hover:text-red-500 remove-skill-btn focus:outline-none" aria-label="Remove ${htmlspecialchars(trimmedSkill)}"><i class="fas fa-times text-xs"></i></button>`;
                editSkillsContainer.appendChild(skillElement);
                updateSkillsInput();
            }


            function initializeEditSkills(skillsArray) {
                if (!editSkillsContainer) return;
                editSkillsContainer.innerHTML = ''; // Clear
                (skillsArray || []).forEach(skillText => { if (skillText) addSkillToModal(skillText); });
                updateSkillsInput(); // Initial update
            }

            function displaySkillsOnProfile(skillsArray) {
                 if (!skillsContainer) return;
                 skillsContainer.innerHTML = ''; // Clear
                 if (skillsArray && skillsArray.length > 0) {
                     skillsArray.forEach(skillText => {
                         const el = document.createElement('span');
                         el.className = 'bg-blue-100 text-primary-700 text-xs font-medium px-2.5 py-1 rounded-full';
                         el.textContent = htmlspecialchars(skillText);
                         skillsContainer.appendChild(el);
                     });
                 } else {
                     skillsContainer.innerHTML = '<span class="text-gray-500 text-xs italic">Click \'Edit Profile\' to add skills.</span>';
                 }
            }


            function closeModal() {
                if (editModal) {
                    editModal.classList.add('hidden');
                    document.body.style.overflow = ''; // Restore scroll
                }
            }

            function triggerImageInput(inputId) {
                 const input = document.getElementById(inputId);
                 if (input) input.click();
            }

             function handleImagePreview(event, previewElementId) {
                 const file = event.target.files[0];
                 const previewEl = document.getElementById(previewElementId);
                 const otherPreviewId = previewElementId === 'profile-image-preview' ? 'profile-image' : 'profile-image-preview';
                 const otherPreviewEl = document.getElementById(otherPreviewId);
                 const initialElId = previewElementId === 'profile-image-preview' ? 'profile-initial-alt-modal' : 'profile-initial-alt';
                 const initialEl = document.getElementById(initialElId);
                 const otherInitialId = previewElementId === 'profile-image-preview' ? 'profile-initial-alt' : 'profile-initial-alt-modal';
                 const otherInitialEl = document.getElementById(otherInitialId);

                 if (file && file.type.startsWith('image/') && file.size <= 5 * 1024 * 1024) { // 5MB Limit
                     const reader = new FileReader();
                     reader.onload = function(e) {
                         const imageUrl = e.target.result;
                         if (previewEl) { previewEl.src = imageUrl; previewEl.style.display = 'block'; previewEl.classList.remove('hidden'); }
                         if (otherPreviewEl) { otherPreviewEl.src = imageUrl; otherPreviewEl.style.display = 'block'; otherPreviewEl.classList.remove('hidden'); }
                         if (initialEl) initialEl.style.display = 'none';
                         if (otherInitialEl) otherInitialEl.style.display = 'none';
                         // Re-add the CSS classes in case they were removed by error handling
                         if (previewEl) previewEl.classList.add('image-display');
                         if (otherPreviewEl) otherPreviewEl.classList.add('image-display');
                         if (initialEl) initialEl.classList.add('initials-display');
                         if (otherInitialEl) otherInitialEl.classList.add('initials-display');
                     }
                     reader.readAsDataURL(file);
                 } else {
                     if (file) showToast('Invalid image: Max 5MB. JPG, PNG, GIF, WEBP only.', 'error');
                     event.target.value = null; // Clear the input on error or cancel
                 }
             }


            // --- Event Listeners Setup ---
             if (editProfileBtn && editModal) {
                editProfileBtn.addEventListener('click', () => {
                    // Populate form - Use PHP values directly, handle empty/default cases
                    editNameInput.value = "<?php echo ($profile_name == $user_email || empty($profile_name)) ? '' : addslashes(htmlspecialchars($profile_name)); ?>";
                    editTitleInput.value = "<?php echo ($profile_title == 'Member' || empty($profile_title)) ? '' : addslashes(htmlspecialchars($profile_title)); ?>";
                    editPhoneInput.value = "<?php echo addslashes(htmlspecialchars($profile_phone)); ?>";
                    editAddressInput.value = "<?php echo addslashes(htmlspecialchars($profile_address)); ?>";
                    editBioInput.value = "<?php echo addslashes(htmlspecialchars($profile_bio)); ?>";

                    // Set modal image/initial state
                    const currentImageSrc = profileImage?.src;
                    const mainInitialVisible = profileInitialAlt && profileInitialAlt.style.display !== 'none';

                     if (currentImageSrc && !mainInitialVisible) {
                         if(profileImagePreview) { profileImagePreview.src = currentImageSrc; profileImagePreview.style.display = 'block'; profileImagePreview.classList.remove('hidden');}
                         if(profileInitialAltModal) profileInitialAltModal.style.display = 'none';
                     } else {
                         if(profileImagePreview) { profileImagePreview.src = ''; profileImagePreview.style.display = 'none'; profileImagePreview.classList.add('hidden'); }
                         if(profileInitialAltModal) {
                            profileInitialAltModal.style.display = 'flex';
                            profileInitialAltModal.textContent = profileInitialAlt?.textContent || '?';
                         }
                    }
                     // Re-add the CSS classes to be sure
                     if(profileImagePreview) profileImagePreview.classList.add('image-display');
                     if(profileInitialAltModal) profileInitialAltModal.classList.add('initials-display');

                    initializeEditSkills(currentSkills);
                    editModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    editNameInput?.focus();
                });
            }

            cancelEditBtns.forEach(btn => btn?.addEventListener('click', closeModal));
            editModal?.addEventListener('click', (e) => { if (e.target === editModal) closeModal(); });
            document.addEventListener('keydown', (e) => { if (e.key === "Escape" && editModal && !editModal.classList.contains('hidden')) closeModal(); });

            // Save Profile AJAX
             if (saveProfileBtn && profileForm) {
                saveProfileBtn.addEventListener('click', function() {
                    updateSkillsInput(); // Ensure skills JSON is current
                    const formData = new FormData(profileForm);

                    // Show loading state
                    if(saveProfileBtnText) saveProfileBtnText.textContent = 'Saving...';
                    saveProfileSpinner?.classList.remove('hidden');
                    saveProfileBtn.disabled = true;
                    saveProfileBtn.classList.add('opacity-75', 'cursor-wait');

                    fetch(profileForm.action, { method: 'POST', body: formData })
                    .then(response => {
                        if (!response.ok) {
                             // If not OK, read response text and throw error
                             return response.text().then(text => { throw new Error(text || `Server error: ${response.status}`) });
                         }
                        return response.json(); // If OK, parse JSON
                    })
                    .then(data => {
                        if (data.success) {
                            // Update page elements (use helper function for safety)
                            const updateElementText = (el, text, defaultText = '') => { if (el) el.textContent = text || defaultText; };
                            const updateElementHTML = (el, html, defaultHTML = '') => { if (el) el.innerHTML = html || defaultHTML; };

                            updateElementText(profileNameDisplay, data.name, 'User');
                            updateElementText(profileTitleDisplay, data.title, 'Member');
                            updateElementText(profilePhoneDisplay, data.phone, '<span class="text-gray-400 italic">Not provided</span>');
                            updateElementText(profileAddressDisplay, data.address, '<span class="text-gray-400 italic">Not provided</span>');
                             // Update bio using nl2br for display
                             const bioHTML = data.bio ? nl2br(htmlspecialchars(data.bio)) : '<p class="text-gray-500 italic">No bio added yet. Click Edit Profile to add one!</p>';
                             updateElementHTML(profileBioDisplay, bioHTML);


                            currentSkills = data.skills || []; // Update JS state
                            displaySkillsOnProfile(currentSkills); // Update display

                            // Update Image & Initials
                            const newImageUrl = data.imageUrl;
                            const displayInitial = (data.name || '?').substring(0,1).toUpperCase();
                             updateElementText(profileInitialAlt, displayInitial);
                             updateElementText(profileInitialAltModal, displayInitial);

                            const updateImageView = (imgEl, initialEl, url) => {
                                if (url) {
                                    const cacheBustedUrl = url + '?t=' + new Date().getTime();
                                     if(imgEl) { imgEl.src = cacheBustedUrl; imgEl.style.display = 'block'; imgEl.classList.add('image-display'); imgEl.classList.remove('hidden');}
                                     if(initialEl) { initialEl.style.display = 'none'; initialEl.classList.add('initials-display'); }
                                 } else {
                                     if(imgEl) { imgEl.src = ''; imgEl.style.display = 'none'; imgEl.classList.add('image-display'); imgEl.classList.remove('hidden');} // Ensure hidden class removed
                                     if(initialEl) { initialEl.style.display = 'flex'; initialEl.classList.add('initials-display'); }
                                 }
                             };
                             updateImageView(profileImage, profileInitialAlt, newImageUrl);
                             updateImageView(profileImagePreview, profileInitialAltModal, newImageUrl); // Sync modal preview


                            closeModal();
                            showToast(data.message || 'Profile updated!', 'success');
                        } else {
                            showToast(data.error || 'Update failed.', 'error');
                            console.error("Update Error:", data.error_detail || data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Save Profile Fetch Error:', error);
                        showToast(`Error: ${error.message || 'Cannot reach server.'}`, 'error');
                    })
                    .finally(() => {
                        if (saveProfileBtn) { // Re-check existence
                           if(saveProfileBtnText) saveProfileBtnText.textContent = 'Save Changes';
                           saveProfileSpinner?.classList.add('hidden');
                           saveProfileBtn.disabled = false;
                           saveProfileBtn.classList.remove('opacity-75', 'cursor-wait');
                        }
                    });
                });
            }


            // Image Change Triggers
            changeImageBtn?.addEventListener('click', () => triggerImageInput('profile-image-input'));
            changeImageModalBtn?.addEventListener('click', () => triggerImageInput('profile-image-input-modal'));
            profileImageInput?.addEventListener('change', (e) => handleImagePreview(e, 'profile-image'));
            profileImageInputModal?.addEventListener('change', (e) => handleImagePreview(e, 'profile-image-preview'));

            // Skill Management
            addSkillBtn?.addEventListener('click', () => { editProfileBtn?.click(); setTimeout(() => newSkillInput?.focus(), 50); });
            addSkillModalBtn?.addEventListener('click', () => { if (newSkillInput) { addSkillToModal(newSkillInput.value); newSkillInput.value = ''; newSkillInput.focus(); } });
            newSkillInput?.addEventListener('keypress', (e) => { if (e.key === 'Enter') { e.preventDefault(); addSkillModalBtn?.click(); } });
            editSkillsContainer?.addEventListener('click', (e) => {
                const removeButton = e.target.closest('.remove-skill-btn');
                if (removeButton) { removeButton.closest('.flex.items-center').remove(); updateSkillsInput(); }
            });

            // --- Initial Page Load ---
            displaySkillsOnProfile(currentSkills); // Initial render of skills on card

        });
        // -------- End of Dashboard JavaScript --------
    </script>

</body>
</html>