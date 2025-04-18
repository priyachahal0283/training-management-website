<?php
/* Database credentials. */

// --- IMPORTANT: SET YOUR ACTUAL DATABASE NAME HERE ---
define('DB_NAME', 'user_auth_db'); // ****** EDIT THIS LINE ******

// --- Other Credentials ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', 3307); // Your specified port

/* ==>> Database Connection <<== */
// This creates the $mysqli variable which is now used by ai_course.php and enroll_course.php
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
if($mysqli->connect_error){
    // It's better to log errors in production, but die() is okay for development
    error_log("Config.php DB connection error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error); // Log the error
    die("ERROR: Database connection failed. Please check configuration."); // User-friendly message
}
if (!$mysqli->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: %s\n", $mysqli->error); // Log charset error
    /* Optional: You might want to exit here depending on requirements */
}
/* ==>> End Database Connection <<== */


/*
 * ====================================================
 *          GOOGLE OAuth CREDENTIALS
 * ====================================================
 * DEFINE your constants here using your actual values.
*/

// --- REPLACE PLACEHOLDERS WITH YOUR ACTUAL GOOGLE CREDENTIALS ---

define('GOOGLE_CLIENT_ID', '644812511164-bqjr4agnii3sidus8hcpvl6uiuqq3vaa.apps.googleusercontent.com'); // ** Using your provided ID **
define('GOOGLE_CLIENT_SECRET', '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'); // ** Using your provided Secret **
define('GOOGLE_REDIRECT_URI', 'http://localhost/website_project/google_callback.php'); // ** VERIFY this is your correct Redirect URI **


/*
 * ====================================================
 *          INCLUDE COMPOSER AUTOLOADER
 * ====================================================
 * Required for Google API Client Library (or league/oauth2-client)
*/
// Ensure the path is correct relative to where config.php is located
require_once __DIR__ . '/vendor/autoload.php'; // Assumes vendor folder is in the same directory as config.php


?>