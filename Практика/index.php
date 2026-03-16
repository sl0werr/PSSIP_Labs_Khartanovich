<?php

$phpVersion = phpversion();
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';

$mysqlStatus = 'Не подключено';
$mysqlVersion = '';
try {
    $pdo = new PDO('mysql:host=127.127.126.31;port=3306', 'root', '');
    $mysqlVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    $mysqlStatus = 'Подключено';
} catch (PDOException $e) {
    $mysqlStatus = 'Ошибка: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoorLockShop — Информация о сервере</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #333; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        h1 { font-size: 28px; margin-bottom: 24px; color: #1a1a2e; }
        .card { background: #fff; border-radius: 12px; padding: 24px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card h2 { font-size: 18px; color: #555; margin-bottom: 16px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f5f5f5; }
        .row:last-child { border-bottom: none; }
        .label { color: #888; }
        .value { font-weight: 600; }
        .status-ok { color: #27ae60; }
        .status-err { color: #e74c3c; }
        a.btn { display: inline-block; margin-top: 24px; padding: 12px 28px; background: #1a1a2e; color: #fff; text-decoration: none; border-radius: 8px; font-size: 15px; transition: background 0.2s; }
        a.btn:hover { background: #16213e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>DoorLockShop.local</h1>

        <div class="card">
            <h2>Сервер</h2>
            <div class="row"><span class="label">Web-сервер</span><span class="value"><?= htmlspecialchars($serverSoftware) ?></span></div>
            <div class="row"><span class="label">PHP</span><span class="value"><?= $phpVersion ?></span></div>
            <div class="row"><span class="label">Документ</span><span class="value"><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '') ?></span></div>
        </div>

        <div class="card">
            <h2>MySQL</h2>
            <div class="row">
                <span class="label">Статус</span>
                <span class="value <?= $mysqlStatus === 'Подключено' ? 'status-ok' : 'status-err' ?>"><?= htmlspecialchars($mysqlStatus) ?></span>
            </div>
            <?php if ($mysqlVersion): ?>
            <div class="row"><span class="label">Версия</span><span class="value"><?= htmlspecialchars($mysqlVersion) ?></span></div>
            <?php endif; ?>
        </div>

        <a class="btn" href="/phpMyAdmin-5.2.2-all-languages/">Открыть phpMyAdmin</a>
    </div>
</body>
</html>
