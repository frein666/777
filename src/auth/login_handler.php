<?php
// src/auth/login_handler.php

/**
 * Handles user login.
 * - Starts a session.
 * - Validates input (identifier: email/username, password).
 * - Fetches user from the database.
 * - Verifies password.
 * - Manages session variables upon successful login.
 * - Redirects user based on login success or failure.
 */

session_start(); // Essential for storing user session data (e.g., user_id)

// Include the database connection script.
require_once __DIR__ . '/../../config/database.php'; // Provides $pdo

// Array to store potential error messages (though we'll mostly use session for redirection)
$errors = []; // Local errors for logic, session errors for display on login form

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve form data.
    // Using trim() for identifier, raw for password.
    // Null coalescing operator (??) for default empty string if not set.
    $identifier = trim($_POST['identifier'] ?? ''); // Can be username or email
    $password = $_POST['password'] ?? '';

    // --- 1. Basic Input Validation ---
    if (empty($identifier)) {
        $errors[] = "Необходимо указать логин или адрес электронной почты.";
    }
    if (empty($password)) {
        $errors[] = "Необходимо ввести пароль.";
    }

    // If basic validation passes, proceed to database check
    if (empty($errors)) {
        try {
            // Determine if the identifier is an email or a username
            // A simple check: if it contains '@', assume it's an email.
            // For more robust validation, filter_var($identifier, FILTER_VALIDATE_EMAIL) can be used.
            $column_to_check = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            // Prepare SQL statement to fetch user by email or username
            $sql = "SELECT id, username, email, password_hash FROM users WHERE $column_to_check = :identifier";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user record

            // --- 2. Verify User and Password ---
            if ($user && password_verify($password, $user['password_hash'])) {
                // User found and password matches
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                // Store user information in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email']; // Optional: store email if needed frequently
                $_SESSION['logged_in_at'] = time(); // Optional: store login timestamp

                // Redirect to the main application page (e.g., dashboard or messenger interface)
                // Assuming 'index.php' in 'public' is the main entry point for logged-in users.
                header('Location: ../../public/index.php');
                exit; // Crucial to stop script execution after a redirect header
            } else {
                // User not found or password incorrect
                $errors[] = "Неверный логин/email или пароль. Пожалуйста, проверьте введенные данные.";
            }
        } catch (PDOException $e) {
            // Log database errors securely.
            error_log("Database login error in login_handler.php: " . $e->getMessage());
            // Set a generic error message for the user.
            $errors[] = "Произошла ошибка при попытке входа. Пожалуйста, попробуйте еще раз позже.";
        }
    }

    // --- 3. Handle Login Failure ---
    // If there are any errors (either from validation or DB check/password verify)
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors; // Store errors in session to display on login page
        $_SESSION['login_form_identifier'] = $identifier; // Optionally preserve identifier input

        // Redirect back to the login page
        header('Location: ../../public/login.php');
        exit; // Stop script execution
    }

} else {
    // Request method is not POST
    // This script should ideally not be accessed directly via GET.
    // Redirect to login page or show an error.
    $_SESSION['login_errors'] = ["Неверный метод запроса."];
    header('Location: ../../public/login.php');
    // Or, for direct access attempt:
    // echo "Доступ запрещен. Пожалуйста, используйте форму входа.";
    exit;
}
?>
