<?php

/**
 * Messenger Installation Script
 *
 * This script handles the initial setup of the database for the PHP Messenger application.
 * It performs the following steps:
 * 1. Checks for required configuration and schema files.
 * 2. Loads database configuration.
 * 3. Attempts to create the specified database if it doesn't already exist.
 * 4. Connects to the database and applies the schema (creates tables).
 *
 * !!! SECURITY WARNING !!!
 * This file should be DELETED or made INACCESSIBLE after successful installation.
 */

// Prevent direct access if already installed (simple check, can be more robust)
// if (file_exists(__DIR__ . '/config/install.lock')) {
//    die("<h1>Установщик Мессенджера</h1><p style='color:orange;'>Приложение уже установлено. Удалите файл 'config/install.lock' для повторной установки (не рекомендуется без резервной копии).</p>");
// }

echo "<!DOCTYPE html><html lang='ru'><head><meta charset='UTF-8'><title>Установщик Мессенджера</title>";
echo "<style>body{font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background-color: #f4f4f4; color: #333;} h1,h2{color: #007bff;} pre{background-color: #eee; padding: 10px; border:1px solid #ccc; white-space: pre-wrap; word-wrap: break-word;} .error{color:red; border: 1px solid red; padding:10px; background-color:#ffebee;} .success{color:green; border: 1px solid green; padding:10px; background-color:#e8f5e9;} .warning{color:orange; border: 1px solid orange; padding:10px; background-color:#fff3e0;}</style>";
echo "</head><body>";
echo "<h1>Установщик Мессенджера</h1>";

// Define paths to configuration and schema files
$configPath = __DIR__ . '/config/database.php';
$schemaPath = __DIR__ . '/database/schema.sql';

// --- Step 1: Check for necessary files ---
echo "<h2>Шаг 1: Проверка наличия файлов...</h2>";
if (!file_exists($configPath)) {
    die("<p class='error'>Ошибка: Файл конфигурации базы данных (<code>" . htmlspecialchars($configPath) . "</code>) не найден. Установка прервана.</p></body></html>");
}
echo "<p class='success'>Файл конфигурации (<code>" . htmlspecialchars($configPath) . "</code>) найден.</p>";

if (!file_exists($schemaPath)) {
    die("<p class='error'>Ошибка: Файл схемы базы данных (<code>" . htmlspecialchars($schemaPath) . "</code>) не найден. Установка прервана.</p></body></html>");
}
echo "<p class='success'>Файл схемы (<code>" . htmlspecialchars($schemaPath) . "</code>) найден.</p>";

// --- Step 2: Load database configuration ---
echo "<h2>Шаг 2: Загрузка конфигурации базы данных...</h2>";
// config/database.php returns an array of configuration settings
$dbConfig = require $configPath;

if (!is_array($dbConfig) ||
    empty($dbConfig['host']) ||
    empty($dbConfig['dbname']) ||
    !isset($dbConfig['username']) || // username can be empty string for some MySQL setups
    !isset($dbConfig['password'])    // password can be empty string
   ) {
    die("<p class='error'>Ошибка: Файл конфигурации <code>config/database.php</code> не вернул корректный массив настроек или отсутствуют обязательные ключи (host, dbname, username, password).</p></body></html>");
}
echo "<p class='success'>Конфигурация базы данных успешно загружена:</p>";
echo "<pre>" . htmlspecialchars(print_r($dbConfig, true)) . "</pre>";

// --- Step 3: Attempt to create the database (if it doesn't exist) ---
$dbName = $dbConfig['dbname'];
echo "<h2>Шаг 3: Создание базы данных '<code>" . htmlspecialchars($dbName) . "</code>' (если она не существует)...</h2>";
$pdoMaster = null; // PDO connection to MySQL server (without selecting a database initially)

try {
    // Connect to the MySQL server (without specifying a dbname in DSN)
    $dsnMaster = "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}";
    $pdoMasterOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdoMaster = new PDO($dsnMaster, $dbConfig['username'], $dbConfig['password'], $pdoMasterOptions);

    // SQL to create the database if it doesn't exist
    // Using backticks around database name for safety, though usually not strictly needed for valid names
    $pdoMaster->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$dbConfig['charset']} COLLATE utf8mb4_unicode_ci");
    echo "<p class='success'>База данных '<code>" . htmlspecialchars($dbName) . "</code>' успешно создана или уже существует.</p>";

} catch (PDOException $e) {
    echo "<p class='error'>Ошибка при попытке создать базу данных '<code>" . htmlspecialchars($dbName) . "</code>': " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='warning'>Пожалуйста, убедитесь, что пользователь MySQL '<code>" . htmlspecialchars($dbConfig['username']) . "</code>' имеет права на создание баз данных, или создайте базу данных '<code>" . htmlspecialchars($dbName) . "</code>' вручную, а затем повторите попытку установки (или просто обновите эту страницу, если БД уже создана).</p>";
    // We don't die here, as the user might create the DB manually and re-run. Step 4 will try to connect.
}
$pdoMaster = null; // Close the master connection

// --- Step 4: Connect to the specific database and apply the schema ---
echo "<h2>Шаг 4: Подключение к базе данных '<code>" . htmlspecialchars($dbName) . "</code>' и применение схемы...</h2>";
$pdoApp = null; // PDO connection to the application's database

try {
    // We require the config file again to ensure getPDOConnection() is defined in this scope
    // if it wasn't already (e.g., if script execution was interrupted and restarted).
    require_once $configPath;

    // Use the getPDOConnection function which now connects to the specific dbname
    $pdoApp = getPDOConnection(); // This function is defined in config/database.php

    if (!$pdoApp) {
        // This case should ideally be handled by getPDOConnection's die() statement,
        // but as a fallback:
        die("<p class='error'>Не удалось подключиться к базе данных '<code>" . htmlspecialchars($dbName) . "</code>' с использованием <code>getPDOConnection()</code>. Проверьте настройки и сообщения об ошибках выше.</p></body></html>");
    }
    echo "<p class='success'>Успешное подключение к базе данных '<code>" . htmlspecialchars($dbName) . "</code>'.</p>";

    echo "<p>Чтение SQL схемы из файла '<code>" . htmlspecialchars($schemaPath) . "</code>'...</p>";
    $sqlSchema = file_get_contents($schemaPath);
    if ($sqlSchema === false) {
        die("<p class='error'>Ошибка: Не удалось прочитать содержимое файла схемы '<code>" . htmlspecialchars($schemaPath) . "</code>'.</p></body></html>");
    }

    // Basic cleaning of SQL: remove comments to prevent issues with some PDO drivers or complex statements.
    $sqlSchema = preg_replace('/--.*$/m', '', $sqlSchema);      // Remove single-line SQL comments
    $sqlSchema = preg_replace('/\/\*.*?\*\//s', '', $sqlSchema); // Remove multi-line SQL comments
    $sqlSchema = trim($sqlSchema);                               // Trim whitespace

    if (empty($sqlSchema)) {
        die("<p class='warning'>Файл схемы '<code>" . htmlspecialchars($schemaPath) . "</code>' пуст или содержит только комментарии. Таблицы не будут созданы.</p></body></html>");
    }

    echo "<p>Выполнение SQL команд для создания таблиц...</p>";
    // Split the schema into individual statements (semicolon as delimiter)
    // array_filter removes any empty statements that might result from multiple semicolons or comments.
    $statements = array_filter(array_map('trim', explode(';', $sqlSchema)));
    $tablesCreatedCount = 0;
    $errorsEncountered = 0;

    foreach ($statements as $statement) {
        if (empty($statement)) {
            continue;
        }
        try {
            $pdoApp->exec($statement);
            // Try to extract table name for logging (basic regex)
            if (preg_match('/CREATE TABLE(?: IF NOT EXISTS)?\s*`?([a-zA-Z0-9_]+)`?/i', $statement, $matches)) {
                echo "<p style='color:green;'>&check; Таблица '<code>" . htmlspecialchars($matches[1]) . "</code>' успешно создана или уже существует.</p>";
                $tablesCreatedCount++;
            } else {
                // For other types of statements (ALTER, INSERT, etc., if any in schema)
                // echo "<p style='color:blue;'>Выполнен SQL оператор: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Ошибка при выполнении SQL оператора: " . htmlspecialchars($e->getMessage()) . "<br>Запрос: <code>" . htmlspecialchars($statement) . "</code></p>";
            $errorsEncountered++;
        }
    }

    if ($errorsEncountered > 0) {
        echo "<p class='warning'><strong>При создании таблиц возникли ошибки.</strong> Пожалуйста, просмотрите сообщения выше.</p>";
    } elseif ($tablesCreatedCount > 0) {
        echo "<p class='success'><strong>Установка таблиц базы данных успешно завершена! ({$tablesCreatedCount} таблиц(ы) обработано)</strong></p>";
        // Create a lock file to indicate installation is done (simple mechanism)
        // file_put_contents(__DIR__ . '/config/install.lock', 'Installed on: ' . date('Y-m-d H:i:s'));
    } else {
        echo "<p class='warning'>Не было создано ни одной таблицы или не удалось определить создание таблиц. Проверьте файл схемы и сообщения об ошибках выше.</p>";
    }

} catch (PDOException $e) {
    // Catch errors from getPDOConnection() or other general PDO issues in this step.
    die("<p class='error'>Критическая ошибка на Шаге 4 (подключение к БД '<code>" . htmlspecialchars($dbName) . "</code>' или выполнение схемы): " . htmlspecialchars($e->getMessage()) . "</p></body></html>");
}

echo "<h2>Установка завершена!</h2>";
echo "<p class='warning'><strong>Важно:</strong> Из соображений безопасности, пожалуйста, <strong>УДАЛИТЕ</strong> этот файл (<code>install.php</code>) с вашего сервера или сделайте его недоступным через веб.</p>";
echo "</body></html>";

?>
