<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <!-- Если у вас есть CSS файл, раскомментируйте следующую строку -->
    <!-- <link rel="stylesheet" href="css/style.css"> -->
</head>
<body>
    <h2>Регистрация нового пользователя</h2>

    <?php
    // session_start(); // Раскомментируйте, если используете сессии для отображения ошибок
    // if (isset($_SESSION['register_errors'])) {
    //     echo '<div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px;">';
    //     echo '<strong>Обнаружены следующие ошибки:</strong><br>';
    //     foreach ($_SESSION['register_errors'] as $error) {
    //         echo htmlspecialchars($error) . '<br>';
    //     }
    //     echo '</div>';
    //     unset($_SESSION['register_errors']); // Очистить ошибки после отображения
    //     // Также можно восстановить введенные данные, если они сохранены в сессии
    //     // $username_value = isset($_SESSION['form_inputs']['username']) ? htmlspecialchars($_SESSION['form_inputs']['username']) : '';
    //     // $email_value = isset($_SESSION['form_inputs']['email']) ? htmlspecialchars($_SESSION['form_inputs']['email']) : '';
    //     // unset($_SESSION['form_inputs']);
    // }
    ?>

    <form action="../src/auth/register_handler.php" method="POST">
        <div>
            <label for="username">Логин:</label><br>
            <input type="text" id="username" name="username" value="<?php /* echo $username_value ?? ''; */ ?>" required>
        </div>
        <br>
        <div>
            <label for="email">Электронная почта:</label><br>
            <input type="email" id="email" name="email" value="<?php /* echo $email_value ?? ''; */ ?>" required>
        </div>
        <br>
        <div>
            <label for="password">Пароль (минимум 8 символов):</label><br>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        <br>
        <div>
            <label for="password_confirm">Подтвердите пароль:</label><br>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
        </div>
        <br>
        <div>
            <button type="submit">Зарегистрироваться</button>
        </div>
    </form>
    <br>
    <div>
        <p><small>Нажимая "Зарегистрироваться", вы соглашаетесь с нашими <a href="rules.html">Правилами регистрации</a> и <a href="privacy.html">Политикой конфиденциальности</a>.</small></p>
    </div>

    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    <!-- login.php будет создан позже -->

</body>
</html>
