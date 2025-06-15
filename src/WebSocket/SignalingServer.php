<?php
namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use PDO; // For database operations
use DateTime; // For duration calculation

class SignalingServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections; // [userId => ['conn' => ConnectionInterface, 'username' => string], ...]
    protected $connectionUsers; // [connectionResourceId => userId, ...]
    protected $dbConfig;        // Database configuration array

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        $this->connectionUsers = [];

        // Load database configuration. config/database.php should return an array.
        // Path is relative to this file's location (src/WebSocket/)
        $configPath = __DIR__ . '/../../config/database.php';
        if (file_exists($configPath)) {
            $this->dbConfig = require $configPath;
            if (!is_array($this->dbConfig)) {
                echo "ERROR: Database configuration file did not return an array: {$configPath}\n";
                $this->dbConfig = null;
            }
        } else {
            echo "ERROR: Database configuration file not found: {$configPath}\n";
            $this->dbConfig = null;
        }

        echo "WebSocket Signaling Server started.\n";
        if ($this->dbConfig) {
            echo "Database configuration loaded. Call logging is enabled.\n";
        } else {
            echo "Database configuration NOT loaded. Call logging will be disabled.\n";
        }
    }

    /**
     * Establishes and returns a PDO database connection.
     * @return PDO|null PDO object on success, null on failure.
     */
    private function getPDO(): ?PDO {
        if (!$this->dbConfig) {
            // echo "Attempted to get PDO, but DB config is not loaded.\n"; // Can be noisy
            return null;
        }
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['dbname']};charset={$this->dbConfig['charset']}";
            $pdo = new PDO($dsn, $this->dbConfig['username'], $this->dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (\PDOException $e) {
            echo "DB Connection Error for logging: " . $e->getMessage() . "\n";
            return null;
        }
    }

    /**
     * Logs call events to the database.
     */
    private function logCall($callerId, $receiverId, $status, $callStartTime = null, $callEndTime = null) {
        if (!$this->dbConfig) {
            // echo "Call logging skipped: DB config missing.\n"; // Can be noisy
            return;
        }

        $pdo = $this->getPDO();
        if (!$pdo) {
            echo "Call logging skipped: Could not connect to DB.\n";
            return;
        }

        $durationSeconds = null;
        if ($callStartTime && $callEndTime) {
            try {
                $start = new DateTime($callStartTime);
                $end = new DateTime($callEndTime);
                $durationSeconds = $end->getTimestamp() - $start->getTimestamp();
                if ($durationSeconds < 0) $durationSeconds = 0; // Ensure non-negative duration
            } catch (\Exception $e) {
                echo "Error calculating call duration: " . $e->getMessage() . "\n";
            }
        }

        // Default times if not provided, though client-provided times are better for accuracy.
        // For 'answered', startTime is crucial. For 'completed'/'declined', endTime is.
        if ($status === 'answered' && $callStartTime === null) $callStartTime = date('Y-m-d H:i:s');
        if (($status === 'completed' || $status === 'declined' || $status === 'missed' || $status === 'failed') && $callEndTime === null) {
             $callEndTime = date('Y-m-d H:i:s');
        }


        // We'll use a simple INSERT. Managing call state for updates (e.g. from 'answered' to 'completed')
        // would require a call ID passed between client and server, or more complex state management here.
        $sql = "INSERT INTO calls (caller_id, receiver_id, start_time, end_time, duration_seconds, status, created_at)
                VALUES (:caller_id, :receiver_id, :start_time, :end_time, :duration_seconds, :status, NOW())";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':caller_id', $callerId, PDO::PARAM_INT);
            $stmt->bindParam(':receiver_id', $receiverId, PDO::PARAM_INT);
            $stmt->bindParam(':start_time', $callStartTime); // Let PDO handle null if not set
            $stmt->bindParam(':end_time', $callEndTime);     // Let PDO handle null if not set
            $stmt->bindParam(':duration_seconds', $durationSeconds, PDO::PARAM_INT); // Let PDO handle null
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->execute();
            echo "Call logged: Caller={$callerId}, Receiver={$receiverId}, Status={$status}\n";
        } catch (\PDOException $e) {
            echo "DB Call Logging Error: " . $e->getMessage() . "\n";
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
        $userId = $queryParams['userId'] ?? null;
        $username = $queryParams['username'] ?? 'User ' . ($userId ?? $conn->resourceId); // Default username

        if ($userId) {
            $numericUserId = filter_var($userId, FILTER_VALIDATE_INT);
            if ($numericUserId === false) {
                 echo "Invalid userId format: {$userId}. Connection {$conn->resourceId} will be anonymous.\n";
                 $userId = null; // Treat as anonymous
            } else {
                $userId = $numericUserId;
            }
        }

        if ($userId) {
            if (isset($this->userConnections[$userId])) {
                $oldConnection = $this->userConnections[$userId]['conn'];
                echo "User {$userId} ('{$this->userConnections[$userId]['username']}') reconnected with new connection {$conn->resourceId}. Closing old connection {$oldConnection->resourceId}.\n";
                $oldConnection->send(json_encode(['type' => 'connection_replaced', 'message' => 'You have connected from another location. This connection is being closed.']));
                $oldConnection->close();
                // No need to manually unset from $connectionUsers, onClose will handle it for oldConnection
            }

            $this->userConnections[$userId] = ['conn' => $conn, 'username' => $username];
            $this->connectionUsers[$conn->resourceId] = $userId;
            echo "Connection {$conn->resourceId} opened for user {$userId} ('{$username}').\n";
            $this->broadcastUserList();
        } else {
            echo "Connection {$conn->resourceId} opened ANONYMOUSLY.\n";
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message from {$from->resourceId}: {$msg}\n";
        $data = json_decode($msg, true);

        if (!$data || !isset($data['type'])) {
            echo "Invalid message format or type not set. Ignoring.\n";
            // $from->send(json_encode(['type' => 'error', 'message' => 'Invalid message format']));
            return;
        }

        $senderId = $this->connectionUsers[$from->resourceId] ?? null;
        if (!$senderId) {
            echo "Message from unidentified connection {$from->resourceId}. Ignoring.\n";
            // $from->send(json_encode(['type' => 'error', 'message' => 'Connection not identified with a userId.']));
            return;
        }

        // Ensure fromUserId and fromUsername are always present and correct
        $data['fromUserId'] = $senderId;
        if (!isset($data['fromUsername']) && isset($this->userConnections[$senderId]['username'])) {
            $data['fromUsername'] = $this->userConnections[$senderId]['username'];
        }


        $targetUserId = $data['targetUserId'] ?? null;
        if ($targetUserId) {
            $targetUserId = filter_var($targetUserId, FILTER_VALIDATE_INT);
            if ($targetUserId === false) {
                echo "Invalid targetUserId format '{$data['targetUserId']}'. Ignoring message type '{$data['type']}'.\n";
                $from->send(json_encode(['type' => 'error', 'message' => "Invalid targetUserId format: {$data['targetUserId']}"]));
                return;
            }
        }


        // Route based on message type
        if (in_array($data['type'], ['offer', 'answer', 'candidate'])) {
            if ($targetUserId && isset($this->userConnections[$targetUserId])) {
                $targetDetails = $this->userConnections[$targetUserId];
                if ($targetDetails['conn'] !== $from) { // Don't send to self
                    echo "Forwarding '{$data['type']}' from user {$senderId} to user {$targetUserId}.\n";
                    $targetDetails['conn']->send(json_encode($data));

                    // Log call initiation on 'offer' and acceptance on 'answer'
                    if ($data['type'] === 'offer') {
                        // caller_id is $senderId, receiver_id is $targetUserId
                        $this->logCall($senderId, $targetUserId, 'initiated');
                    } elseif ($data['type'] === 'answer') {
                        // caller_id is $targetUserId (original offerer), receiver_id is $senderId (who sent answer)
                        $this->logCall($targetUserId, $senderId, 'answered', date('Y-m-d H:i:s'));
                    }
                }
            } else {
                echo "Target user {$targetUserId} for '{$data['type']}' not connected. Message from {$senderId} cannot be delivered.\n";
                $from->send(json_encode(['type' => 'error', 'message' => "User {$targetUserId} is not online."]));
                // If an offer fails because target is offline, log as 'missed' or 'failed'
                if ($data['type'] === 'offer') {
                    $this->logCall($senderId, $targetUserId, 'missed'); // Or 'failed_no_answer'
                }
            }
        } elseif ($data['type'] === 'call_declined') {
            if ($targetUserId && isset($this->userConnections[$targetUserId])) {
                 $this->userConnections[$targetUserId]['conn']->send(json_encode($data));
            }
            // caller_id is $targetUserId (original offerer), receiver_id is $senderId (who declined)
            $this->logCall($targetUserId, $senderId, 'declined', null, date('Y-m-d H:i:s'));
        } elseif ($data['type'] === 'call_ended') {
            if ($targetUserId && isset($this->userConnections[$targetUserId])) {
                $this->userConnections[$targetUserId]['conn']->send(json_encode($data));
            }
            // The one who ends the call is $senderId. The other party is $targetUserId.
            // We need to determine who was caller and receiver from a stored call state or assume roles.
            // For simplicity, let's assume $senderId was involved, and $targetUserId was the other party.
            // This part needs more context (e.g. an active call ID) for accurate logging of caller/receiver.
            // For now, logging with $senderId as one party and $targetUserId as the other.
            // To improve, one could query the last 'answered' call between these two users.
            // For now, just log the event with the participants as they are in the message.
            $this->logCall($senderId, $targetUserId, 'completed', null, date('Y-m-d H:i:s'));
            // Also log for the other direction if needed, or have a more robust call state.
            // $this->logCall($targetUserId, $senderId, 'completed', null, date('Y-m-d H:i:s'));
        } elseif ($data['type'] === 'get_users') {
            $this->sendUserListTo($from);
        } else {
            echo "Unknown message type '{$data['type']}' from user {$senderId}. Ignoring.\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $userId = $this->connectionUsers[$conn->resourceId] ?? null;

        if ($userId) {
            // Check if the connection being closed is the currently active one for this user
            if (isset($this->userConnections[$userId]) && $this->userConnections[$userId]['conn'] === $conn) {
                unset($this->userConnections[$userId]);
                 echo "User {$userId} ('{$username}') connection {$conn->resourceId} closed.\n";
            } else {
                // This might happen if a new connection replaced the old one, and now the old one is closing.
                echo "Old connection {$conn->resourceId} for user {$userId} closed.\n";
            }
            unset($this->connectionUsers[$conn->resourceId]);
            $this->broadcastUserList();
        } else {
            echo "Anonymous connection {$conn->resourceId} closed.\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error on connection {$conn->resourceId}: {$e->getMessage()}\n";

        $userId = $this->connectionUsers[$conn->resourceId] ?? null;
        if ($userId) {
            if (isset($this->userConnections[$userId]) && $this->userConnections[$userId]['conn'] === $conn) {
                unset($this->userConnections[$userId]);
                echo "User {$userId} data cleaned up due to error.\n";
            }
            unset($this->connectionUsers[$conn->resourceId]);
            $this->broadcastUserList();
        }
        $conn->close();
    }

    private function broadcastUserList() {
        if ($this->clients->count() === 0) return; // No one to send to

        $userListPayload = ['type' => 'update_user_list', 'users' => []];
        foreach ($this->userConnections as $uid => $details) {
            // Ensure uid is integer for JS consistency if it comes from numeric strings
            $userListPayload['users'][(int)$uid] = ['username' => $details['username']];
        }

        $jsonPayload = json_encode($userListPayload);
        echo "Broadcasting user list: {$jsonPayload}\n";
        foreach ($this->clients as $client) {
            // Send list only to identified users
            if (isset($this->connectionUsers[$client->resourceId])) {
                 $client->send($jsonPayload);
            }
        }
    }

    private function sendUserListTo(ConnectionInterface $conn) {
        if (!$conn) return;
        $userListPayload = ['type' => 'update_user_list', 'users' => []];
        foreach ($this->userConnections as $uid => $details) {
            $userListPayload['users'][(int)$uid] = ['username' => $details['username']];
        }
        $conn->send(json_encode($userListPayload));
        echo "Sent user list to connection {$conn->resourceId}\n";
    }
}
?>
