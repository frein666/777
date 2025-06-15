<?php
// websocket_server.php

// This script is intended to be run from the root of the project:
// php src/WebSocket/websocket_server.php

// Adjust the path to Composer's autoload.php
// __DIR__ is the directory of the current file (src/WebSocket)
// So, __DIR__ . '/../../vendor/autoload.php' should correctly point to vendor/autoload.php from the project root.
$autoloader = require __DIR__ . '/../../vendor/autoload.php';

if (!$autoloader) {
    echo "Failed to load Composer's autoloader. \n";
    echo "Please make sure you have run 'composer install' in the project root.\n";
    echo "Path checked: " . realpath(__DIR__ . '/../../vendor/autoload.php') . "\n";
    exit(1); // Exit with an error code
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\SignalingServer; // Ensure this namespace matches your SignalingServer.php and composer.json

// Default port for the WebSocket server
$port = 8080;

// Check if a port is provided as a command-line argument
if (isset($argv[1])) {
    $cliPort = filter_var($argv[1], FILTER_VALIDATE_INT);
    if ($cliPort !== false && $cliPort > 0 && $cliPort < 65536) {
        $port = $cliPort;
    } else {
        echo "Warning: Invalid port number '{$argv[1]}' provided. Using default port {$port}.\n";
    }
}

echo "Attempting to start WebSocket server on port {$port}...\n";

try {
    // Create the main IoServer instance.
    // It wraps our SignalingServer in the WsServer (WebSocket protocol handler)
    // and then in the HttpServer (to handle HTTP handshake).
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new SignalingServer() // Your application logic for WebSocket communication
            )
        ),
        $port,
        '0.0.0.0' // Listen on all available network interfaces, not just localhost
    );

    echo "WebSocket server started successfully on 0.0.0.0:{$port}\n";
    echo "You can connect to ws://your_server_ip:{$port}?userId=your_user_id\n";
    echo "Press Ctrl+C to stop the server.\n";

    // Run the server loop
    $server->run();

} catch (\React\Socket\ConnectionException $e) {
    echo "Could not start WebSocket server: {$e->getMessage()}\n";
    echo "This might be due to the port {$port} already being in use or insufficient permissions.\n";
    echo "Try checking if another service is running on port {$port} (e.g., using 'sudo netstat -tulnp | grep {$port}') or try a different port.\n";
    exit(1);
} catch (\Throwable $e) { // Catch any other general errors
    echo "An unexpected error occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    // echo "Stack trace: \n" . $e->getTraceAsString() . "\n"; // Uncomment for debugging if needed
    exit(1);
}
?>
