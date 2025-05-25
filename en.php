<?php
$lang = [
    // Placeholders from this subtask's requirements
    'installer_title_placeholder' => 'Installer', // Will be replaced by 'installer_title' later
    'select_language_placeholder' => 'Select Language:', // Will be replaced by 'select_language' later
    'language_en_placeholder' => 'English (US)', // Will be replaced by 'language_en' later
    'language_ru_placeholder' => 'Russian', // Will be replaced by 'language_ru' later
    'welcome_message_placeholder' => 'Welcome to the Installer! Select your language to proceed.',
    'just_a_test_placeholder' => 'This is a test of the translation system for the current step.',
    'system_requirements_content_placeholder' => 'System requirements will be checked here.',
    'database_configuration_content_placeholder' => 'Database configuration will be done here.',
    'admin_account_content_placeholder' => 'Admin account creation will be done here.',
    'license_key_content_placeholder' => 'License key will be entered here.',
    'terms_conditions_content_placeholder' => 'Terms and conditions will be displayed here.',
    'finish_content_placeholder' => 'Finalizing installation.',
    'button_previous_placeholder' => 'Previous', // Will be replaced by 'button_previous'
    'button_next_placeholder' => 'Next', // Will be replaced by 'button_next'
    'button_install_placeholder' => 'Install', // Will be replaced by 'button_install'

    // Core Installer Steps (already present, ensure they are correct)
    'installer_title' => 'Discord Clone Installer',
    'step_language_selection' => 'Language Selection',
    'step_system_requirements' => 'System Requirements',
    'step_database_configuration' => 'Database Configuration',
    'step_admin_account' => 'Admin Account',
    'step_license_key' => 'License Key',
    'step_terms_conditions' => 'Terms & Conditions',
    'step_finish' => 'Finish',

    // General UI elements (already present)
    'select_language' => 'Select Language:',
    'language_en' => 'English (US)',
    'language_ru' => 'Русский (Russian)',
    'button_next' => 'Next',
    'button_previous' => 'Previous',
    'button_install' => 'Install',

    // Keys from previous subtasks (to ensure they are not lost)
    'system_requirements_title' => 'System Requirements',
    'php_version_check' => 'PHP Version',
    'mysqli_extension_check' => 'MySQLi Extension',
    'write_permissions_check' => 'Write Permissions (config.php)',
    'check_status_ok' => 'OK',
    'check_status_missing' => 'Missing (Example)',
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
    'language_en_name' => 'English (US)', 
    'language_ru_name' => 'Русский (Russian)', 

    'db_host_label' => 'Database Host:',
    'db_name_label' => 'Database Name:',
    'db_user_label' => 'Database User:',
    'db_password_label' => 'Database Password:',
    'db_host_placeholder' => 'localhost',
    'db_connect_button' => 'Test Connection & Save',
    'db_connection_testing' => 'Attempting to connect to database "{db_name}"...',
    'db_connection_success' => 'Successfully connected to database "{db_name}".',
    'db_connection_failed_server' => 'Failed to connect to database server. Error: {error}',
    'db_connection_failed_db' => 'Failed to connect to database "{db_name}". Check credentials and if database exists or can be created.',
    'db_validation_errors_occurred' => 'Please correct the validation errors below.',
    'db_creation_success' => 'Database "{db_name}" created successfully or already existed.',
    'db_creation_failed' => 'Failed to create database "{db_name}". Error: {error}. Please check permissions or if it already exists with different settings.',
    'db_tables_creating' => 'Creating database tables...',
    'db_tables_success' => 'Database tables created successfully.',
    'db_tables_failed' => 'Failed to create database tables. Errors: {errors}',
    'db_connection_not_established_for_tables' => 'Database connection not established or not successful. Cannot create tables.',
    'db_connection_failed_for_tables' => 'Failed to connect to database for table creation.',
    'db_schema_file_missing' => 'Database schema file not found at {path}.',
    'error_db_host_empty' => 'Database host cannot be empty.',
    'error_db_name_empty' => 'Database name cannot be empty.',
    'error_db_user_empty' => 'Database user cannot be empty.',
    'error_db_password_empty' => 'Database password cannot be empty (for this installer).', 
    'all_db_checks_passed' => 'Database configured and tables created successfully.',
    'db_config_incomplete' => 'Database configuration is not yet complete or successful.',

    'admin_username' => 'Admin Username',
    'admin_email_label' => 'Admin Email:',
    'admin_password_label' => 'Admin Password:',
    'admin_password' => 'Admin Password', // Used by step_admin_account.php
    'admin_password_confirm' => 'Confirm Admin Password',
    'create_admin_button' => 'Create Admin Account',
    'admin_account_creation_success' => 'Admin account created successfully.',
    'admin_account_creation_failed' => 'Failed to create admin account. Error: {error}',
    'admin_account_validation_errors' => 'Please correct the errors and try again.',
    'error_db_config_missing' => 'Database configuration is missing. Please complete previous steps.',
    'error_db_connection_failed_admin' => 'Failed to connect to database to create admin account.',
    'error_passwords_mismatch' => 'Passwords do not match.',
    'error_password_short' => 'Password must be at least {min_length} characters long.',
    'error_email_invalid' => 'Invalid email address.',
    'error_field_required' => '{field_name} is required.',
    'error_admin_account_duplicate' => 'An account with this username or email already exists. Please choose a different username or email.',
    'admin_account_already_created' => 'Admin account has already been created.',

    'apply_license_button' => 'Apply License Key',
    'license_accepted_message' => 'License key accepted successfully.',
    'license_rejected_message' => 'License key rejected or invalid.',
    'license_validation_failed' => 'License validation failed. Please check the key.',
    'error_license_key_empty' => 'License key cannot be empty.',
    'license_not_yet_validated' => 'Please enter and apply your license key.',
    
    'terms_purchase_title' => 'Purchase Agreement',
    'terms_policy_title' => 'Usage Policy',
    'terms_agree_checkbox_label' => 'I have read and agree to the Purchase Agreement and Usage Policy.',
    'error_terms_not_agreed' => 'You must agree to the terms and conditions to continue.',
    'activation_log_success' => 'Activation log created successfully in the "install" directory.',
    'activation_log_failed_dir_not_writable' => 'Failed to create activation log. The installation directory ("install/") is not writable. Please check permissions for: {path}',
    'activation_log_failed_file_write' => 'Failed to create activation log. Could not write to file: {path}',
    'terms_content_not_found' => 'Terms content file not found: {filepath}', 
    'terms_content_placeholder_purchase' => 'Placeholder for Purchase Agreement. The content file is missing or unreadable.', 
    'terms_content_placeholder_policy' => 'Placeholder for Usage Policy. The content file is missing or unreadable.', 
    'terms_initial_message' => 'Please review and agree to the terms and policies below.', 
    
    // New placeholders for step content from this subtask
    'system_requirements_content_placeholder' => 'System requirements checks will be displayed here.',
    'database_configuration_content_placeholder' => 'Database connection and setup form will be here.',
    'admin_account_content_placeholder' => 'Administrator account creation form will be here.',
    'license_key_content_placeholder' => 'License key entry form will be here.',
    'terms_conditions_content_placeholder' => 'Terms and conditions text will be displayed here with an agreement checkbox.',
    'finish_content_placeholder' => 'Final installation actions and summary will be shown here.',

    // Placeholders for navigation buttons (can be replaced by existing 'button_next', etc. if preferred)
    // For clarity in this step, using distinct keys as per prompt, can be merged later.
    'button_previous_placeholder' => 'Previous Step',
    'button_next_placeholder' => 'Next Step',
    'button_install_placeholder' => 'Finalize Installation',
];
?>
