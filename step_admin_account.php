<?php
// install/steps/step_admin_account.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/db.php'; // For connectDB

// Helper function for translation
function admin_translate($key, $params = []) {
    if (function_exists('t')) {
        $string = t($key);
    } else {
        global $lang;
        $string = $lang[$key] ?? $key;
    }
    foreach ($params as $param_key => $param_value) {
        $string = str_replace('{' . $param_key . '}', htmlspecialchars($param_value), $string);
    }
    return $string;
}

// Initialize session variables for this step
$_SESSION['admin_account_errors'] = [];
$_SESSION['admin_account_input'] = $_SESSION['admin_account_input'] ?? []; // Persist input across attempts
$_SESSION['admin_account_created'] = null; // true, false
$_SESSION['admin_account_message'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin_button'])) {
    // Clear previous errors and messages for this attempt
    $_SESSION['admin_account_errors'] = [];
    $_SESSION['admin_account_created'] = null;
    $_SESSION['admin_account_message'] = '';

    $admin_username = sanitizeInput($_POST['admin_username'] ?? '');
    $admin_email = sanitizeInput($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? ''; // No htmlspecialchars for password itself
    $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';

    // Store input for repopulation (except passwords)
    $_SESSION['admin_account_input'] = [
        'admin_username' => $admin_username,
        'admin_email' => $admin_email,
    ];

    // Validation
    if (isEmpty($admin_username)) {
        $_SESSION['admin_account_errors']['admin_username'] = admin_translate('error_field_required', ['field_name' => admin_translate('admin_username')]);
    }
    if (isEmpty($admin_email)) {
        $_SESSION['admin_account_errors']['admin_email'] = admin_translate('error_field_required', ['field_name' => admin_translate('admin_email')]);
    } elseif (!isValidEmail($admin_email)) {
        $_SESSION['admin_account_errors']['admin_email'] = admin_translate('error_email_invalid');
    }
    if (isEmpty($admin_password)) {
        $_SESSION['admin_account_errors']['admin_password'] = admin_translate('error_field_required', ['field_name' => admin_translate('admin_password')]);
    } elseif (strlen($admin_password) < 8) {
        $_SESSION['admin_account_errors']['admin_password'] = admin_translate('error_password_short', ['min_length' => 8]);
    }
    if (isEmpty($admin_password_confirm)) {
        $_SESSION['admin_account_errors']['admin_password_confirm'] = admin_translate('error_field_required', ['field_name' => admin_translate('admin_password_confirm')]);
    } elseif (!passwordsMatch($admin_password, $admin_password_confirm)) {
        $_SESSION['admin_account_errors']['admin_password_confirm'] = admin_translate('error_passwords_mismatch');
    }

    if (empty($_SESSION['admin_account_errors'])) {
        // Proceed with database insertion if validation passes
        if (!isset($_SESSION['db_config'])) {
            $_SESSION['admin_account_created'] = false;
            $_SESSION['admin_account_message'] = admin_translate('error_db_config_missing');
        } else {
            $db_config = $_SESSION['db_config'];
            $conn = connectDB($db_config['host'], $db_config['name'], $db_config['user'], $db_config['password']);

            if (!$conn) {
                $_SESSION['admin_account_created'] = false;
                $_SESSION['admin_account_message'] = admin_translate('error_db_connection_failed_admin');
            } else {
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $role = 'admin';

                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssss", $admin_username, $admin_email, $hashed_password, $role);
                    if ($stmt->execute()) {
                        $_SESSION['admin_account_created'] = true;
                        $_SESSION['admin_account_message'] = admin_translate('admin_account_creation_success');
                        // Clear input on success
                        unset($_SESSION['admin_account_input']); 
                    } else {
                        $_SESSION['admin_account_created'] = false;
                        // Check for duplicate entry
                        if ($conn->errno == 1062) { // Error code for duplicate entry
                             $_SESSION['admin_account_message'] = admin_translate('error_admin_account_duplicate', ['error' => $stmt->error]);
                        } else {
                             $_SESSION['admin_account_message'] = admin_translate('admin_account_creation_failed', ['error' => $stmt->error]);
                        }
                    }
                    $stmt->close();
                } else {
                    $_SESSION['admin_account_created'] = false;
                    $_SESSION['admin_account_message'] = admin_translate('admin_account_creation_failed', ['error' => $conn->error]);
                }
                $conn->close();
            }
        }
    } else {
        // Validation errors occurred
         $_SESSION['admin_account_created'] = false; // Ensure this is set
         $_SESSION['admin_account_message'] = admin_translate('admin_account_validation_errors');
    }
    // Redirection is handled by install/index.php after POST.
}

/*
Translation keys needed:
'admin_username' => 'Username',
'admin_email' => 'Email',
'admin_password' => 'Password',
'admin_password_confirm' => 'Confirm Password',
'create_admin_button' => 'Create Account',
'admin_account_creation_success' => 'Admin account created successfully.',
'admin_account_creation_failed' => 'Failed to create admin account. Error: {error}',
'admin_account_validation_errors' => 'Please correct the errors and try again.',
'error_db_config_missing' => 'Database configuration is missing. Please complete previous steps.',
'error_db_connection_failed_admin' => 'Failed to connect to database to create admin account.',
'error_passwords_mismatch' => 'Passwords do not match.',
'error_password_short' => 'Password must be at least {min_length} characters long.',
'error_email_invalid' => 'Invalid email address.',
'error_field_required' => '{field_name} is required.',
'error_admin_account_duplicate' => 'An account with this username or email already exists. Error: {error}'
*/
?>
