<?php
/**
 * Блок администратора — доступ только после авторизации по паролю
 */
require_once __DIR__ . '/config.php';
requireLogin();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Блок администратора</title>
    <style>
        body { font-family: sans-serif; max-width: 640px; margin: 2rem auto; padding: 0 1rem; }
        h1 { color: #333; }
        .panel { background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 1.5rem; margin: 1rem 0; }
        ul { list-style: none; padding: 0; }
        li { margin: 0.5rem 0; }
        a { color: #2563eb; }
        .out { margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>Блок администратора</h1>
    <p>Вы вошли в панель администратора личного сайта. Доступ разрешён по сессии после ввода пароля.</p>
    <div class="panel">
        <h2>Действия</h2>
        <ul>
            <li><a href="../site/index.php">Открыть личный сайт (Our Place)</a></li>
            <li><a href="../index.php">Перейти к заданиям (главная)</a></li>
        </ul>
    </div>
    <p class="out"><a href="logout.php">Выйти</a></p>
</body>
</html>
