<?php
// ---- SESSION START MUST BE ABSOLUTELY FIRST ----
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ---- END SESSION START ----


// ---- REDIRECT IF ALREADY LOGGED IN ----
// Check if user is already logged in, if yes then redirect them to dashboard/home page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: Home.php"); // Or Dashboard.php if preferred
    exit; // Make sure to exit after redirect
}
// ---- END REDIRECT ----


// Include config file AFTER the logged-in check
// This file should NOT contain any redirects itself.
require_once "config.php"; // Needs DB connection details ($mysqli) for email/password login

// Define variables for standard login
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Check for error messages passed in URL from Google callback
$google_error_msg = '';
if (isset($_GET['error'])) {
    // Use a mapping for clearer error messages
    $error_map = [
        'session_mismatch' => 'Login session mismatch. Please try again.',
        'google_auth_failed' => 'Authentication failed with Google. Please try again.',
        'google_code_missing' => 'Authorization code not received. Please try again.',
        'google_token_error' => 'Could not retrieve access token or user details. Please try again.',
        'user_creation_failed' => 'Could not create local user account. Please contact support.',
        'db_error' => 'A database error occurred. Please try again later.',
        'unexpected_code_init' => 'An unexpected error occurred (code init). Please try again.',
        // Add errors from standard login if needed (though typically handled by $login_err)
        // 'invalid_credentials' => 'Invalid email or password.'
    ];
    $error_key = $_GET['error'];
    // Assign to google_error_msg specifically for Google errors
    $google_error_msg = isset($error_map[$error_key]) ? $error_map[$error_key] : 'An unknown Google login error occurred.';
}


// Processing form data when form is submitted for EMAIL/PASSWORD login
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Prevent processing if Google error is already shown (optional UX choice)
    // Generally, allow standard login even if a previous Google attempt failed.
    // if (empty($google_error_msg)) { // Removed this check

        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter email.";
        } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
             $email_err = "Please enter a valid email address.";
        } else {
            $email = trim($_POST["email"]);
        }

        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($_POST["password"]);
        }

        // Proceed if no validation errors
        if (empty($email_err) && empty($password_err)) {
            // Prepare a select statement
            // Make sure 'users' table and columns 'id', 'email', 'password', 'provider', 'name' exist
            $sql = "SELECT id, email, password, provider, name FROM users WHERE email = ?"; // Added name

            // Use $mysqli from config.php
            if ($stmt = $mysqli->prepare($sql)) { // Changed mysqli_prepare to $mysqli->prepare
                $stmt->bind_param("s", $param_email); // Changed mysqli_stmt_bind_param
                $param_email = $email;

                if ($stmt->execute()) { // Changed mysqli_stmt_execute
                    $stmt->store_result(); // Changed mysqli_stmt_store_result

                    if ($stmt->num_rows == 1) { // Changed mysqli_stmt_num_rows
                        // Bind result variables
                        // Make sure the order matches the SELECT statement
                        $stmt->bind_result($id, $db_email, $hashed_password, $provider, $name);
                        if ($stmt->fetch()) { // Changed mysqli_stmt_fetch

                            // Check if the user registered via email/password
                            // Allow NULL provider or 'email' provider for password check
                            // Also check if hashed_password is not empty/null
                            if (($provider === null || $provider === 'email') && !empty($hashed_password)) {
                                if (password_verify($password, $hashed_password)) {
                                    // Password is correct
                                    // Start session again just in case (should already be started)
                                    if (session_status() == PHP_SESSION_NONE) { session_start(); }

                                    // Store data in session variables
                                    $_SESSION["loggedin"] = true;
                                    $_SESSION["id"] = $id;
                                    $_SESSION["email"] = $db_email;
                                    // Use the fetched name, default if empty
                                    $_SESSION["name"] = !empty($name) ? $name : $db_email;
                                    $_SESSION["provider"] = 'email'; // Explicitly set provider for email login

                                    // Redirect user to welcome page
                                    header("location: Home.php");
                                    exit;
                                } else {
                                    // Password not valid for email user
                                    $login_err = "Invalid email or password.";
                                }
                            } else {
                                // Password field might be empty (social login) OR provider is not 'email'
                                $login_err = "Invalid email or password.";
                                if (!empty($provider) && $provider !== 'email') {
                                     // If provider is set (e.g., 'google'), suggest that method
                                     $login_err .= " This email might be associated with a " . ucfirst($provider) . " login.";
                                } elseif (empty($hashed_password)) {
                                     // If password hash is empty, it's likely a social login account
                                     $login_err .= " Account may use social login.";
                                }
                            }
                        } else {
                             // This error shouldn't ideally happen if num_rows was 1
                             $login_err = "Error fetching user data after finding user.";
                             error_log("Login Error: Failed to fetch after finding user with email: " . $email);
                        }
                    } else {
                        // Email doesn't exist in the database
                        $login_err = "Invalid email or password.";
                    }
                } else {
                    $login_err = "Oops! Error executing query. Please try again later.";
                     error_log("Login Error (Execute): " . $stmt->error);
                }
                // Close statement
                $stmt->close();
            } else {
                 $login_err = "Oops! Database error preparing login. Please try again later.";
                 error_log("Login Error (Prepare): " . $mysqli->error);
            }
            // Close connection? Usually done by PHP automatically at script end unless persistent.
            // $mysqli->close();
        }
    // } // End of check for empty Google error (Removed)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Tailwind Config (Keep your customizations)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 600: '#2563eb', 700: '#1d4ed8' },
                        dark: { 800: '#1e293b', 900: '#0f172a' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f3f4f6; /* Light gray background */ }
        .error-message { color: #DC2626; /* text-red-600 */ font-size: 0.875rem; /* text-sm */ margin-top: 0.25rem; /* mt-1 */ }
        .input-error { border-color: #DC2626 !important; /* border-red-600 */ }
        .alert-box { border-left-width: 4px; padding: 1rem; /* p-4 */ margin-bottom: 1.5rem; /* mb-6 */ border-radius: 0.375rem; /* rounded-md */ }
        .alert-error { border-color: #EF4444; /* border-red-500 */ background-color: #FEE2E2; /* bg-red-100 */ color: #B91C1C; /* text-red-700 */ }
        .alert-warning { border-color: #F97316; /* border-orange-500 */ background-color: #FFF7ED; /* bg-orange-100 */ color: #C2410C; /* text-orange-700 */ }
        .alert-icon { margin-right: 0.75rem; /* mr-3 */ vertical-align: middle; font-size: 1.1em; /* Slightly larger icon */ }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-100 to-blue-100 py-10 px-4">
    <div class="w-full max-w-md p-8 md:p-10 bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                 <!-- Your Logo SVG -->
                 <svg class="h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"> <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" /> </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Welcome back</h1>
            <p class="text-gray-500 mt-2 text-sm">Sign in to access your dashboard</p>
        </div>

        <!-- Display Combined Login/Google Errors -->
        <?php if (!empty($login_err) || !empty($google_error_msg)): ?>
            <div class="alert-box <?php echo !empty($login_err) ? 'alert-error' : 'alert-warning'; ?>" role="alert">
                 <i class="fas <?php echo !empty($login_err) ? 'fa-times-circle' : 'fa-exclamation-triangle'; ?> alert-icon"></i>
                <span class="font-medium"><?php echo htmlspecialchars(!empty($login_err) ? $login_err : $google_error_msg); ?></span>
            </div>
        <?php endif; ?>


        <!-- Standard Login Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400 h-5 w-5"></i>
                    </div>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                           class="appearance-none block w-full pl-10 pr-4 py-2.5 border <?php echo (!empty($email_err)) ? 'input-error border-red-500' : 'border-gray-300'; ?> rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150"
                           placeholder="your@email.com" required aria-invalid="<?php echo !empty($email_err) ? 'true' : 'false'; ?>" aria-describedby="email-error">
                </div>
                 <?php if (!empty($email_err)): ?>
                    <p id="email-error" class="error-message"><?php echo htmlspecialchars($email_err); ?></p>
                <?php endif; ?>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <a href="#" class="text-xs text-blue-600 hover:text-blue-500">Forgot password?</a>
                </div>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400 h-5 w-5"></i>
                    </div>
                    <input type="password" id="password" name="password"
                           class="appearance-none block w-full pl-10 pr-10 py-2.5 border <?php echo (!empty($password_err)) ? 'input-error border-red-500' : 'border-gray-300'; ?> rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150"
                           placeholder="••••••••" required aria-invalid="<?php echo !empty($password_err) ? 'true' : 'false'; ?>" aria-describedby="password-error">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer" aria-label="Toggle password visibility">
                        <i id="eye-icon" class="fas fa-eye-slash h-5 w-5"></i>
                    </button>
                </div>
                 <?php if (!empty($password_err)): ?>
                    <p id="password-error" class="error-message"><?php echo htmlspecialchars($password_err); ?></p>
                <?php endif; ?>
            </div>

            <div class="flex items-center">
                <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                    Remember me
                </label>
            </div>

            <button type="submit" class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 shadow-sm hover:shadow-md">
                Sign in
                <i class="fas fa-arrow-right ml-2 transform transition-transform duration-200 ease-in-out group-hover:translate-x-1 self-center text-xs"></i>
            </button>

            <!-- Divider -->
            <div class="relative pt-2"> <!-- Added padding top -->
                <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-gray-300"></div></div>
                <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-gray-500">Or continue with</span></div>
            </div>

            <!-- Social Login Buttons -->
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                 <!-- Google Login Link -->
                 <a href="google_login_init.php" title="Sign in with Google" class="col-span-1 inline-flex w-full justify-center items-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-500 shadow-sm hover:bg-gray-50 transition duration-150">
                     <span class="sr-only">Sign in with Google</span>
                     <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                 </a>
                 <!-- Facebook Placeholder -->
                <button type="button" disabled title="Sign in with Facebook (Not implemented)" class="col-span-1 inline-flex w-full justify-center items-center rounded-md border border-gray-300 bg-gray-100 py-2 px-4 text-sm font-medium text-gray-400 shadow-sm cursor-not-allowed opacity-60">
                    <span class="sr-only">Sign in with Facebook</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M20 10c0-5.523-4.477-10-10-10S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clip-rule="evenodd" /></svg>
                </button>
                 <!-- LinkedIn Placeholder -->
                <button type="button" disabled title="Sign in with LinkedIn (Not implemented)" class="col-span-1 inline-flex w-full justify-center items-center rounded-md border border-gray-300 bg-gray-100 py-2 px-4 text-sm font-medium text-gray-400 shadow-sm cursor-not-allowed opacity-60">
                    <span class="sr-only">Sign in with LinkedIn</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22.23 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.46c.98 0 1.77-.77 1.77-1.73V1.73C24 .77 23.21 0 22.23 0zM7.06 20.45H3.55V8.97h3.51v11.48zM5.3 7.43c-1.12 0-2.03-.91-2.03-2.03s.91-2.03 2.03-2.03 2.03.91 2.03 2.03-.91 2.03-2.03 2.03zm15.15 13.02h-3.51v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.95v5.66H9.44V8.97h3.37v1.54h.05c.47-.89 1.62-1.83 3.33-1.83 3.56 0 4.22 2.34 4.22 5.39v6.28z"/></svg>
                </button>
            </div>
        </form>

        <p class="text-xs text-center text-gray-500 mt-8">
            Don't have an account?
            <a href="Signup.php" class="font-medium text-blue-600 hover:text-blue-500"> <!-- Adjust if your signup page has a different name -->
                Sign up now
            </a>
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (!passwordField || !eyeIcon) return;

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }

        // Optional: Add focus styling to input icons (subtle effect)
        document.querySelectorAll('input[type="email"], input[type="password"]').forEach(input => {
             const icon = input.parentElement.querySelector('.absolute i.fas');
             if (icon) {
                 input.addEventListener('focus', () => icon.classList.add('text-blue-600'));
                 input.addEventListener('blur', () => icon.classList.remove('text-blue-600'));
             }
        });
    </script>
</body>
</html>