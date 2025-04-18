<?php
// File: upload_profile.php

// Session start karna zaroori hai, agar pehle se start nahi hui hai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration file include karna
// YEH config.php $mysqli VARIABLE MEIN DATABASE CONNECTION BANATA HAI
require_once 'config.php';

// Response ko JSON format mein bhejna
header('Content-Type: application/json');
$response = ['success' => false, 'error' => 'An unknown error occurred.'];

// --- Authentication Check ---
// Check karo ki user logged in hai ya nahi
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    $response['error'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit; // Script rok do
}

// Logged-in user ki ID session se le lo
$user_id = $_SESSION["id"];

// --- Database Connection Check (Using $mysqli) ---
// Check karo ki $mysqli variable config.php se mila ya nahi aur valid hai
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
     // Log error: config.php did not provide a valid $mysqli object
     error_log("upload_profile.php Error: config.php did not create a valid \$mysqli object for user ID: " . $user_id);
     $response['error'] = 'Database configuration error. Please contact support.'; // Error message for user
     echo json_encode($response);
     exit;
}


// --- Process POST Request ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Input Sanitization and Retrieval ---
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $title = isset($_POST['title']) ? trim($_POST['title']) : ''; // Default to empty string
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : ''; // Default to empty string
    $address = isset($_POST['address']) ? trim($_POST['address']) : ''; // Default to empty string
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : ''; // Default to empty string
    $skills_json = isset($_POST['skills_list']) ? $_POST['skills_list'] : '[]'; // Default to empty JSON array string

    // --- Basic Validation ---
    if (empty($name)) {
        $response['error'] = 'Name cannot be empty.';
        echo json_encode($response);
        exit;
    }

    // Limit string lengths
    if (strlen($name) > 100) $name = substr($name, 0, 100);
    if (strlen($title) > 100) $title = substr($title, 0, 100);
    if (strlen($phone) > 30) $phone = substr($phone, 0, 30);

    // --- Skills Processing ---
    $decoded_skills = json_decode($skills_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_skills)) {
         $skills_json = '[]';
         error_log("Invalid skills JSON received for user $user_id: " . (isset($_POST['skills_list']) ? $_POST['skills_list'] : 'Not Set'));
    } else {
        $sanitized_skills = [];
        foreach ($decoded_skills as $skill) {
            $trimmed_skill = trim($skill);
            if (is_string($trimmed_skill) && strlen($trimmed_skill) > 0 && strlen($trimmed_skill) <= 50) {
                 $sanitized_skills[] = htmlspecialchars($trimmed_skill, ENT_QUOTES, 'UTF-8');
            }
        }
        $skills_json = json_encode($sanitized_skills);
    }

    // --- Profile Image Handling ---
    $profile_image_sql_part = "";
    $params_to_bind = []; // Parameters to bind later
    $types = ""; // Parameter types
    $newImagePath = null;
    $oldImagePath = null;

    // 1. Fetch old image path using Object-Oriented style
     $sql_old_img = "SELECT profile_image_path FROM users WHERE id = ?";
     // Prepare statement using $mysqli object
     if ($stmt_old_img = $mysqli->prepare($sql_old_img)) {
         // Bind parameter using the statement object's method
         $stmt_old_img->bind_param("i", $user_id);
         // Execute using the statement object's method
         if ($stmt_old_img->execute()) {
             // Bind result using the statement object's method
             $stmt_old_img->bind_result($oldImagePath);
             // Fetch the result using the statement object's method
             $stmt_old_img->fetch();
         } else {
             // Log error using the $mysqli object's error property
             error_log("Could not fetch old image path for user $user_id: (" . $mysqli->errno . ") " . $mysqli->error);
         }
         // Close the statement using the statement object's method
         $stmt_old_img->close();
     } else {
         // Log error using the $mysqli object's error property if prepare failed
         error_log("Prepare failed for fetching old image path for user $user_id: (" . $mysqli->errno . ") " . $mysqli->error);
     }

    // 2. Check for new image upload
    if (isset($_FILES['profile_image_file']) && $_FILES['profile_image_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image_file'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        // 3. Validate file type and size
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxFileSize) {
            $uploadDir = 'uploads/profile_pics/'; // Upload directory remains the same

            // 5. Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0775, true)) {
                    $response['error'] = 'Server error: Failed to create upload directory.';
                    error_log("Failed to create directory: " . $uploadDir . " for user ID: " . $user_id);
                    echo json_encode($response);
                    exit;
                }
            }

            // 6. Get file extension
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // 7. Double-check extension
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                 $response['error'] = 'Invalid image file extension.';
                 echo json_encode($response);
                 exit;
            }

            // 8. Create unique filename
            $uniqueFilename = 'user_' . $user_id . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $uniqueFilename;

            // 9. Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $profile_image_sql_part = ", profile_image_path = ?"; // SQL part for update
                $newImagePath = $uploadPath; // Store new path
                // Note: Parameter and type will be added later to the final list

                // 10. Delete old image if exists and different
                if ($oldImagePath && $oldImagePath !== $newImagePath && file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            } else {
                error_log("Failed to move uploaded file for user ID: " . $user_id . " from " . $file['tmp_name'] . " to " . $uploadPath);
                $response['error'] = 'Server error: Failed to save profile picture.';
                // Consider if you want to stop the whole update or just skip image update
                // echo json_encode($response); exit; // Uncomment to stop full update on image move failure
            }
        } else {
             // Handle invalid file type or size
             if(!in_array($file['type'], $allowedTypes)){
                 $response['error'] = 'Invalid image file type. Allowed: JPG, PNG, GIF, WEBP.';
             } else if ($file['size'] > $maxFileSize){
                 $response['error'] = 'Image file size exceeds 5MB limit.';
             } else {
                 $response['error'] = 'Invalid image file provided.';
             }
             // echo json_encode($response); exit; // Uncomment to stop full update on invalid image
        }
    } // End of image upload handling

    // --- Database Update (Using Object-Oriented Style) ---

    // Initial parameters (always updated)
    $final_params = [$name, $title, $phone, $address, $bio, $skills_json];
    $final_types = "ssssss"; // 6 strings

    // Add image path parameter if a new image was successfully uploaded
    if ($newImagePath !== null) {
        $final_params[] = $newImagePath;
        $final_types .= "s"; // Add 's' for the image path string
    }

    // Finally, add the User ID for the WHERE clause
    $final_params[] = $user_id;
    $final_types .= "i"; // Add 'i' for the integer user ID

    // Prepare the SQL Update query (profile_image_path part is added conditionally)
    $sql_update = "UPDATE users SET name = ?, title = ?, phone = ?, address = ?, bio = ?, skills_json = ? {$profile_image_sql_part} WHERE id = ?";

    // Prepare statement using $mysqli object
    if ($stmt_update = $mysqli->prepare($sql_update)) {

        // Bind parameters using the statement object's method
        // The spread operator (...) unpacks the $final_params array
        if (!$stmt_update->bind_param($final_types, ...$final_params)) {
             $response['error'] = 'Database error: Failed to bind parameters.';
             // Log error using the statement object's error property
             error_log("Update Profile Error (Bind Param) for user $user_id: (" . $stmt_update->errno . ") " . $stmt_update->error);
        } else {
            // Execute the query using the statement object's method
            if ($stmt_update->execute()) {
                // If successful:
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully!';

                // Send back updated data (HTML escaped)
                $response['name'] = htmlspecialchars($name ?? '');
                $response['title'] = htmlspecialchars($title ?? '');
                $response['phone'] = htmlspecialchars($phone ?? '');
                $response['address'] = htmlspecialchars($address ?? '');
                $response['bio'] = htmlspecialchars($bio ?? '');
                $response['skills'] = json_decode($skills_json); // Send decoded skills array

                // Determine correct image URL (new or old) with cache buster
                $finalImageUrl = $newImagePath ? ($newImagePath . '?t=' . time()) : ($oldImagePath ? $oldImagePath . '?t=' . time() : null);
                $response['imageUrl'] = $finalImageUrl ? htmlspecialchars($finalImageUrl) : null;

                // Update session name if changed
                if (isset($_SESSION['name']) && $_SESSION['name'] !== $name) {
                    $_SESSION['name'] = $name;
                }

            } else {
                // If execution failed:
                $response['error'] = 'Database error: Failed to execute update.';
                // Log error using the $mysqli object's error property
                error_log("Update Profile Error (Execute) for user $user_id: (" . $mysqli->errno . ") " . $mysqli->error);
            }
        }
        // Close the statement using the statement object's method
        $stmt_update->close();
    } else {
        // If statement preparation failed:
        $response['error'] = 'Database error: Failed to prepare update statement.';
        // Log error using the $mysqli object's error property
        error_log("Update Profile Error (Prepare) for user $user_id: (" . $mysqli->errno . ") " . $mysqli->error);
    }

    // Close the database connection using the $mysqli object's method
    $mysqli->close();

} else {
    // If request method is not POST
    $response['error'] = 'Invalid request method.';
    // Close connection if it exists and wasn't closed before (e.g., if code exited early)
    if (isset($mysqli) && $mysqli instanceof mysqli) {
       $mysqli->close();
    }
}

// Output the final JSON response
echo json_encode($response);
exit; // End script execution

?>