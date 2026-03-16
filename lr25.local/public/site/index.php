<?php
header('Content-Type: text/html; charset=utf-8');
include __DIR__ . '/site.html';
exit;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password !== '') {
        if (login($password)) {
            header('Location: index.php');
            exit;
        }
        $error = 'Неверный пароль.';
    } else {
        $error = 'Введите пароль.';
    }
}

if (!isLoggedIn()) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход на сайт</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        .site-login { max-width: 360px; margin: 4rem auto; padding: 0 1rem; font-family: sans-serif; }
        .site-login h1 { color: #35312e; font-size: 1.5rem; margin-bottom: 1rem; }
        .site-login .error { color: #b91c1c; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .site-login input { display: block; width: 100%; padding: 0.5rem; margin: 0.5rem 0; border: 1px solid #ccc; border-radius: 6px; }
        .site-login button { width: 100%; padding: 0.6rem; background: #d37657; color: #fff; border: 0; cursor: pointer; border-radius: 6px; font-size: 1rem; }
        .site-login button:hover { background: #c2684a; }
        .site-login a { color: #2563eb; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="site-login">
        <h1>Вход на сайт</h1>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post">
            <label>Пароль:</label>
            <input type="password" name="password" placeholder="Пароль" required autofocus>
            <button type="submit">Войти</button>
        </form>
        <p style="margin-top: 1rem;"><a href="../index.php">← К заданиям</a></p>
    </div>
</body>
</html>
    <?php
    exit;
}

$adminPanelUrl = '../task3_auth/admin.php';
$logoutUrl = '../task3_auth/logout.php';
?>
<style>
.admin-panel-bar {
  position: sticky;
  top: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.5rem 1rem;
  background: #1e3a5f;
  color: #fff;
  font-family: sans-serif;
  font-size: 0.9rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.admin-panel-bar a {
  color: #93c5fd;
  text-decoration: none;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}
.admin-panel-bar a:hover {
  color: #fff;
  background: rgba(255,255,255,0.15);
}
.admin-panel-bar .admin-panel-title {
  font-weight: 600;
  color: #fff;
}
</style>
<div class="admin-panel-bar">
  <span class="admin-panel-title">Панель администратора</span>
  <div>
    <a href="<?= htmlspecialchars($adminPanelUrl) ?>">Блок администратора</a>
    <a href="<?= htmlspecialchars($logoutUrl) ?>">Выйти</a>
  </div>
</div>
<?php
include __DIR__ . '/site.html';

