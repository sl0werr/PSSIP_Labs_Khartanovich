<?php
/**
 * Вход в блок администратора по паролю
 */
require_once __DIR__ . '/config.php';

$error = '';

// Если уже авторизован, сразу даём доступ к сайту
if (isLoggedIn()) {
    header('Location: ../site/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password !== '') {
        if (login($password)) {
            // После успешного входа авторизованный пользователь получает доступ к личному сайту
            $redirect = $_GET['redirect'] ?? '../site/index.php';
            header('Location: ' . $redirect);
            exit;
        }
        $error = 'Неверный пароль.';
    } else {
        $error = 'Введите пароль.';
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Блок администратора</title>
    <style>
        body { font-family: sans-serif; max-width: 360px; margin: 3rem auto; padding: 0 1rem; }
        h1 { color: #333; font-size: 1.25rem; }
        .error { color: #b91c1c; margin: 0.5rem 0; font-size: 0.9rem; }
        input { display: block; width: 100%; padding: 0.5rem; margin: 0.5rem 0; }
        button { width: 100%; padding: 0.5rem; background: #2563eb; color: #fff; border: 0; cursor: pointer; border-radius: 6px; }
        button:hover { background: #1d4ed8; }
        a { color: #2563eb; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>Вход в блок администратора</h1>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Пароль:</label>
        <input type="password" name="password" placeholder="Пароль" required autofocus>
        <button type="submit">Войти</button>
    </form>
    <p style="margin-top: 1rem;"><a href="../index.php">← На главную</a></p>
</body>
</html>
