<?php
/**
 * Главная страница — переходы на задания 1, 2 и 3
 * lr24.local
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lr24.local — Практические задания</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; padding: 0 1rem; }
        h1 { color: #333; }
        ul { list-style: none; padding: 0; }
        li { margin: 0.5rem 0; }
        a { display: block; padding: 0.75rem 1rem; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px; }
        a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <h1>lr24.local — Практические задания</h1>
    <p>Выберите задание:</p>
    <ul>
        <li><a href="task1_sessions.php">Задание №1 — Работа с сессиями</a></li>
        <li><a href="task2_cookies.php">Задание №2 — Работа с cookies</a></li>
        <li><a href="site/index.php">Задание №3 — Авторизация и блок администратора (личный сайт)</a></li>
    </ul>
</body>
</html>
