<?php

function isEmpty($data) {
    return empty(trim($data));
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function passwordsMatch($password, $confirm_password) {
    return $password === $confirm_password;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data); // Удаляет экранирующие слеши
    $data = htmlspecialchars($data); // Преобразует специальные символы в HTML-сущности
    return $data;
}

// Можно добавить и другие функции по необходимости
?>
