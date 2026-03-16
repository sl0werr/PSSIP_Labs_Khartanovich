<?php
/**
 * Задание №2 — Механизм работы с cookies
 * Пример: запись и чтение cookie, срок жизни
 */
header('Content-Type: text/html; charset=utf-8');

$message = '';
$savedName = $_COOKIE['user_name'] ?? '';
$savedColor = $_COOKIE['user_color'] ?? '#2563eb';

// Установка cookie из формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '#2563eb');
    $days = (int)($_POST['days'] ?? 7);
    if ($days < 1) $days = 1;

    if ($name !== '') {
        setcookie('user_name', $name, time() + 86400 * $days, '/');
        setcookie('user_color', $color, time() + 86400 * $days, '/');
        $message = 'Cookie сохранены на ' . $days . ' дн.';
        $savedName = $name;
        $savedColor = $color;
        header('Location: task2_cookies.php?ok=1');
        exit;
    }
}
if (isset($_GET['ok'])) {
    $message = 'Cookie успешно сохранены.';
}

// Удаление cookie
if (isset($_GET['clear'])) {
    setcookie('user_name', '', time() - 3600, '/');
    setcookie('user_color', '', time() - 3600, '/');
    header('Location: task2_cookies.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 2 — Cookies</title>
    <style>
        body { font-family: sans-serif; max-width: 560px; margin: 2rem auto; padding: 0 1rem; }
        .box { background: #f0fdf4; border: 1px solid #22c55e; border-radius: 8px; padding: 1rem; margin: 1rem 0; }
        .msg { color: #15803d; margin: 0.5rem 0; }
        input[type="text"], input[type="number"] { padding: 0.35rem; margin: 0.25rem 0; }
        a { color: #2563eb; }
        .back { display: inline-block; margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>Задание №2 — Работа с cookies</h1>
    <?php if ($message): ?>
        <p class="msg"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <div class="box">
        <p><strong>Имя из cookie:</strong> <?= $savedName !== '' ? htmlspecialchars($savedName) : '(не задано)' ?></p>
        <p><strong>Цвет из cookie:</strong> <span style="color:<?= htmlspecialchars($savedColor) ?>"><?= htmlspecialchars($savedColor) ?></span></p>
    </div>
    <form method="post" style="margin: 1rem 0;">
        <p>
            <label>Имя: <input type="text" name="name" value="<?= htmlspecialchars($savedName) ?>" placeholder="Введите имя"></label>
        </p>
        <p>
            <label>Цвет (hex): <input type="text" name="color" value="<?= htmlspecialchars($savedColor) ?>" placeholder="#2563eb"></label>
        </p>
        <p>
            <label>Хранить cookie (дней): <input type="number" name="days" value="7" min="1" max="365"></label>
        </p>
        <button type="submit">Сохранить в cookie</button>
    </form>
    <p><a href="task2_cookies.php?clear=1">Удалить cookie</a></p>
    <a href="index.php" class="back">← На главную</a>
</body>
</html>
