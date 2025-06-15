<?php
// src/auth/register_handler.php

/**
 * Handles user registration.
 * - Validates input (username, email, password).
 * - Checks for existing username/email.
 * - Hashes password.
 * - Inserts new user into the database.
 */

// Start session to store error messages or user data if needed.
// session_start(); // Uncomment if you plan to use sessions for feedback.

// Include the database connection script.
// __DIR__ is a magic constant that gives the directory of the current file.
// Adjust the path ../../ to navigate from src/auth/ to the root, then to config/.
require_once __DIR__ . '/../../config/database.php'; // Provides $pdo

// Array to store validation errors
$errors = [];

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize and retrieve form data.
    // Using trim() to remove whitespace from beginning and end.
    // Using null coalescing operator (??) to provide a default empty string if not set.
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Raw password
    $password_confirm = $_POST['password_confirm'] ?? ''; // Password confirmation

    // --- 1. Basic Input Validation ---

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    // Add more password complexity rules if needed (e.g., uppercase, number, special char)

    // Validate password confirmation
    if ($password !== $password_confirm) {
        $errors[] = "Passwords do not match.";
    }

    // --- 2. Database Validation (Check for unique username and email) ---
    // This section proceeds only if basic validation passed.
    if (empty($errors)) {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $errors[] = "Username already taken. Please choose another.";
            }

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $errors[] = "This email address is already registered.";
            }
        } catch (PDOException $e) {
            // Log database errors instead of showing them directly to users in production.
            error_log("Database validation error in register_handler.php: " . $e->getMessage());
            $errors[] = "A database error occurred during validation. Please try again later.";
        }
    }

    // --- 3. Process Registration if no errors ---
    if (empty($errors)) {
        // Hash the password for secure storage
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // PASSWORD_DEFAULT uses the current best algorithm (bcrypt by default)
        // and will update automatically as PHP versions improve.

        // Insert user data into the database
        try {
            $sql = "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)";
            $stmt = $pdo->prepare($sql);

            // Bind parameters to prevent SQL injection
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Registration successful
                // In a real application, you would redirect to a login page or a welcome page.
                // You might also automatically log the user in.
                // For now, just output a success message.
                echo "Registration successful! You can now log in.";
                // Example redirect: header('Location: ../../public/login.php?status=registered');
                exit; // Important to call exit after a redirect header.
            } else {
                $errors[] = "Registration failed due to a server error. Please try again.";
            }
        } catch (PDOException $e) {
            // Log database errors.
            error_log("Database insertion error in register_handler.php: " . $e->getMessage());

            // Check for specific SQL error codes, like duplicate entry
            if ($e->getCode() == '23000') { // SQLSTATE 23000: Integrity constraint violation
                $errors[] = "This username or email is already taken. Please choose different credentials.";
            } else {
                $errors[] = "A database error occurred during registration. Please try again later.";
            }
        }
    }

    // --- 4. Display Errors if any occurred ---
    if (!empty($errors)) {
        // In a real application, you would typically store errors in a session
        // and redirect back to the registration form to display them.
        // Example:
        // $_SESSION['register_errors'] = $errors;
        // $_SESSION['form_inputs'] = ['username' => $username, 'email' => $email]; // Preserve inputs
        // header('Location: ../../public/register.php');
        // exit;

        // For now, just print errors directly.
        echo "<strong>Error(s) occurred:</strong><br>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "<br>";
        }
        // Optionally, provide a link to go back or try again
        // echo '<br><a href="../../public/register.php">Try again</a>';
        exit;
    }

} else {
    // Request method is not POST
    // Redirect to the registration page or show an error.
    // For now, just output a message.
    // header('Location: ../../public/register.php'); // Example redirect
    echo "Invalid request method. Please submit the form correctly.";
    exit;
}
?>
