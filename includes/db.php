<?php

define('DB_CHARSET', 'utf8mb4');

function connectDB($host, $db_name, $username, $password) {
    // Create connection
    $conn = new mysqli($host, $username, $password, $db_name);

    // Check connection
    if ($conn->connect_error) {
        // In a real application, it's better to log the error here, not output it directly
        // error_log("Connection failed: " . $conn->connect_error);
        return false;
    }

    // Set character set
    if (!$conn->set_charset(DB_CHARSET)) {
        // Error setting character set
        // error_log("Error loading character set " . DB_CHARSET . ": " . $conn->error);
        $conn->close(); // Close connection if charset setting fails
        return false;
    }

    return $conn;
}

// Example usage (for testing, then remove or comment out):
/*
$connection = connectDB("localhost", "test_db", "root", "password");
if ($connection) {
    echo "Successfully connected to the database with charset " . $connection->character_set_name() . "!";
    // Perform other database operations here
    $connection->close();
} else {
    echo "Failed to connect to the database.";
}
*/
?>
