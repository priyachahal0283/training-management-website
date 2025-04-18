<?php
// File: google_callback.php

// ---- MUST BE AT THE VERY TOP ----
// Start the session BEFORE any output or variable access
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ---- END SESSION START ----

require_once 'config.php'; // Loads DB conn, Google constants, autoloader

// --- Check if Google returned an error ---
if (isset($_GET['error'])) {
    // Log the error from Google
    error_log("Google Login Error: " . htmlspecialchars($_GET['error']) . " - Description: " . (isset($_GET['error_description']) ? htmlspecialchars($_GET['error_description']) : 'N/A'));
    // Redirect back to login page with a generic error
    header('Location: Login.php?error=google_auth_failed');
    exit;
}

// --- CSRF Protection: State Check ---
// Check if 'state' parameter exists in URL and if we have a stored state in session
if (empty($_GET['state']) || !isset($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {

    // Log the mismatch for debugging
    $log_get_state = isset($_GET['state']) ? $_GET['state'] : 'NOT_SET';
    $log_session_state = isset($_SESSION['oauth2state']) ? $_SESSION['oauth2state'] : 'NOT_SET';
    error_log("Google Login State Mismatch: GET State='{$log_get_state}', Session State='{$log_session_state}'");

    // Important: Unset the potentially invalid session state if it exists
    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }

    // Redirect to login page with the mismatch error
    header('Location: Login.php?error=session_mismatch'); // This triggers the error you saw
    exit;
}

// --- State is valid, proceed: Unset the session state ---
unset($_SESSION['oauth2state']); // State is validated, no longer needed

// --- Check for Authorization Code ---
if (empty($_GET['code'])) {
    // Log error: Google didn't return an authorization code
    error_log("Google Login Error: Authorization code missing in callback.");
    header('Location: Login.php?error=google_code_missing');
    exit;
}

// --- Exchange Authorization Code for Access Token ---
// Recreate the provider object (using config values)
try {
    $provider = new League\OAuth2\Client\Provider\Google([
        'clientId'     => GOOGLE_CLIENT_ID,
        'clientSecret' => GOOGLE_CLIENT_SECRET,
        'redirectUri'  => GOOGLE_REDIRECT_URI,
    ]);

    // Try to get an access token using the authorization code grant.
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // --- Get User Information from Google ---
    // Use the access token to fetch owner details.
    $ownerDetails = $provider->getResourceOwner($token);

    // --- Extract User Data ---
    $google_id = $ownerDetails->getId();
    $google_email = $ownerDetails->getEmail();
    $google_name = $ownerDetails->getName();
    $google_avatar = $ownerDetails->getAvatar(); // Optional: get profile picture URL

    // --- Database Interaction: Find or Create User ---
    // At this point, you have the user's Google info ($google_id, $google_email, $google_name)
    // You need to:
    // 1. Check if a user with this $google_email or $google_id already exists in your 'users' table.
    // 2. If yes: Update their google_id if needed, maybe update name/avatar, log them in.
    // 3. If no: Create a new user record in your 'users' table with their Google details.

    // Example (Simplified - NEEDS proper implementation with prepared statements):

    // Prepare SQL to find user by email OR google_id
    $sql_find = "SELECT id, email, name, google_id FROM users WHERE email = ? OR google_id = ? LIMIT 1";
    if ($stmt_find = $mysqli->prepare($sql_find)) {
        $stmt_find->bind_param("ss", $google_email, $google_id);
        $stmt_find->execute();
        $result = $stmt_find->get_result();

        if ($result->num_rows === 1) {
            // --- User Found ---
            $user = $result->fetch_assoc();
            $user_id = $user['id'];

            // Optional: Update google_id if it was missing or changed
            if (empty($user['google_id'])) {
                $sql_update_gid = "UPDATE users SET google_id = ? WHERE id = ?";
                if ($stmt_update_gid = $mysqli->prepare($sql_update_gid)) {
                    $stmt_update_gid->bind_param("si", $google_id, $user_id);
                    $stmt_update_gid->execute();
                    $stmt_update_gid->close();
                }
            }
            // Optional: Update name/avatar if desired
            $stmt_find->close();

        } else {
            // --- User Not Found - Create New User ---
            $stmt_find->close(); // Close find statement first
            // Note: You might need a placeholder password or handle password differently for social logins
            $placeholder_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Secure random password

            $sql_insert = "INSERT INTO users (name, email, password, google_id, created_at) VALUES (?, ?, ?, ?, NOW())";
            if ($stmt_insert = $mysqli->prepare($sql_insert)) {
                $stmt_insert->bind_param("ssss", $google_name, $google_email, $placeholder_password, $google_id);
                if ($stmt_insert->execute()) {
                    $user_id = $mysqli->insert_id; // Get the ID of the newly created user
                } else {
                    // Handle insert error
                    error_log("Google Login: Failed to insert new user. " . $stmt_insert->error);
                    header('Location: Login.php?error=user_creation_failed');
                    exit;
                }
                $stmt_insert->close();
            } else {
                 // Handle prepare error
                 error_log("Google Login: Failed to prepare insert statement. " . $mysqli->error);
                 header('Location: Login.php?error=db_error');
                 exit;
            }
        }

        // --- Log the User In (Set Session Variables) ---
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $user_id;
        $_SESSION["email"] = $google_email;
        $_SESSION["name"] = $google_name; // Store name from Google
         // Store avatar if you want to use it
        $_SESSION["profile_pic"] = $google_avatar;


        // --- Redirect to Dashboard ---
        $mysqli->close(); // Close DB connection
        header("location: Dashboard.php"); // Or Home.php
        exit;

    } else {
        // Handle prepare error for finding user
        error_log("Google Login: Failed to prepare find user statement. " . $mysqli->error);
         header('Location: Login.php?error=db_error');
         exit;
    }


} catch (Exception $e) {
    // Failed to get token or user details
    error_log("Google OAuth Exception: " . $e->getMessage());
    // Redirect back to login page with a generic error
    header('Location: Login.php?error=google_token_error');
    exit;
}

?>