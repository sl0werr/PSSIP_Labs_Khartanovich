<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once __DIR__ . '/../config/database.php';

$token = $_COOKIE['auth_token'] ?? '';
$user = null;
if ($token) {
    $db = getDB();
    $stmt = $db->prepare("SELECT u.* FROM users u JOIN user_sessions ut ON u.id = ut.user_id WHERE ut.token = ? AND ut.expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
}

if (!$user || $user['role'] !== 'admin') {
    header('Location: /DoorLocksShop.html');
    exit;
}

$db = getDB();
$productsCount = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$ordersCount = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$revenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$newOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();
$callbacksNew = $db->query("SELECT COUNT(*) FROM callbacks WHERE status = 'new'")->fetchColumn();

$recentOrders = $db->query("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
$recentUsers = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$callbacks = $db->query("SELECT * FROM callbacks ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель — DoorLockShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/CSS/theme.css">
    <style>
        .admin-layout{display:grid;grid-template-columns:240px 1fr;min-height:100vh}
        .admin-sidebar{background:var(--dark);color:#fff;padding:0}
        .admin-sidebar-brand{padding:24px;font-size:18px;font-weight:800;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;gap:10px}
        .admin-sidebar-brand i{color:var(--primary)}
        .admin-nav{padding:12px 0}
        .admin-nav a{display:flex;align-items:center;gap:12px;padding:12px 24px;color:rgba(255,255,255,0.6);font-size:14px;font-weight:500;transition:var(--transition);text-decoration:none}
        .admin-nav a:hover,.admin-nav a.active{color:#fff;background:rgba(255,255,255,0.06)}
        .admin-nav a i{width:18px;text-align:center}
        .admin-nav .nav-badge{background:var(--danger);color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;margin-left:auto}
        .admin-main{padding:32px;overflow-y:auto}
        .admin-topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px}
        .admin-topbar h1{font-size:24px;font-weight:800;color:var(--dark)}
        .stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:20px;margin-bottom:32px}
        .stat-box{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;display:flex;align-items:center;gap:16px}
        .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px}
        .stat-info .val{font-size:24px;font-weight:800;color:var(--dark)}
        .stat-info .lbl{font-size:13px;color:var(--text-3);margin-top:2px}
        .panel-card{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);margin-bottom:24px}
        .panel-card-head{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
        .panel-card-head h2{font-size:16px;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:8px}
        .panel-table{width:100%;border-collapse:collapse}
        .panel-table th{text-align:left;font-size:12px;font-weight:600;color:var(--text-3);padding:10px 16px;border-bottom:2px solid var(--border);text-transform:uppercase;letter-spacing:0.5px}
        .panel-table td{padding:12px 16px;font-size:14px;border-bottom:1px solid #f0f2f5}
        .panel-table tr:hover td{background:#fafbfd}
        .status-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;color:#fff}
        .cb-status-btn{padding:4px 10px;border-radius:8px;border:1px solid var(--border);background:var(--white);font-size:12px;cursor:pointer;transition:var(--transition)}
        .cb-status-btn:hover{background:var(--primary-light);border-color:var(--primary);color:var(--primary)}
        @media(max-width:768px){.admin-layout{grid-template-columns:1fr}.admin-sidebar{display:none}.stat-grid{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand"><i class="fas fa-lock"></i> Admin Panel</div>
        <nav class="admin-nav">
            <a href="/admin/" class="active"><i class="fas fa-chart-bar"></i> Dashboard</a>
            <a href="#"><i class="fas fa-box"></i> Товары</a>
            <a href="#"><i class="fas fa-shopping-bag"></i> Заказы</a>
            <a href="#"><i class="fas fa-users"></i> Пользователи</a>
            <a href="#callbacks-section"><i class="fas fa-phone-alt"></i> Заявки <?php if ($callbacksNew > 0): ?><span class="nav-badge"><?= $callbacksNew ?></span><?php endif; ?></a>
            <a href="#"><i class="fas fa-tags"></i> Категории</a>
            <a href="/phpMyAdmin-5.2.2-all-languages/"><i class="fas fa-database"></i> phpMyAdmin</a>
            <a href="/DoorLocksShop.html"><i class="fas fa-store"></i> Магазин</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1>Dashboard</h1>
            <div style="font-size:14px;color:var(--text-3)">
                <i class="fas fa-user-shield"></i> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-box">
                <div class="stat-icon" style="background:#e8f0fe;color:var(--primary)"><i class="fas fa-box"></i></div>
                <div class="stat-info"><div class="val"><?= $productsCount ?></div><div class="lbl">Товаров</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:#e8f5e9;color:var(--success)"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-info"><div class="val"><?= $ordersCount ?></div><div class="lbl">Заказов</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:#fff3e0;color:var(--warning)"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><div class="val"><?= $newOrders ?></div><div class="lbl">Новых заказов</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:#fce4ec;color:var(--danger)"><i class="fas fa-coins"></i></div>
                <div class="stat-info"><div class="val"><?= number_format($revenue, 0, '', ' ') ?></div><div class="lbl">Выручка, BYN</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:#ede7f6;color:var(--accent)"><i class="fas fa-users"></i></div>
                <div class="stat-info"><div class="val"><?= $usersCount ?></div><div class="lbl">Пользователей</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:#e3f2fd;color:#1976d2"><i class="fas fa-phone-alt"></i></div>
                <div class="stat-info"><div class="val"><?= $callbacksNew ?></div><div class="lbl">Новых заявок</div></div>
            </div>
        </div>

        <!-- CALLBACKS -->
        <div class="panel-card" id="callbacks-section">
            <div class="panel-card-head">
                <h2><i class="fas fa-phone-alt" style="color:var(--primary)"></i> Заявки на обратный звонок</h2>
                <?php if ($callbacksNew > 0): ?>
                <span class="status-badge" style="background:var(--danger)"><?= $callbacksNew ?> новых</span>
                <?php endif; ?>
            </div>
            <table class="panel-table">
                <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Телефон</th><th>Сообщение</th><th>Статус</th><th>Дата</th></tr></thead>
                <tbody>
                <?php if (empty($callbacks)): ?>
                <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-3)">Заявок пока нет</td></tr>
                <?php else: ?>
                <?php foreach ($callbacks as $cb):
                    $cbColors = ['new'=>'var(--danger)','processing'=>'var(--warning)','done'=>'var(--success)'];
                    $cbLabels = ['new'=>'Новая','processing'=>'В работе','done'=>'Выполнена'];
                ?>
                <tr>
                    <td><b>#<?= $cb['id'] ?></b></td>
                    <td><?= htmlspecialchars($cb['name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($cb['email'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($cb['phone'] ?? '—') ?></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($cb['message'] ?? '—') ?></td>
                    <td><span class="status-badge" style="background:<?= $cbColors[$cb['status']] ?? '#999' ?>"><?= $cbLabels[$cb['status']] ?? $cb['status'] ?></span></td>
                    <td><?= (new DateTime($cb['created_at']))->format('d.m.Y H:i') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ORDERS -->
        <div class="panel-card">
            <div class="panel-card-head"><h2>Последние заказы</h2></div>
            <table class="panel-table">
                <thead><tr><th>ID</th><th>Клиент</th><th>Сумма</th><th>Статус</th><th>Дата</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o):
                    $colors = ['new'=>'var(--primary)','processing'=>'var(--warning)','shipped'=>'#9b59b6','delivered'=>'var(--success)','cancelled'=>'var(--danger)'];
                    $labels = ['new'=>'Новый','processing'=>'Обработка','shipped'=>'Доставка','delivered'=>'Доставлен','cancelled'=>'Отменён'];
                ?>
                <tr>
                    <td><b>#<?= $o['id'] ?></b></td>
                    <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
                    <td><b><?= number_format($o['total'], 0, '', ' ') ?> BYN</b></td>
                    <td><span class="status-badge" style="background:<?= $colors[$o['status']] ?? '#999' ?>"><?= $labels[$o['status']] ?? $o['status'] ?></span></td>
                    <td><?= (new DateTime($o['created_at']))->format('d.m.Y H:i') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- USERS -->
        <div class="panel-card">
            <div class="panel-card-head"><h2>Новые пользователи</h2></div>
            <table class="panel-table">
                <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Роль</th><th>Дата</th></tr></thead>
                <tbody>
                <?php foreach ($recentUsers as $u):
                    $roleColors = ['admin'=>'var(--danger)','manager'=>'var(--warning)','customer'=>'var(--primary)'];
                    $roleLabels = ['admin'=>'Админ','manager'=>'Менеджер','customer'=>'Покупатель'];
                ?>
                <tr>
                    <td><b>#<?= $u['id'] ?></b></td>
                    <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="status-badge" style="background:<?= $roleColors[$u['role']] ?? '#999' ?>"><?= $roleLabels[$u['role']] ?? $u['role'] ?></span></td>
                    <td><?= (new DateTime($u['created_at']))->format('d.m.Y') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
