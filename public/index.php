<?php
session_start(); // Start or resume session to access user data

// Check if the user is logged in by verifying if 'user_id' is set in the session.
// If not, redirect them to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page
    exit; // Stop further script execution
}

// Retrieve the username from the session and sanitize it for safe display.
// htmlspecialchars() prevents XSS attacks.
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Пользователь';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мессенджер</title>
    <!-- Link to an external CSS file if you prefer (e.g., css/style.css) -->
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f4f7f6;
        }
        header {
            background-color: #007bff; /* Primary color */
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #0056b3; /* Darker shade for border */
        }
        header h1 { margin: 0; font-size: 1.5em; }
        header a { color: white; text-decoration: none; padding: 8px 15px; background-color: #0056b3; border-radius: 5px; }
        header a:hover { background-color: #004085; }
        .welcome-message { font-size: 0.9em; }
        .container {
            display: flex;
            flex-grow: 1;
            overflow: hidden; /* Important for controlling scrolling within panes */
        }
        .sidebar {
            width: 280px;
            border-right: 1px solid #ccc;
            padding: 15px;
            background-color: #ffffff;
            overflow-y: auto; /* Allow scrolling for contact list */
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .sidebar h3 { margin-top: 0; color: #333; }
        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #e9ecef; /* Light gray for chat background */
        }
        .chat-window {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto; /* Allow scrolling for messages */
            border-bottom: 1px solid #ccc;
        }
        .message-input-area {
            padding: 15px;
            background-color: #f8f9fa; /* Slightly different background for input area */
            border-top: 1px solid #ccc;
            display: flex;
            align-items: center;
        }
        .message-input-area textarea {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            resize: none; /* Disable textarea resizing by user */
            min-height: 40px;
            margin-right: 10px;
        }
        .message-input-area button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .message-input-area button:hover { background-color: #0056b3; }
        .placeholder-text {
            color: #6c757d; /* Bootstrap muted color */
            text-align: center;
            margin-top: 20px;
            font-style: italic;
        }
        .future-features-note {
            font-size: 0.85em;
            color: #555;
            text-align: center;
            margin-top: 10px;
            padding: 5px;
            background-color: #e9ecef; /* Match chat area background */
        }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Мессенджер</h1>
            <p class="welcome-message">Добро пожаловать, <?php echo $username; ?>!</p>
        </div>
        <a href="../src/auth/logout_handler.php">Выйти</a>
    </header>

    <div class="container">
        <aside class="sidebar">
            <h3>Список контактов</h3>
            <div class="placeholder-text">(Здесь будет отображаться список ваших контактов)</div>
            <!-- Example contact item (can be generated dynamically later) -->
            <!--
            <ul id="contact-list">
                <li>Контакт 1</li>
                <li>Контакт 2</li>
            </ul>
            -->
        </aside>

        <main class="chat-area">
            <section class="chat-window" id="chat-window">
                <div class="placeholder-text">(Выберите контакт, чтобы начать чат или просмотреть историю сообщений)</div>
                <!-- Messages will appear here, e.g.: -->
                <!--
                <div class="message self">Привет!</div>
                <div class="message other">Привет, как дела?</div>
                -->
            </section>

            <section class="message-input-area">
                <textarea id="message-input" placeholder="Напишите сообщение..." rows="2"></textarea>
                <button id="send-button">Отправить</button>
            </section>
            <div class="future-features-note">
                <p><em>Скоро здесь появятся функции голосовых сообщений и звонков!</em></p>
            </div>
        </main>
    </div>

    <!-- JavaScript for interactivity will be added later (e.g., js/main.js or inline) -->
    <!-- <script src="js/main.js"></script> -->
    <script>
        // Basic placeholder for sending a message (will be expanded later)
        // document.getElementById('send-button').addEventListener('click', function() {
        //     const messageInput = document.getElementById('message-input');
        //     const messageText = messageInput.value.trim();
        //     if (messageText) {
        //         const chatWindow = document.getElementById('chat-window');
        //         const messageElement = document.createElement('div');
        //         messageElement.textContent = "Вы: " + messageText; // Simple display
        //         chatWindow.appendChild(messageElement);
        //         messageInput.value = ''; // Clear input
        //         chatWindow.scrollTop = chatWindow.scrollHeight; // Scroll to bottom
        //     }
        // });
    </script>
</body>
</html>
