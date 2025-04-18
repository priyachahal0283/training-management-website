<?php
// File: google_login_init.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include configuration - This loads the constants defined above
require_once 'config.php';

// ---- CORRECTED CHECKS ----
// 1. Verify that the constants were actually defined in config.php
if (!defined('GOOGLE_CLIENT_ID') || !defined('GOOGLE_CLIENT_SECRET') || !defined('GOOGLE_REDIRECT_URI')) {
    error_log("FATAL ERROR: Google OAuth constants are not defined in config.php.");
    die('Google Login configuration error: Required constants are missing from config.php.');
}

// 2. Check if the DEFINED constants still hold generic placeholder values
//    (These specific placeholder strings should match what you might temporarily put in config.php)
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' || GOOGLE_CLIENT_SECRET === 'YOUR_GOOGLE_CLIENT_SECRET' || GOOGLE_REDIRECT_URI === '' || GOOGLE_REDIRECT_URI === 'YOUR_REDIRECT_URI_HERE' ) {
     error_log("FATAL ERROR: Google OAuth constants in config.php appear to be placeholders.");
     die('Google Login configuration error: Please replace placeholder values in config.php with actual credentials.');
}
// ---- END CORRECTED CHECKS ----


// Ensure Composer autoloader worked (check if class exists)
if (!class_exists('League\OAuth2\Client\Provider\Google')) {
    error_log("FATAL ERROR: League OAuth2 Google Provider class not found. Check Composer install and autoload include.");
    die('Google Login library error: Class not found.');
}


// Create Google OAuth Client using credentials from config.php
try {
    $provider = new League\OAuth2\Client\Provider\Google([
        'clientId'     => GOOGLE_CLIENT_ID,       // Constant is now defined
        'clientSecret' => GOOGLE_CLIENT_SECRET,   // Constant is now defined
        'redirectUri'  => GOOGLE_REDIRECT_URI,    // Constant is now defined
    ]);
} catch (Exception $e) {
    error_log("Error creating Google OAuth Provider: " . $e->getMessage());
    die('Error initializing Google Login provider.');
}


// Redirect if accessed incorrectly after callback.
if (isset($_GET['code'])) {
     header('Location: Login.php?error=unexpected_code_init');
     exit;
}


// Generate the Google Authorization URL
try {
    $options = [
        // 'prompt' => 'consent', // Optional: Force consent screen every time
        'scope' => ['email', 'profile'] // Request email and profile
    ];
    $authorizationUrl = $provider->getAuthorizationUrl($options);

    // Store state in session for CSRF protection
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect user to Google's authentication page
    header('Location: ' . $authorizationUrl);
    exit;

} catch (Exception $e) {
    error_log("Error generating Google authorization URL: " . $e->getMessage());
    die('Error preparing Google login request.');
}

?>