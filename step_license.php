<?php
// install/steps/step_license.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/validation.php'; // For sanitizeInput and isEmpty

// Helper function for translation
function license_translate($key, $params = []) {
    if (function_exists('t')) {
        $string = t($key);
    } else {
        global $lang; // Fallback
        $string = $lang[$key] ?? $key;
    }
    foreach ($params as $param_key => $param_value) {
        $string = str_replace('{' . $param_key . '}', htmlspecialchars($param_value), $string);
    }
    return $string;
}

// Initialize session variables for this step if not already set
if (!isset($_SESSION['license_key_input'])) {
    $_SESSION['license_key_input'] = '';
}
if (!isset($_SESSION['license_key_accepted'])) {
    $_SESSION['license_key_accepted'] = null; // null, true, or false
}
if (!isset($_SESSION['license_key_message'])) {
    $_SESSION['license_key_message'] = '';
}
if (!isset($_SESSION['license_error_message'])) {
    $_SESSION['license_error_message'] = '';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_license_button'])) {
    // Clear previous messages for this attempt
    $_SESSION['license_key_accepted'] = null;
    $_SESSION['license_key_message'] = '';
    $_SESSION['license_error_message'] = '';

    $license_key = sanitizeInput($_POST['license_key'] ?? '');
    $_SESSION['license_key_input'] = $license_key; // Save sanitized input for repopulation

    // Simple validation: check if not empty
    if (isEmpty($license_key)) {
        $_SESSION['license_key_accepted'] = false;
        $_SESSION['license_error_message'] = license_translate('error_license_key_empty');
        $_SESSION['license_key_message'] = license_translate('license_validation_failed'); // General status
    } else {
        // Emulate license check
        // For simplicity, any non-empty string is considered "valid"
        // You could add more complex checks here, e.g., prefix or length
        // Example: if (strpos($license_key, 'DISCORDCLONE-') === 0 && strlen($license_key) > 15)
        
        $_SESSION['license_key_accepted'] = true;
        $_SESSION['license_key_message'] = license_translate('license_accepted_message');
        // In a real scenario, you might want to store the accepted key more permanently
        // For this emulation, just accepting it is enough for the session.
        $_SESSION['license_key'] = $license_key; // Store the accepted key
    }
    
    // Redirection is handled by install/index.php after POST.
}

/*
Translation keys needed:
'license_key_label' => 'License Key:',
'apply_license_button' => 'Apply License Key',
'license_accepted_message' => 'License key accepted successfully.',
'license_rejected_message' => 'License key rejected or invalid.', // More generic if specific checks fail
'license_validation_failed' => 'License validation failed. Please check the key.', // General status for errors
'error_license_key_empty' => 'License key cannot be empty.',
// Optional more specific errors if you add more validation:
// 'error_license_key_invalid_format' => 'License key has an invalid format.',
*/
?>
