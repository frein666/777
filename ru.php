<?php
$lang = [
    // Placeholders from this subtask's requirements
    'installer_title_placeholder' => 'Установщик',
    'select_language_placeholder' => 'Выберите язык:',
    'language_en_placeholder' => 'Английский (США)',
    'language_ru_placeholder' => 'Русский',
    'welcome_message_placeholder' => 'Добро пожаловать в установщик!',
    'just_a_test_placeholder' => 'Это тест системы переводов.',

    // Existing keys from previous tasks (to avoid losing them)
    'installer_title' => 'Установщик Discord Клона',
    'step_language_selection' => 'Выбор языка',
    'step_system_requirements' => 'Системные требования',
    'step_database_configuration' => 'Настройка базы данных',
    'step_admin_account' => 'Аккаунт администратора',
    'step_license_key' => 'Лицензионный ключ',
    'step_terms_conditions' => 'Условия использования',
    'step_finish' => 'Завершение',

    'select_language' => 'Выберите язык:',
    'language_en' => 'English (Английский)', // Note: Original 'language_en' was 'English (Английский)'
    'language_ru' => 'Русский',

    'button_next' => 'Далее',
    'button_previous' => 'Назад',
    'button_install' => 'Установить',

    'system_requirements_title' => 'Системные требования',
    'php_version_check' => 'Версия PHP', // Corrected
    'mysqli_extension_check' => 'Расширение MySQLi',
    'write_permissions_check' => 'Права на запись (config.php)',
    'check_status_ok' => 'ОК',
    'check_status_missing' => 'Отсутствует (Пример)',

    'db_host_label' => 'Хост базы данных:',
    'db_name_label' => 'Имя базы данных:',
    'db_user_label' => 'Пользователь базы данных:',
    'db_password_label' => 'Пароль базы данных:',
    'db_host_placeholder' => 'localhost',
    
    'admin_email_label' => 'Email администратора:',
    'admin_password_label' => 'Пароль администратора:',

    // 'license_key_label' => 'Лицензионный ключ:', // is step_license_key

    'terms_agree_label' => 'Я согласен с условиями использования.', // Old key
    'terms_placeholder' => 'Это пример текста условий использования. Устанавливая флажок ниже, вы соглашаетесь с нашими условиями предоставления услуг.',

    'finish_message' => 'Установка готова к запуску на основе вашей конфигурации.',
    'finish_language' => 'Язык:',
    'finish_db_host' => 'Хост БД:',
    'finish_db_name' => 'Имя БД:',
    'finish_admin_email' => 'Email администратора:',
    'finish_license' => 'Лицензионный ключ:',
    'finish_terms_agreed' => 'С условиями согласен:',
    'finish_status_not_set' => 'Не установлено',
    'finish_status_yes' => 'Да',
    'finish_status_no' => 'Нет',
    'installation_progress' => 'Идет установка...',
    'installation_complete' => 'Установка завершена!',
    'installation_success_message' => 'Приложение успешно установлено! Пожалуйста, удалите директорию /install.',

    // For step_requirements.php
    'php_version_error' => 'Ошибка: Ваша версия PHP {current_version}. Требуется версия {required_version} или выше.',
    'php_version_ok' => 'ОК (Версия: {current_version})',
    'php_extension_check' => 'Расширение PHP: {extension_name}',
    'php_extension_error' => 'Ошибка: Расширение PHP "{extension_name}" не загружено. Пожалуйста, установите или включите его.',
    'php_extension_ok' => 'ОК (Загружено)',
    'dir_permissions_check' => 'Права на запись в директорию: {dir_name}',
    'dir_permissions_error' => 'Ошибка: Директория "{dir_name}" не доступна для записи. Пожалуйста, проверьте права.',
    'dir_permissions_ok' => 'ОК (Доступно для записи)',
    'dir_not_exists_error' => 'Ошибка: Директория "{dir_name}" не существует или недоступна.',
    'install_dir_name' => 'Директория установки (install/)',
    'app_root_dir_name' => 'Корневая директория приложения (для config.php)',
    'all_requirements_passed' => 'Все системные требования соблюдены. Вы можете перейти к следующему шагу.',
    'requirements_failed' => 'Некоторые системные требования не соблюдены. Пожалуйста, исправьте проблемы и попробуйте снова.',
    'language_en_name' => 'English (US)', 
    'language_ru_name' => 'Русский (Russian)',

    // For step_db_config.php
    'db_connect_button' => 'Проверить соединение и сохранить',
    'db_connection_testing' => 'Попытка подключения к базе данных "{db_name}"...',
    'db_connection_success' => 'Успешное подключение к базе данных "{db_name}".',
    'db_connection_failed_server' => 'Не удалось подключиться к серверу базы данных. Ошибка: {error}',
    'db_connection_failed_db' => 'Не удалось подключиться к базе данных "{db_name}". Проверьте учетные данные и существует ли база данных или может ли она быть создана.',
    'db_validation_errors_occurred' => 'Пожалуйста, исправьте ошибки валидации ниже.',
    'db_creation_success' => 'База данных "{db_name}" успешно создана или уже существовала.',
    'db_creation_failed' => 'Не удалось создать базу данных "{db_name}". Ошибка: {error}. Пожалуйста, проверьте права доступа или не существует ли она уже с другими настройками.',
    'db_tables_creating' => 'Создание таблиц базы данных...',
    'db_tables_success' => 'Таблицы базы данных успешно созданы.',
    'db_tables_failed' => 'Не удалось создать таблицы базы данных. Ошибки: {errors}',
    'db_connection_not_established_for_tables' => 'Соединение с базой данных не установлено или не успешно. Невозможно создать таблицы.',
    'db_connection_failed_for_tables' => 'Не удалось подключиться к базе данных для создания таблиц.',
    'db_schema_file_missing' => 'Файл схемы базы данных не найден по пути {path}.',
    'error_db_host_empty' => 'Хост базы данных не может быть пустым.',
    'error_db_name_empty' => 'Имя базы данных не может быть пустым.',
    'error_db_user_empty' => 'Пользователь базы данных не может быть пустым.',
    'error_db_password_empty' => 'Пароль базы данных не может быть пустым (для этого установщика).', 
    'all_db_checks_passed' => 'База данных настроена и таблицы успешно созданы.',
    'db_config_incomplete' => 'Конфигурация базы данных еще не завершена или не успешна.',

    // For step_admin_account.php
    'admin_username' => 'Имя пользователя администратора',
    // 'admin_email' => 'Email администратора', // Already admin_email_label
    'admin_password' => 'Пароль администратора',
    'admin_password_confirm' => 'Подтвердите пароль администратора',
    'create_admin_button' => 'Создать аккаунт администратора',
    'admin_account_creation_success' => 'Аккаунт администратора успешно создан.',
    'admin_account_creation_failed' => 'Не удалось создать аккаунт администратора. Ошибка: {error}',
    'admin_account_validation_errors' => 'Пожалуйста, исправьте ошибки и попробуйте снова.',
    'error_db_config_missing' => 'Конфигурация базы данных отсутствует. Пожалуйста, завершите предыдущие шаги.',
    'error_db_connection_failed_admin' => 'Не удалось подключиться к базе данных для создания аккаунта администратора.',
    'error_passwords_mismatch' => 'Пароли не совпадают.',
    'error_password_short' => 'Пароль должен содержать не менее {min_length} символов.',
    'error_email_invalid' => 'Неверный формат email адреса.',
    'error_field_required' => 'Поле "{field_name}" обязательно для заполнения.',
    'error_admin_account_duplicate' => 'Аккаунт с таким именем пользователя или email уже существует. Пожалуйста, выберите другие данные.',
    'admin_account_already_created' => 'Аккаунт администратора уже создан.',

    // For step_license.php
    'apply_license_button' => 'Применить лицензионный ключ',
    'license_accepted_message' => 'Лицензионный ключ успешно принят.',
    'license_rejected_message' => 'Лицензионный ключ отклонен или недействителен.',
    'license_validation_failed' => 'Проверка лицензионного ключа не удалась. Пожалуйста, проверьте ключ.',
    'error_license_key_empty' => 'Лицензионный ключ не может быть пустым.',
    'license_not_yet_validated' => 'Пожалуйста, введите и примените ваш лицензионный ключ.',
    
    // For step_terms.php
    'terms_purchase_title' => 'Соглашение о покупке',
    'terms_policy_title' => 'Политика использования',
    'terms_agree_checkbox_label' => 'Я прочитал(а) и согласен(на) с Соглашением о покупке и Политикой использования.',
    'error_terms_not_agreed' => 'Вы должны согласиться с условиями для продолжения.',
    'activation_log_success' => 'Лог активации успешно создан в директории "install".',
    'activation_log_failed_dir_not_writable' => 'Не удалось создать лог активации. Директория "install" недоступна для записи. Пожалуйста, проверьте права для: {path}',
    'activation_log_failed_file_write' => 'Не удалось создать лог активации. Ошибка записи в файл: {path}',
    'terms_content_not_found' => 'Файл с текстом условий не найден: {filepath}', 
    'terms_content_placeholder_purchase' => 'Заполнитель для Соглашения о покупке. Файл с содержанием отсутствует или нечитаем.', 
    'terms_content_placeholder_policy' => 'Заполнитель для Политики использования. Файл с содержанием отсутствует или нечитаем.', 
    'terms_initial_message' => 'Пожалуйста, ознакомьтесь и согласитесь с условиями и политиками ниже.', 
    
    // New placeholders for step content from this subtask
    'system_requirements_content_placeholder' => 'Проверки системных требований будут отображены здесь.',
    'database_configuration_content_placeholder' => 'Форма настройки соединения с базой данных будет здесь.',
    'admin_account_content_placeholder' => 'Форма создания аккаунта администратора будет здесь.',
    'license_key_content_placeholder' => 'Форма ввода лицензионного ключа будет здесь.',
    'terms_conditions_content_placeholder' => 'Текст условий использования и флажок согласия будут отображены здесь.',
    'finish_content_placeholder' => 'Завершающие действия установки и сводка будут показаны здесь.',

    // Placeholders for navigation buttons
    'button_previous_placeholder' => 'Предыдущий шаг',
    'button_next_placeholder' => 'Следующий шаг',
    'button_install_placeholder' => 'Завершить установку',
];
?>
