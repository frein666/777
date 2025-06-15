<?php

/**
 * Database Configuration and Connection Script
 *
 * This script defines the parameters for connecting to a MySQL database,
 * provides functions to access the configuration and the PDO connection object.
 */

// Database connection parameters are stored in an array for better organization.
$config = [
    'host'     => 'localhost',        // Database host: 'localhost' or an IP address.
    'dbname'   => 'messenger_db',   // Database name for the application.
    'username' => 'root',         // MySQL username (use environment variables or secure config for production).
    'password' => '',             // MySQL password (use environment variables or secure config for production).
    'charset'  => 'utf8mb4',       // Recommended charset for modern MySQL to support a wide range of characters.
];

/**
 * Returns the database configuration array.
 * This function allows other parts of the application to access database settings if needed.
 *
 * @return array The database configuration array.
 */
function get_database_config(): array {
    global $config; // Access the global $config variable defined in this file.
    return $config;
}

/**
 * Establishes and returns a PDO database connection.
 *
 * This function uses a static variable to ensure that the database connection
 * is established only once per request (Singleton pattern for the connection).
 * It uses the configuration defined in this file.
 *
 * @return PDO|null Returns a PDO object on successful connection.
 *                  Returns null and terminates script execution (via die()) on failure.
 *                  In a more robust application, you might throw an exception or handle errors differently.
 */
function getPDOConnection(): ?PDO {
    static $pdo = null; // Static variable to store the PDO connection object.

    // Only attempt to connect if a connection hasn't been established yet.
    if ($pdo === null) {
        $dbConfig = get_database_config(); // Get configuration details.

        // Construct the Data Source Name (DSN) for PDO.
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

        // Define PDO connection options for error handling, fetch mode, and prepared statement emulation.
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays.
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation of prepared statements for security and performance.
        ];

        try {
            // Attempt to create a new PDO instance.
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        } catch (\PDOException $e) {
            // Log the detailed error message for server administrators/developers.
            error_log("Database Connection Error: " . $e->getMessage());

            // Display a more generic error message to the user and terminate.
            // In a production web application, you might render an error page or return a JSON response.
            // For a CLI script, die() might be acceptable.
            die("Database Connection Error: Unable to connect to the database. " .
                "Details have been logged. Please check your configuration (config/database.php) " .
                "and ensure the database server is operational.");
            // The return null below is unreachable due to die() but satisfies static analysis for ?PDO return type.
            // return null;
        }
    }

    return $pdo; // Return the established PDO connection.
}

// If this file is included directly (e.g., not via a function call that uses getPDOConnection()),
// it will return the configuration array. This can be useful in some contexts.
return $config;

?>
