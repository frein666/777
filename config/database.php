<?php

/**
 * Database Configuration and Connection Script
 *
 * This script defines the parameters for connecting to a MySQL database
 * using PDO (PHP Data Objects). It attempts to establish a connection
 * and returns the PDO object. Includes basic error handling.
 */

// Database connection parameters
$host = 'localhost';        // Typically 'localhost' or an IP address
$dbname = 'messenger_db';   // The name of your database
$username = 'root';         // Your MySQL username (placeholder)
$password = '';             // Your MySQL password (placeholder)
$charset = 'utf8mb4';       // Recommended charset for MySQL

// Data Source Name (DSN)
// Specifies the database driver, host, database name, and charset.
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO connection options
// These options configure how PDO handles errors, fetches results, and emulates prepares.
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays instead of numeric
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation of prepared statements for security and performance
];

// Attempt to create a PDO instance (connect to the database)
try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // If you need to test the connection, you can uncomment the line below.
    // echo "Database connected successfully!";
} catch (\PDOException $e) {
    // In a production environment, it's crucial to log this error securely
    // and display a user-friendly message instead of the raw error details.
    error_log("Database Connection Error: " . $e->getMessage());

    // For development purposes, displaying the error can be helpful.
    // For a live application, replace this with a generic error message.
    // Example: die("A database error occurred. Please try again later or contact support.");
    die("Database Connection Error: " . $e->getMessage());
}

// The PDO object ($pdo) is now available for use in other parts of the application.
// For example, you might return it from this script if it's included,
// or store it in a global variable or a dependency injection container.

// To make the PDO object easily accessible, you could return it:
// return $pdo;

// Or define a function to get the PDO instance:
/*
function getPDO() {
    global $pdo; // Or handle $pdo scope differently (e.g., static variable, registry)
    if ($pdo === null) {
        // Logic to initialize connection if not already done (though this script does it above)
    }
    return $pdo;
}
*/

?>
