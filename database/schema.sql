-- SQL schema for the PHP VoIP and chat application

-- Users table: Stores information about registered users.
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Messages table: Stores text and voice messages exchanged between users.
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL, -- For direct messages.
    message_text TEXT,
    voice_message_path VARCHAR(255) NULL, -- Path to the stored voice message file
    message_type ENUM('text', 'voice') NOT NULL DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Calls table: Stores information about voice/video calls made between users.
CREATE TABLE calls (
    id INT PRIMARY KEY AUTO_INCREMENT,
    caller_id INT NOT NULL,
    receiver_id INT NOT NULL,
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    duration_seconds INT NULL, -- Calculated from start_time and end_time
    status ENUM('initiated', 'answered', 'declined', 'completed', 'missed', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caller_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);
