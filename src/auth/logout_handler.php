<?php
// src/auth/logout_handler.php

/**
 * Handles user logout.
 * - Starts or resumes the current session.
 * - Clears all session variables.
 * - Destroys the session.
 * - Deletes the session cookie.
 * - Redirects the user to the login page.
 */

// Step 1: Start or resume the session.
// This is necessary to access and manipulate session data and to destroy the session itself.
session_start();

// Step 2: Unset all session variables.
// This clears all data stored in the $_SESSION superglobal array for the current session.
$_SESSION = array();

// Step 3: Delete the session cookie (optional but recommended for thorough logout).
// This step ensures that the session cookie is removed from the user's browser,
// preventing the session from being inadvertently resumed if the cookie were to persist.
if (ini_get("session.use_cookies")) { // Check if sessions are cookie-based
    $params = session_get_cookie_params(); // Get current cookie parameters
    setcookie(
        session_name(), // Get the name of the session cookie (e.g., PHPSESSID)
        '', // Set cookie value to empty
        time() - 42000, // Set expiration time to the past to delete the cookie
        $params["path"], // Cookie path (usually /)
        $params["domain"], // Cookie domain
        $params["secure"], // True if cookie should only be sent over HTTPS
        $params["httponly"] // True if cookie should not be accessible via JavaScript
    );
}

// Step 4: Destroy the session.
// This function cleans up all session data on the server associated with the current session ID.
session_destroy();

// Step 5: Redirect the user to the login page.
// After logout, the user is typically sent back to the login screen.
// The path '../../public/login.php' navigates two levels up from 'src/auth/'
// to the root, then into the 'public' directory.
header("Location: ../../public/login.php");
exit; // Ensure no further script execution after the redirect.
?>
