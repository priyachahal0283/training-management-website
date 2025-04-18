<?php
// --- TEMPORARY DEBUGGING - REMOVE AFTER FIXING ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUGGING ---

// Start session MUST be first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database config (this sets $mysqli)
require_once 'config.php'; // <--- Check if this path is correct and file has no errors/output

// Prepare default JSON response
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// --- Input and Session Validation ---

// Check for database connection ($mysqli should be set in config.php)
// Use a more robust check
if (!isset($mysqli) || !($mysqli instanceof mysqli) || $mysqli->connect_error) {
    $db_error = 'Unknown DB connection issue.';
    if (isset($mysqli) && ($mysqli instanceof mysqli) && $mysqli->connect_error) {
        $db_error = "(" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    } elseif (!isset($mysqli)) {
        $db_error = '$mysqli variable not set in config.php.';
    } elseif (!($mysqli instanceof mysqli)) {
        $db_error = '$mysqli is not a valid mysqli object.';
    }
    error_log("enroll_course.php: Database connection error: " . $db_error);
    $response['message'] = 'Database connection error.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Explicit exit
}

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $response['message'] = 'Invalid request method.';
    mysqli_close($mysqli);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Explicit exit
}

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['id'])) {
    error_log("enroll_course.php: User not logged in or session ID missing.");
    $response['message'] = 'You must be logged in to enroll.';
    $response['action'] = 'redirect_login';
    mysqli_close($mysqli);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Explicit exit
}

// Check if course_slug was sent
if (!isset($_POST['course_slug']) || empty(trim($_POST['course_slug']))) {
    $response['message'] = 'Course identifier missing.';
    mysqli_close($mysqli);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Explicit exit
}

// --- Get Data ---
$user_id = (int)$_SESSION['id'];
$course_slug = trim($_POST['course_slug']);

// --- Database Operations ---

// 1. Check if already enrolled
$sql_check = "SELECT id FROM user_courses WHERE user_id = ? AND course_slug = ?";
if ($stmt_check = mysqli_prepare($mysqli, $sql_check)) {
    mysqli_stmt_bind_param($stmt_check, "is", $user_id, $course_slug);

    if (mysqli_stmt_execute($stmt_check)) {
        mysqli_stmt_store_result($stmt_check);
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $response['success'] = false;
            $response['message'] = 'You are already enrolled in this course.';
            mysqli_stmt_close($stmt_check);
            mysqli_close($mysqli);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit; // Explicit exit
        }
        mysqli_stmt_close($stmt_check);
    } else {
        error_log("enroll_course.php: Execute failed (Check Enrollment): " . mysqli_stmt_error($stmt_check));
        $response['message'] = 'Error checking enrollment status.';
        mysqli_stmt_close($stmt_check);
        mysqli_close($mysqli);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit; // Explicit exit
    }
} else {
    error_log("enroll_course.php: Prepare failed (Check Enrollment): " . mysqli_error($mysqli));
    $response['message'] = 'Database error during enrollment check.';
    mysqli_close($mysqli);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Explicit exit
}


// 2. Insert enrollment record
$sql_insert = "INSERT INTO user_courses (user_id, course_slug, enrollment_date) VALUES (?, ?, NOW())";
if ($stmt_insert = mysqli_prepare($mysqli, $sql_insert)) {
    mysqli_stmt_bind_param($stmt_insert, "is", $user_id, $course_slug);

    if (mysqli_stmt_execute($stmt_insert)) {
        $response['success'] = true;
        $response['message'] = 'Successfully enrolled in the course!';
        mysqli_stmt_close($stmt_insert);
    } else {
        error_log("enroll_course.php: Execute failed (Insert Enrollment): " . mysqli_stmt_error($stmt_insert));
        $response['message'] = 'Failed to enroll in the course. Please try again later.';
        mysqli_stmt_close($stmt_insert);
    }
} else {
    error_log("enroll_course.php: Prepare failed (Insert Enrollment): " . mysqli_error($mysqli));
    $response['message'] = 'Database error during enrollment process.';
}

// --- Send Final JSON Response ---
mysqli_close($mysqli);
header('Content-Type: application/json');
echo json_encode($response);
exit; // Explicit exit

?>