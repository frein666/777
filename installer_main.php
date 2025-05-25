<?php
session_start(); // Начало сессии

// Определение языка
$default_lang = 'en';
$available_langs = ['en', 'ru']; // Доступные языки

// Логика выбора языка: POST -> SESSION -> DEFAULT
if (isset($_POST['language_select']) && in_array($_POST['language_select'], $available_langs)) {
    $_SESSION['language'] = $_POST['language_select'];
    // Перенаправление для очистки POST и обновления URL (если мы на каком-то шаге)
    // Пока просто сохраняем в сессию, перезагрузка страницы обработает остальное.
    // В будущем, при наличии шагов: header('Location: ?step=' . $current_step_number); exit;
}

$current_lang_code = $_SESSION['language'] ?? $default_lang;
if (!in_array($current_lang_code, $available_langs)) {
    $current_lang_code = $default_lang; // Фолбэк на язык по умолчанию
}

// Загрузка языкового файла (теперь файлы в корне)
$lang_file_path = __DIR__ . '/' . $current_lang_code . '.php';
if (file_exists($lang_file_path)) {
    require $lang_file_path; // $lang массив теперь доступен
} else {
    // Фолбэк на английский, если файл языка не найден
    $lang_file_path_default = __DIR__ . '/' . $default_lang . '.php';
    if (file_exists($lang_file_path_default)) {
        require $lang_file_path_default;
    } else {
        // Критическая ошибка: нет языковых файлов. Создаем пустой $lang.
        $lang = []; // Это предотвратит ошибки PHP, но интерфейс будет без текста
        // error_log("CRITICAL: Language files not found for lang '{$current_lang_code}' or default '{$default_lang}'.");
    }
}

// Функция для перевода
function t($key) {
    global $lang;
    return $lang[$key] ?? $key; // Возвращает перевод или сам ключ, если перевод не найден
}

// Определение шагов установки
$steps = [
    1 => 'step_language_selection', // Ключ для перевода
    2 => 'step_system_requirements',
    3 => 'step_database_configuration',
    4 => 'step_admin_account',
    5 => 'step_license_key',
    6 => 'step_terms_conditions',
    7 => 'step_finish',
];
$total_steps = count($steps);

// Определение текущего шага из GET-параметра
$current_step_number = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($current_step_number < 1 || $current_step_number > $total_steps) {
    $current_step_number = 1; // По умолчанию на первый шаг, если значение некорректно
}
$current_step_name_key = $steps[$current_step_number]; // Ключ для перевода названия текущего шага

// Обработка навигационных кнопок "Далее" и "Назад"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Язык уже обработан выше
    if (isset($_POST['nextBtn'])) {
        if ($current_step_number < $total_steps) {
            $current_step_number++;
            header('Location: ?step=' . $current_step_number);
            exit;
        }
    } elseif (isset($_POST['prevBtn'])) {
        if ($current_step_number > 1) {
            $current_step_number--;
            header('Location: ?step=' . $current_step_number);
            exit;
        }
    }
    // Другие POST-обработчики (для конкретных шагов) будут добавлены позже
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('installer_title_placeholder'); ?> - <?php echo t($current_step_name_key); ?></title>
    <link rel="stylesheet" href="install.css"> <!-- Adjusted path -->
    <!-- Шрифты и другие специфичные head-элементы будут добавлены по мере необходимости -->
</head>
<body>
    <div class="installer-container">
        <aside class="sidebar">
            <nav class="steps-navigation">
                <ul>
                    <?php foreach ($steps as $number => $name_key): ?>
                        <li class="step <?php echo ($number == $current_step_number) ? 'active' : ''; ?>" data-step="<?php echo $number; ?>">
                            <a href="?step=<?php echo $number; ?>"><?php echo t($name_key); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>
        <div class="main-area"> <!-- Новый div для main и кнопок -->
            <form method="POST" action="?step=<?php echo $current_step_number; ?>" id="installerStepForm">
                <main class="content">
                    <h2><?php echo t($current_step_name_key); ?></h2>

                    <?php if ($current_step_number == 1): ?>
                        <?php /* Отдельная форма для языка, чтобы POST не конфликтовал с навигацией */ ?>
                        <form method="POST" action="?step=1" id="languageForm"> 
                             <div class="form-group">
                                <label for="language_select"><?php echo t('select_language_placeholder'); ?></label>
                                <select id="language_select" name="language_select" onchange="document.getElementById('languageForm').submit()">
                                     <option value="en" <?php echo ($current_lang_code == 'en') ? 'selected' : ''; ?>><?php echo t('language_en_placeholder'); ?></option>
                                     <option value="ru" <?php echo ($current_lang_code == 'ru') ? 'selected' : ''; ?>><?php echo t('language_ru_placeholder'); ?></option>
                                 </select>
                             </div>
                        </form>
                        <p><?php echo t('welcome_message_placeholder'); ?></p>
                        <p><?php echo t('just_a_test_placeholder'); ?></p>
                    <?php elseif ($current_step_number == 2): ?>
                        <p><?php echo t('system_requirements_content_placeholder'); ?></p>
                    <?php elseif ($current_step_number == 3): ?>
                        <p><?php echo t('database_configuration_content_placeholder'); ?></p>
                    <?php elseif ($current_step_number == 4): ?>
                        <p><?php echo t('admin_account_content_placeholder'); ?></p>
                    <?php elseif ($current_step_number == 5): ?>
                        <p><?php echo t('license_key_content_placeholder'); ?></p>
                    <?php elseif ($current_step_number == 6): ?>
                        <p><?php echo t('terms_conditions_content_placeholder'); ?></p>
                    <?php elseif ($current_step_number == 7): ?>
                        <p><?php echo t('finish_content_placeholder'); ?></p>
                    <?php else: ?>
                        <p>Content for step <?php echo $current_step_number; ?></p>
                    <?php endif; ?>
                </main>

                <div class="navigation-buttons">
                    <?php if ($current_step_number > 1): ?>
                        <button type="submit" name="prevBtn" class="btn btn-secondary"><?php echo t('button_previous_placeholder'); ?></button>
                    <?php else: ?>
                        <span class="btn-placeholder" style="width: 120px; display: inline-block;"></span> <!-- Для сохранения разметки -->
                    <?php endif; ?>

                    <?php if ($current_step_number < $total_steps): ?>
                        <button type="submit" name="nextBtn" class="btn btn-primary"><?php echo t('button_next_placeholder'); ?></button>
                    <?php elseif ($current_step_number == $total_steps): ?>
                        <button type="submit" name="installBtn" class="btn btn-success"><?php echo t('button_install_placeholder'); ?></button>
                    <?php endif; ?>
                </div>
            </form> <!-- Конец installerStepForm -->
        </div> <!-- Конец main-area -->
    </div>
</body>
</html>
