<?php
// install/steps/step_db_config.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/db.php'; // For connectDB

// Helper function for translation within this step file
function db_translate($key, $params = []) {
    if (function_exists('t')) {
        $string = t($key);
    } else {
        global $lang; // Fallback if t() is not available (should be)
        $string = $lang[$key] ?? $key;
    }
    foreach ($params as $param_key => $param_value) {
        $string = str_replace('{' . $param_key . '}', htmlspecialchars($param_value), $string);
    }
    return $string;
}

$_SESSION['db_form_errors'] = [];
$_SESSION['db_connection_status'] = null; // 'success', 'error'
$_SESSION['db_connection_message'] = '';
$_SESSION['db_creation_status'] = null;
$_SESSION['db_creation_message'] = '';
$_SESSION['db_table_creation_status'] = null;
$_SESSION['db_table_creation_message'] = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = sanitizeInput($_POST['db_host'] ?? 'localhost');
    $db_name = sanitizeInput($_POST['db_name'] ?? '');
    $db_user = sanitizeInput($_POST['db_user'] ?? '');
    $db_password = $_POST['db_password'] ?? ''; // Password sanitization is tricky, typically not htmlspecialchars'd for DB

    // Store sanitized inputs in session to repopulate form
    $_SESSION['db_config_input'] = [
        'db_host' => $db_host,
        'db_name' => $db_name,
        'db_user' => $db_user,
        // Don't store password in session for repopulation for security reasons
    ];

    // Validation
    if (isEmpty($db_host)) {
        $_SESSION['db_form_errors']['db_host'] = db_translate('error_db_host_empty');
    }
    if (isEmpty($db_name)) {
        $_SESSION['db_form_errors']['db_name'] = db_translate('error_db_name_empty');
    }
    if (isEmpty($db_user)) {
        $_SESSION['db_form_errors']['db_user'] = db_translate('error_db_user_empty');
    }
    // Password can be empty for some MySQL setups, so no isEmpty check unless required by policy

    if (empty($_SESSION['db_form_errors'])) {
        // Save valid config to session (excluding password for long-term storage if preferred, but needed for connection)
        $_SESSION['db_config'] = [
            'host' => $db_host,
            'name' => $db_name,
            'user' => $db_user,
            'password' => $db_password, // Stored for this session to attempt connection & table creation
        ];

        // Test Connection (without selecting DB initially to try creating it)
        $_SESSION['db_connection_message'] = db_translate('db_connection_testing', ['db_name' => $db_name]);
        
        $conn_test = new mysqli($db_host, $db_user, $db_password);
        if ($conn_test->connect_error) {
            $_SESSION['db_connection_status'] = 'error';
            $_SESSION['db_connection_message'] = db_translate('db_connection_failed_server', ['error' => $conn_test->connect_error]);
        } else {
            // Try to create database if it doesn't exist
            if (!$conn_test->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                 $_SESSION['db_creation_status'] = 'error';
                 $_SESSION['db_creation_message'] = db_translate('db_creation_failed', ['db_name' => $db_name, 'error' => $conn_test->error]);
                 // Even if DB creation fails, proceed to connectDB to see if it already existed and is connectable
            } else {
                 $_SESSION['db_creation_status'] = 'success';
                 $_SESSION['db_creation_message'] = db_translate('db_creation_success', ['db_name' => $db_name]);
            }
            $conn_test->close();

            // Now, try to connect properly using connectDB function which also sets charset
            $conn = connectDB($db_host, $db_name, $db_user, $db_password);
            if ($conn) {
                $_SESSION['db_connection_status'] = 'success';
                $_SESSION['db_connection_message'] = db_translate('db_connection_success', ['db_name' => $db_name]);
                
                // If "Next" button was clicked (not just "Test Connection") and connection is good
                // This part will be handled by install/index.php checking the installBtn and current step
                // For now, we just set the status. Table creation logic will be called from index.php if this step is successful.

                $conn->close();
            } else {
                $_SESSION['db_connection_status'] = 'error';
                // connectDB function doesn't return detailed error, construct one
                $_SESSION['db_connection_message'] = db_translate('db_connection_failed_db', ['db_name' => $db_name]);
            }
        }
    } else {
        // Validation errors occurred
        $_SESSION['db_connection_status'] = 'error';
        $_SESSION['db_connection_message'] = db_translate('db_validation_errors_occurred');
    }
    
    // Redirect back to the installer page (current step) to show results/errors
    // The main index.php will handle query parameters for step.
    // header('Location: ../index.php?step=' . ($_POST['current_step_hidden'] ?? 3)); // Assuming step 3 is DB config
    // No, redirection is handled by index.php itself after POST. We just set session vars.
}


// Function to create tables, called from index.php if DB connection is successful
// and user proceeds from DB config step.
function create_tables_from_schema() {
    if (!isset($_SESSION['db_config']) || !isset($_SESSION['db_connection_status']) || $_SESSION['db_connection_status'] !== 'success') {
        $_SESSION['db_table_creation_status'] = 'error';
        $_SESSION['db_table_creation_message'] = db_translate('db_connection_not_established_for_tables');
        return false;
    }

    $db_config = $_SESSION['db_config'];
    $conn = connectDB($db_config['host'], $db_config['name'], $db_config['user'], $db_config['password']);

    if (!$conn) {
        $_SESSION['db_table_creation_status'] = 'error';
        $_SESSION['db_table_creation_message'] = db_translate('db_connection_failed_for_tables');
        return false;
    }

    $schema_file = __DIR__ . '/schema.sql';
    if (!file_exists($schema_file)) {
        $_SESSION['db_table_creation_status'] = 'error';
        $_SESSION['db_table_creation_message'] = db_translate('db_schema_file_missing', ['path' => $schema_file]);
        $conn->close();
        return false;
    }

    $sql_commands = file_get_contents($schema_file);
    // Basic split by semicolon, might need improvement for complex SQL with semicolons in comments/strings
    $sql_statements = explode(';', $sql_commands);
    $sql_statements = array_filter(array_map('trim', $sql_statements)); // Remove empty statements

    $all_successful = true;
    $errors = [];

    $_SESSION['db_table_creation_message'] = db_translate('db_tables_creating');
    foreach ($sql_statements as $stmt) {
        if (empty($stmt)) continue;
        if (!$conn->query($stmt)) {
            $all_successful = false;
            $errors[] = $conn->error;
        }
    }

    if ($all_successful) {
        $_SESSION['db_table_creation_status'] = 'success';
        $_SESSION['db_table_creation_message'] = db_translate('db_tables_success');
        $conn->close();
        return true;
    } else {
        $_SESSION['db_table_creation_status'] = 'error';
        $_SESSION['db_table_creation_message'] = db_translate('db_tables_failed', ['errors' => implode('<br>', $errors)]);
        $conn->close();
        return false;
    }
}

/*
Translations needed:
'error_db_host_empty' => 'Database host cannot be empty.',
'error_db_name_empty' => 'Database name cannot be empty.',
'error_db_user_empty' => 'Database user cannot be empty.',
'db_connect_button' => 'Test Connection & Save', // In index.php form
'db_connection_testing' => 'Attempting to connect to database "{db_name}"...',
'db_connection_success' => 'Successfully connected to database "{db_name}".',
'db_connection_failed_server' => 'Failed to connect to database server. Error: {error}',
'db_connection_failed_db' => 'Failed to connect to database "{db_name}". Check credentials and if database exists.',
'db_validation_errors_occurred' => 'Please correct the validation errors above.',
'db_creation_success' => 'Database "{db_name}" created successfully or already existed.',
'db_creation_failed' => 'Failed to create database "{db_name}". Error: {error}',
'db_tables_creating' => 'Creating database tables...',
'db_tables_success' => 'Database tables created successfully.',
'db_tables_failed' => 'Failed to create database tables. Errors: {errors}',
'db_connection_not_established_for_tables' => 'Database connection not established or not successful. Cannot create tables.',
'db_connection_failed_for_tables' => 'Failed to connect to database for table creation.',
'db_schema_file_missing' => 'Database schema file not found at {path}.'
*/
?>
