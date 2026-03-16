<?php

$host = '127.127.126.32';
$port = 3306;
$user = 'root';
$pass = '';
$dbName = 'doorlockshop';

$error = null;
$stats = [];

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");

    $schemaSQL = file_get_contents(__DIR__ . '/sql/schema.sql');
    $pdo->exec($schemaSQL);

    $pdo->exec("USE `{$dbName}`");

    $seedSQL = file_get_contents(__DIR__ . '/sql/seed.sql');
    $seedSQL = preg_replace('/^USE\s+.+?;\s*$/mi', '', $seedSQL);
    $pdo->exec($seedSQL);

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $stats[$t] = (int)$pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Инициализация БД — DoorLockShop</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:'Segoe UI',sans-serif;background:#f0f2f5;padding:40px}
        .c{max-width:800px;margin:0 auto}h1{color:#1a1a2e;margin-bottom:8px}.sub{color:#666;margin-bottom:32px}
        .ok{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:16px 24px;border-radius:8px;margin-bottom:24px;font-size:18px}
        .err{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;padding:16px 24px;border-radius:8px;margin-bottom:24px}
        table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        th{background:#1a1a2e;color:#fff;text-align:left;padding:12px 16px}td{padding:10px 16px;border-bottom:1px solid #eee}
        tr:last-child td{border-bottom:none}tr:nth-child(even){background:#f8f9fa}.n{font-weight:700;text-align:center;color:#1a1a2e}
        .links{margin-top:24px}.links a{display:inline-block;margin-right:16px;padding:10px 24px;background:#1a1a2e;color:#fff;text-decoration:none;border-radius:6px}
        .links a:hover{background:#16213e}
    </style>
</head>
<body>
<div class="c">
    <h1>Инициализация базы данных</h1>
    <p class="sub">doorlockshop — <?= count($stats) ?> таблиц</p>
    <?php if ($error): ?>
        <div class="err">Ошибка: <?= htmlspecialchars($error) ?></div>
    <?php else: ?>
        <div class="ok">База данных <b>doorlockshop</b> создана и заполнена тестовыми данными.</div>
        <table>
            <thead><tr><th>Таблица</th><th style="text-align:center">Записей</th></tr></thead>
            <tbody>
            <?php foreach ($stats as $table => $count): ?>
                <tr><td><?= htmlspecialchars($table) ?></td><td class="n"><?= $count ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div class="links">
        <a href="DoorLocksShop.html">На главную</a>
        <a href="phpMyAdmin-5.2.2-all-languages/">phpMyAdmin</a>
    </div>
</div>
</body>
</html>
