<?php
/**
 * Задание №1 — Механизм работы с сессиями
 * Пример: счётчик посещений страницы, хранение данных в сессии
 */
session_start();

// Очистка сессии по запросу (до любого вывода)
if (isset($_GET['clear'])) {
    session_destroy();
    header('Location: task1_sessions.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

// Инициализация счётчика в сессии
if (!isset($_SESSION['visit_count'])) {
    $_SESSION['visit_count'] = 0;
}
$_SESSION['visit_count']++;

// Сохраняем время последнего визита
$_SESSION['last_visit'] = date('d.m.Y H:i:s');

// Опционально: имя пользователя (можно задать через GET для демо)
if (isset($_GET['name']) && trim($_GET['name']) !== '') {
    $_SESSION['user_name'] = trim($_GET['name']);
}
$userName = $_SESSION['user_name'] ?? 'Гость';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 1 — Сессии</title>
    <style>
        body { font-family: sans-serif; max-width: 560px; margin: 2rem auto; padding: 0 1rem; }
        .box { background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 1rem; margin: 1rem 0; }
        a { color: #2563eb; }
        .back { display: inline-block; margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>Задание №1 — Работа с сессиями</h1>
    <div class="box">
        <p><strong>Количество посещений этой страницы (в текущей сессии):</strong> <?= (int)$_SESSION['visit_count'] ?></p>
        <p><strong>Последний визит:</strong> <?= htmlspecialchars($_SESSION['last_visit']) ?></p>
        <p><strong>Имя в сессии:</strong> <?= htmlspecialchars($userName) ?></p>
    </div>
    <p>Перезагрузите страницу — счётчик увеличится. Данные хранятся в <code>$_SESSION</code> на сервере.</p>
    <form method="get" style="margin: 1rem 0;">
        <label>Задать имя в сессии: <input type="text" name="name" placeholder="Ваше имя" value="<?= htmlspecialchars($userName !== 'Гость' ? $userName : '') ?>"></label>
        <button type="submit">Сохранить</button>
    </form>
    <p><a href="task1_sessions.php?clear=1">Очистить сессию (сбросить счётчик)</a></p>
    <a href="index.php" class="back">← На главную</a>
</body>
</html>
