<?php
// ---- SESSION START MUST BE ABSOLUTELY FIRST ----
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ---- END SESSION START ----

// Include config file AFTER session start
require_once "config.php"; // Provides $mysqli object

// ---- REDIRECT IF ALREADY LOGGED IN ----
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: Home.php");
    exit;
}
// ---- END REDIRECT ----


// Define variables and initialize with empty values
// ADDED $name and $name_err
$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = $signup_err = "";

// Check for Google callback errors
$google_error_msg = '';
if (isset($_GET['error'])) {
     $error_map = [ /* ... Keep your error map from Login.php if desired ... */
          'creation_failed' => 'Could not create your account after Google sign up.',
          'db_error' => 'Database error during Google sign up.',
          'invalid_state' => 'Login session mismatch. Please try again.',
     ];
     $error_key = $_GET['error'];
     $google_error_msg = isset($error_map[$error_key]) ? $error_map[$error_key] : 'An unknown Google sign up error occurred.';
}


// Processing form data when form is submitted for EMAIL/PASSWORD/NAME signup
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name (NEW)
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', trim($_POST["name"]))) { // Allow letters and spaces
        $name_err = "Name can only contain letters and white space.";
    } elseif (strlen(trim($_POST["name"])) > 50) {
         $name_err = "Name cannot be longer than 50 characters.";
    } else {
        $name = trim($_POST["name"]);
    }


    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
         $email_err = "Please enter a valid email address.";
    } else {
        // Prepare a select statement to check if email exists
        $sql_check = "SELECT id, provider, password FROM users WHERE email = ?";
        // Use $mysqli from config.php
        if ($stmt_check = $mysqli->prepare($sql_check)) {
            $stmt_check->bind_param("s", $param_email_check);
            $param_email_check = trim($_POST["email"]);

            if ($stmt_check->execute()) {
                $stmt_check->store_result();
                if ($stmt_check->num_rows == 1) {
                     $stmt_check->bind_result($existing_id, $provider, $existing_password);
                     $stmt_check->fetch();
                    // Handle existing email based on provider
                     if ($provider !== 'email' && $provider !== null && empty($existing_password)) {
                          $email_err = "This email is linked to a " . ucfirst($provider) . " account. Please log in using " . ucfirst($provider) . ".";
                     } else {
                         $email_err = "This email is already registered. Please log in.";
                     }
                } else {
                    $email = trim($_POST["email"]); // Email is available
                }
            } else {
                $signup_err = "Oops! Error checking email existence.";
                error_log("Signup Error (Email Check Execute): " . $stmt_check->error);
            }
            $stmt_check->close();
        } else {
             $signup_err = "Oops! Database error preparing email check.";
             error_log("Signup Error (Email Check Prepare): " . $mysqli->error);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // Check input errors before inserting in database
    // ADDED check for $name_err
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($signup_err)) {

        // Prepare an insert statement
        // ADDED name parameter
        $sql_insert = "INSERT INTO users (name, email, password, provider, created_at) VALUES (?, ?, ?, 'email', NOW())"; // Explicitly set provider to 'email', added created_at

        if ($stmt_insert = $mysqli->prepare($sql_insert)) {
            // Bind variables to the prepared statement as parameters
            // CHANGED "ss" to "sss" for three strings (name, email, password)
            $stmt_insert->bind_param("sss", $param_name, $param_email_insert, $param_password_insert);

            // Set parameters
            $param_name = $name; // Use the validated name
            $param_email_insert = $email;
            $param_password_insert = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if ($stmt_insert->execute()) {
                 // Get new user ID and log in immediately
                 $user_id = $mysqli->insert_id; // Get ID of the new user

                 // Store data in session variables
                 $_SESSION["loggedin"] = true;
                 $_SESSION["id"] = $user_id;
                 $_SESSION["email"] = $email;
                 $_SESSION["name"] = $name; // Store the name in session
                 $_SESSION["provider"] = 'email'; // Set provider

                 // Redirect user to welcome page
                 header("location: Home.php");
                 exit;
            } else {
                $signup_err = "Something went wrong. Please try again later.";
                error_log("Signup Error (Insert Execute): " . $stmt_insert->error);
            }
            // Close statement
            $stmt_insert->close();
        } else {
             $signup_err = "Oops! Database error preparing account creation.";
             error_log("Signup Error (Insert Prepare): " . $mysqli->error);
        }
    }

    // Close connection? Usually not needed here.
    // $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - EduPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { /* Keep your config */ }
    </script>
     <style>
        body { background-color: #f3f4f6; }
        .error-message { color: #DC2626; font-size: 0.875rem; margin-top: 0.25rem; }
        .input-error { border-color: #DC2626 !important; }
        .alert-box { border-left-width: 4px; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.375rem; }
        .alert-error { border-color: #EF4444; background-color: #FEE2E2; color: #B91C1C; }
        .alert-warning { border-color: #F97316; background-color: #FFF7ED; color: #C2410C; }
        .alert-icon { margin-right: 0.75rem; vertical-align: middle; font-size: 1.1em; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-100 to-blue-100 py-10 px-4">
    <div class="w-full max-w-md p-8 md:p-10 bg-white rounded-2xl shadow-xl border border-gray-200">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /> </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Create your account</h1>
            <p class="text-gray-500 mt-2 text-sm">Join EduPro today!</p>
        </div>

        <!-- Display Combined Signup/Google Errors -->
        <?php if (!empty($signup_err) || !empty($google_error_msg)): ?>
            <div class="alert-box <?php echo !empty($signup_err) ? 'alert-error' : 'alert-warning'; ?>" role="alert">
                 <i class="fas <?php echo !empty($signup_err) ? 'fa-times-circle' : 'fa-exclamation-triangle'; ?> alert-icon"></i>
                <span class="font-medium"><?php echo htmlspecialchars(!empty($signup_err) ? $signup_err : $google_error_msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-5">

            <!-- Name Field (NEW) -->
             <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400 h-5 w-5"></i>
                    </div>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"
                           class="appearance-none block w-full pl-10 pr-4 py-2.5 border <?php echo (!empty($name_err)) ? 'input-error border-red-500' : 'border-gray-300'; ?> rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150"
                           placeholder="Enter your full name" required aria-invalid="<?php echo !empty($name_err) ? 'true' : 'false'; ?>" aria-describedby="name-error">
                </div>
                 <?php if (!empty($name_err)): ?>
                    <p id="name-error" class="error-message"><?php echo htmlspecialchars($name_err); ?></p>
                <?php endif; ?>
            </div>


            <!-- Email Field -->
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

            <!-- Password Field -->
            <div>
                 <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400 h-5 w-5"></i>
                    </div>
                    <input type="password" id="password" name="password"
                           class="appearance-none block w-full pl-10 pr-10 py-2.5 border <?php echo (!empty($password_err)) ? 'input-error border-red-500' : 'border-gray-300'; ?> rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150"
                           placeholder="Create a password (min 6 chars)" required aria-invalid="<?php echo !empty($password_err) ? 'true' : 'false'; ?>" aria-describedby="password-error">
                    <button type="button" onclick="togglePassword('password', 'eye-icon-pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer" aria-label="Toggle password visibility">
                        <i id="eye-icon-pass" class="fas fa-eye-slash h-5 w-5"></i>
                    </button>
                </div>
                <?php if (!empty($password_err)): ?>
                    <p id="password-error" class="error-message"><?php echo htmlspecialchars($password_err); ?></p>
                <?php endif; ?>
            </div>

             <!-- Confirm Password Field -->
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400 h-5 w-5"></i>
                    </div>
                    <input type="password" id="confirm_password" name="confirm_password"
                           class="appearance-none block w-full pl-10 pr-10 py-2.5 border <?php echo (!empty($confirm_password_err)) ? 'input-error border-red-500' : 'border-gray-300'; ?> rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150"
                           placeholder="Confirm your password" required aria-invalid="<?php echo !empty($confirm_password_err) ? 'true' : 'false'; ?>" aria-describedby="confirm-password-error">
                    <button type="button" onclick="togglePassword('confirm_password', 'eye-icon-confirm')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer" aria-label="Toggle password confirmation visibility">
                        <i id="eye-icon-confirm" class="fas fa-eye-slash h-5 w-5"></i>
                    </button>
                </div>
                <?php if (!empty($confirm_password_err)): ?>
                    <p id="confirm-password-error" class="error-message"><?php echo htmlspecialchars($confirm_password_err); ?></p>
                <?php endif; ?>
            </div>

             <!-- Submit Button -->
            <button type="submit" class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 shadow-sm hover:shadow-md">
                Sign up
                <i class="fas fa-user-plus ml-2 self-center text-xs"></i>
            </button>

            <!-- Divider -->
            <div class="relative pt-2">
                <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-gray-300"></div></div>
                <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-gray-500">Or sign up with</span></div>
            </div>

            <!-- Social Login/Signup Buttons -->
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                 <a href="google_login_init.php" title="Sign up with Google" class="col-span-1 inline-flex w-full justify-center items-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-500 shadow-sm hover:bg-gray-50 transition duration-150">
                     <span class="sr-only">Sign up with Google</span>
                     <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                 </a>
                <button type="button" disabled title="Sign up with Facebook (Not implemented)" class="col-span-1 inline-flex w-full justify-center items-center rounded-md border border-gray-300 bg-gray-100 py-2 px-4 text-sm font-medium text-gray-400 shadow-sm cursor-not-allowed opacity-60">
                    <span class="sr-only">Sign up with Facebook</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M20 10c0-5.523-4.477-10-10-10S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clip-rule="evenodd" /></svg>
                </button>
                <button type="button" disabled title="Sign up with LinkedIn (Not implemented)" class="col-span-1 inline-flex w-full justify-center items-center rounded-md border border-gray-300 bg-gray-100 py-2 px-4 text-sm font-medium text-gray-400 shadow-sm cursor-not-allowed opacity-60">
                    <span class="sr-only">Sign up with LinkedIn</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22.23 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.46c.98 0 1.77-.77 1.77-1.73V1.73C24 .77 23.21 0 22.23 0zM7.06 20.45H3.55V8.97h3.51v11.48zM5.3 7.43c-1.12 0-2.03-.91-2.03-2.03s.91-2.03 2.03-2.03 2.03.91 2.03 2.03-.91 2.03-2.03 2.03zm15.15 13.02h-3.51v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.95v5.66H9.44V8.97h3.37v1.54h.05c.47-.89 1.62-1.83 3.33-1.83 3.56 0 4.22 2.34 4.22 5.39v6.28z"/></svg>
                </button>
            </div>
        </form>

        <p class="text-xs text-center text-gray-500 mt-8">
            Already have an account?
            <a href="Login.php" class="font-medium text-blue-600 hover:text-blue-500">Sign in</a>
        </p>
    </div>

    <script>
        function togglePassword(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(iconId);
             if (!passwordField || !eyeIcon) return;

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        // Optional: Add focus styling to input icons
        document.querySelectorAll('input').forEach(input => {
             const icon = input.parentElement.querySelector('.absolute i.fas');
             if (icon) {
                 input.addEventListener('focus', () => icon.classList.add('text-blue-600'));
                 input.addEventListener('blur', () => icon.classList.remove('text-blue-600'));
             }
        });
    </script>
</body>
</html>