<?php
// install/steps/step_terms.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function for translation, ensuring it's available
if (!function_exists('terms_translate')) {
    function terms_translate($key, $params = []) {
        if (function_exists('t')) {
            $string = t($key);
        } else {
            global $lang; 
            $string = $lang[$key] ?? $key; // Fallback
        }
        foreach ($params as $param_key => $param_value) {
            $string = str_replace('{' . $param_key . '}', htmlspecialchars($param_value), $string);
        }
        return $string;
    }
}

if (!function_exists('create_activation_log')) {
    function create_activation_log() {
        // This function is called only after terms have been explicitly agreed to.
        // It handles log file creation and sets appropriate session messages.

        $writable_dir = __DIR__; // Root directory where step_terms.php is located
        
        // Check if root directory is writable
        if (!is_writable($writable_dir)) {
            $_SESSION['terms_message'] = terms_translate('activation_log_failed_dir_not_writable', ['path' => $writable_dir]);
            $_SESSION['activation_log_created'] = false;
            return false;
        }

        $log_file_path = $writable_dir . '/activation_log.txt';
        
        $date = date("Y-m-d H:i:s");
        $license_key = $_SESSION['license_key'] ?? 'N/A'; // From previous step (License Key)
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        $log_content = "Activation Log\n";
        $log_content .= "--------------------\n";
        $log_content .= "Date: " . $date . "\n";
        $log_content .= "License Key: " . htmlspecialchars($license_key) . "\n";
        $log_content .= "IP Address: " . htmlspecialchars($ip_address) . "\n";
        $log_content .= "Terms Accepted: Yes\n"; // This function is called only if terms are accepted

        if (file_put_contents($log_file_path, $log_content)) {
            $_SESSION['activation_log_created'] = true;
            $_SESSION['terms_message'] = terms_translate('activation_log_success'); 
            return true;
        } else {
            $_SESSION['activation_log_created'] = false;
            $_SESSION['terms_message'] = terms_translate('activation_log_failed_file_write', ['path' => $log_file_path]);
            return false;
        }
    }
}

// Initialize session variables for this step if they aren't already set by index.php's main logic.
if (!isset($_SESSION['terms_agreed'])) { 
    $_SESSION['terms_agreed'] = false;
}
if (!isset($_SESSION['activation_log_created'])) { 
    $_SESSION['activation_log_created'] = false;
}
if (!isset($_SESSION['terms_error_message'])) { 
    $_SESSION['terms_error_message'] = '';
}
if (!isset($_SESSION['terms_message']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // This will be set by index.php's GET logic for step 6 to show an initial prompt.
}
?>
