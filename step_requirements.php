<?php
// install/steps/step_requirements.php

$requirements = [];
$all_ok = true;

// Function to safely translate, assuming t() is available globally or pass $lang array
function req_translate($key, $params = []) {
    if (function_exists('t')) {
        $string = t($key);
    } else {
        // Fallback or error if t() is not defined, this should not happen if included correctly
        global $lang;
        $string = $lang[$key] ?? $key;
    }
    foreach ($params as $param_key => $param_value) {
        $string = str_replace('{' . $param_key . '}', htmlspecialchars($param_value), $string);
    }
    return $string;
}


// 1. Проверка версии PHP
$min_php_version = '7.4.0'; // Set your minimum PHP version
if (version_compare(PHP_VERSION, $min_php_version, '<')) {
    $requirements[] = [
        'check' => req_translate('php_version_check') . " (>= $min_php_version)",
        'status' => 'error',
        'message' => req_translate('php_version_error', ['current_version' => PHP_VERSION, 'required_version' => $min_php_version])
    ];
    $all_ok = false;
} else {
    $requirements[] = [
        'check' => req_translate('php_version_check') . " (>= $min_php_version)",
        'status' => 'ok',
        'message' => req_translate('php_version_ok', ['current_version' => PHP_VERSION])
    ];
}

// 2. Проверка расширений PHP
$required_extensions = ['mysqli', 'json', 'mbstring', 'session'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $requirements[] = [
            'check' => req_translate('php_extension_check', ['extension_name' => $ext]),
            'status' => 'error',
            'message' => req_translate('php_extension_error', ['extension_name' => $ext])
        ];
        $all_ok = false;
    } else {
        $requirements[] = [
            'check' => req_translate('php_extension_check', ['extension_name' => $ext]),
            'status' => 'ok',
            'message' => req_translate('php_extension_ok', ['extension_name' => $ext])
        ];
    }
}

// 3. Проверка прав на запись (например, для директории install и корневой директории для config.php)
$writable_paths = [
    'install_dir' => ['path' => realpath(__DIR__ . '/install'), 'name_key' => 'install_dir_name'], // Директория install/
    'app_root_dir' => ['path' => realpath(__DIR__ . '/'), 'name_key' => 'app_root_dir_name'] // Корневая директория /app
    // Добавьте другие директории, если нужно, например, '../logs/'
];

foreach ($writable_paths as $key => $path_info) {
    $dir_path = $path_info['path'];
    $dir_display_name = req_translate($path_info['name_key']); // Get translated name for display

    if (!$dir_path) { // realpath can return false if path does not exist
         $requirements[] = [
            'check' => req_translate('dir_permissions_check', ['dir_name' => $dir_display_name]),
            'status' => 'error',
            'message' => req_translate('dir_not_exists_error', ['dir_name' => $dir_display_name])
        ];
        $all_ok = false;
    } elseif (!is_writable($dir_path)) {
        $requirements[] = [
            'check' => req_translate('dir_permissions_check', ['dir_name' => $dir_display_name]),
            'status' => 'error',
            'message' => req_translate('dir_permissions_error', ['dir_name' => $dir_display_name])
        ];
        $all_ok = false;
    } else {
        $requirements[] = [
            'check' => req_translate('dir_permissions_check', ['dir_name' => $dir_display_name]),
            'status' => 'ok',
            'message' => req_translate('dir_permissions_ok', ['dir_name' => $dir_display_name])
        ];
    }
}

// Передаем результаты в главный скрипт через сессию
$_SESSION['requirements_check_results'] = $requirements;
$_SESSION['requirements_all_ok'] = $all_ok;

/*
Необходимые строки для перевода (добавить в ru.php и en.php):
'step_requirements_title' => 'System Requirements Check', // Already exists as step_system_requirements
'php_version_check' => 'PHP Version',
'php_version_error' => 'Error: Your PHP version is {current_version}. Required version is {required_version} or higher.',
'php_version_ok' => 'OK (Version: {current_version})',
'php_extension_check' => 'PHP Extension: {extension_name}',
'php_extension_error' => 'Error: PHP extension "{extension_name}" is not loaded. Please install or enable it.',
'php_extension_ok' => 'OK (Loaded)',
'dir_permissions_check' => 'Directory Permissions: {dir_name}',
'dir_permissions_error' => 'Error: Directory "{dir_name}" is not writable. Please check permissions.',
'dir_permissions_ok' => 'OK (Writable)',
'dir_not_exists_error' => 'Error: Directory "{dir_name}" does not exist or is not accessible.',
'install_dir_name' => 'Installation Directory (install/)',
'app_root_dir_name' => 'Application Root Directory (for config.php)',
'all_requirements_passed' => 'All system requirements are met. You can proceed to the next step.',
'requirements_failed' => 'Some system requirements are not met. Please fix the issues and try again.',
*/
?>
