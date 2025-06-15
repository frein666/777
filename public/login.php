<?php
session_start(); // Start or resume session to access session variables like errors
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <!-- Если у вас есть CSS файл, раскомментируйте следующую строку -->
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <style>
        .error-message {
            color: red;
            border: 1px solid red;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #ffebee;
        }
        .success-message {
            color: green;
            border: 1px solid green;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #e8f5e9;
        }
    </style>
</head>
<body>
    <h2>Вход в систему</h2>

    <?php
    // Display login errors, if any, passed from login_handler.php
    if (isset($_SESSION['login_errors']) && !empty($_SESSION['login_errors'])) {
        echo '<div class="error-message">';
        echo '<strong>Ошибка входа:</strong><br>';
        foreach ($_SESSION['login_errors'] as $error) {
            echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '<br>';
        }
        echo '</div>';
        unset($_SESSION['login_errors']); // Clear errors after displaying
    }

    // Display registration success message, if redirected from registration
    // This can be set in register_handler.php upon successful registration before redirecting to login
    // Example: $_SESSION['registration_success'] = "Регистрация прошла успешно! Теперь вы можете войти.";
    if (isset($_SESSION['registration_success'])) {
        echo '<div class="success-message">';
        echo htmlspecialchars($_SESSION['registration_success'], ENT_QUOTES, 'UTF-8');
        echo '</div>';
        unset($_SESSION['registration_success']); // Clear the message after displaying
    }

    // Preserve identifier input if login fails and user is redirected back
    $identifier_value = '';
    if (isset($_SESSION['login_form_identifier'])) {
        $identifier_value = htmlspecialchars($_SESSION['login_form_identifier'], ENT_QUOTES, 'UTF-8');
        unset($_SESSION['login_form_identifier']); // Clear after use
    }
    ?>

    <form action="../src/auth/login_handler.php" method="POST">
        <div>
            <label for="identifier">Логин или Email:</label><br>
            <input type="text" id="identifier" name="identifier" value="<?php echo $identifier_value; ?>" required autofocus>
        </div>
        <br>
        <div>
            <label for="password">Пароль:</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <div>
            <button type="submit">Войти</button>
        </div>
    </form>
    <br>
    <p>Еще нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    <!-- Optional: Add a "Forgot Password?" link here later -->
    <!-- <p><a href="forgot_password.php">Забыли пароль?</a></p> -->

</body>
</html>
