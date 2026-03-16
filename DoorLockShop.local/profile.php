<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once __DIR__ . '/config/database.php';

$token = $_COOKIE['auth_token'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
$token = str_replace('Bearer ', '', $token);
$user = null;

if ($token) {
    $db = getDB();
    $stmt = $db->prepare("SELECT u.* FROM users u JOIN user_sessions ut ON u.id = ut.user_id WHERE ut.token = ? AND ut.expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
}

if (!$user) {
    header('Location: DoorLocksShop.html');
    exit;
}

$db = getDB();
$ordersStmt = $db->prepare("SELECT o.*, (SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) AS items_count FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 5");
$ordersStmt->execute([$user['id']]);
$orders = $ordersStmt->fetchAll();

$statsStmt = $db->prepare("SELECT COUNT(*) AS total_orders, COALESCE(SUM(total),0) AS total_spent FROM orders WHERE user_id = ?");
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();

$statusLabels = [
    'new' => ['Новый', 'var(--primary)'],
    'processing' => ['В обработке', 'var(--warning)'],
    'shipped' => ['Доставляется', '#9b59b6'],
    'delivered' => ['Доставлен', 'var(--success)'],
    'cancelled' => ['Отменён', 'var(--danger)'],
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль — DoorLockShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/theme.css">
    <link rel="stylesheet" href="CSS/auth.css">
    <style>
        .profile-layout{display:grid;grid-template-columns:280px 1fr;gap:32px;align-items:start}
        .profile-sidebar{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);overflow:hidden;position:sticky;top:80px}
        .profile-avatar-block{background:linear-gradient(135deg,var(--primary),var(--accent));padding:32px 24px;text-align:center;color:#fff}
        .profile-avatar{width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.2);display:inline-flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;margin-bottom:12px;border:3px solid rgba(255,255,255,0.3)}
        .profile-fullname{font-size:18px;font-weight:700}
        .profile-role{font-size:13px;opacity:0.8;margin-top:4px}
        .profile-menu{padding:8px 0}
        .profile-menu a{display:flex;align-items:center;gap:12px;padding:12px 24px;color:var(--text);font-size:14px;font-weight:500;transition:var(--transition);text-decoration:none}
        .profile-menu a:hover,.profile-menu a.active{background:var(--primary-light);color:var(--primary)}
        .profile-menu a i{width:18px;text-align:center;color:var(--text-3)}
        .profile-menu a:hover i,.profile-menu a.active i{color:var(--primary)}
        .profile-section{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:28px;margin-bottom:24px}
        .profile-section h2{font-size:18px;font-weight:700;color:var(--dark);margin-bottom:20px;display:flex;align-items:center;gap:10px}
        .profile-section h2 i{color:var(--primary)}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .info-item{padding:16px;background:#f8f9fb;border-radius:var(--radius)}
        .info-item-label{font-size:12px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px}
        .info-item-value{font-size:15px;font-weight:600;color:var(--text)}
        .stat-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
        .stat-card-p{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;text-align:center}
        .stat-card-p .num{font-size:28px;font-weight:800;color:var(--dark)}
        .stat-card-p .lbl{font-size:13px;color:var(--text-3);margin-top:4px}
        .order-table{width:100%;border-collapse:collapse}
        .order-table th{text-align:left;font-size:13px;font-weight:600;color:var(--text-3);padding:10px 12px;border-bottom:2px solid var(--border)}
        .order-table td{padding:12px;font-size:14px;border-bottom:1px solid #f0f2f5}
        .order-status{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;color:#fff}
        @media(max-width:768px){.profile-layout{grid-template-columns:1fr}.profile-sidebar{position:static}.info-grid{grid-template-columns:1fr}.stat-cards{grid-template-columns:1fr}}
    </style>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="DoorLocksShop.html" class="header-logo"><img src="IMG/logo.png" alt=""><span>GoldenService</span></a>
        <nav class="header-nav">
            <a href="DoorLocksShop.html">Главная</a>
            <a href="categoty.html">Каталог</a>
            <a href="wholesale.html">Оптовая продажа</a>
            <a href="about.html">О нас</a>
        </nav>
        <div class="header-right">
            <div class="header-phone"><i class="fas fa-phone-alt"></i> +375 (29) 558-84-99</div>
            <div class="header-icons">
                <a href="wishlist.html" class="icon-btn-h" title="Избранное"><i class="far fa-heart"></i><span class="badge-count wishlist-badge-count" style="display:none">0</span></a>
                <button class="icon-btn-h cart-open-btn" title="Корзина"><i class="fas fa-shopping-bag"></i><span class="badge-count cart-badge-count" style="display:none">0</span></button>
                <div id="authContainer"></div>
            </div>
        </div>
    </div>
</header>

<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-sidebar-head"><h2><i class="fas fa-shopping-bag"></i> Корзина</h2><button class="cart-close-btn">&times;</button></div>
    <div class="cart-sidebar-body" id="cartSidebarBody"></div>
    <div class="cart-sidebar-foot" id="cartSidebarFoot"><div class="cart-total-row"><span class="cart-total-label">Итого:</span><span class="cart-total-value">0 BYN</span></div><div class="cart-foot-btns"><a href="buying.html" class="btn btn-primary btn-block">Оформить заказ</a><button class="btn btn-outline btn-block cart-close-btn">Продолжить покупки</button></div></div>
</div>

<section style="padding-top:32px;padding-bottom:64px">
    <div class="container">
        <div class="breadcrumbs"><a href="DoorLocksShop.html">Главная</a><span class="sep">/</span><span>Профиль</span></div>

        <div class="stat-cards" style="margin-top:16px">
            <div class="stat-card-p"><div class="num"><?= (int)$stats['total_orders'] ?></div><div class="lbl">Заказов</div></div>
            <div class="stat-card-p"><div class="num"><?= number_format($stats['total_spent'], 0, '', ' ') ?> BYN</div><div class="lbl">Сумма покупок</div></div>
            <div class="stat-card-p"><div class="num"><?= (new DateTime($user['created_at']))->format('d.m.Y') ?></div><div class="lbl">Дата регистрации</div></div>
        </div>

        <div class="profile-layout">
            <aside class="profile-sidebar">
                <div class="profile-avatar-block">
                    <div class="profile-avatar"><?= mb_strtoupper(mb_substr($user['first_name'], 0, 1)) . mb_strtoupper(mb_substr($user['last_name'], 0, 1)) ?></div>
                    <div class="profile-fullname"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                    <div class="profile-role"><?= $user['role'] === 'admin' ? 'Администратор' : ($user['role'] === 'manager' ? 'Менеджер' : 'Покупатель') ?></div>
                </div>
                <div class="profile-menu">
                    <a href="#personal" class="active"><i class="fas fa-user"></i> Личные данные</a>
                    <a href="#orders"><i class="fas fa-shopping-bag"></i> Мои заказы</a>
                    <a href="#addresses"><i class="fas fa-map-marker-alt"></i> Адреса</a>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="/admin/"><i class="fas fa-cog"></i> Админ-панель</a>
                    <?php endif; ?>
                    <a href="DoorLocksShop.html"><i class="fas fa-arrow-left"></i> Вернуться в магазин</a>
                </div>
            </aside>

            <div>
                <div class="profile-section" id="personal">
                    <h2><i class="fas fa-user"></i> Личные данные</h2>
                    <div class="info-grid">
                        <div class="info-item"><div class="info-item-label">Имя</div><div class="info-item-value"><?= htmlspecialchars($user['first_name']) ?></div></div>
                        <div class="info-item"><div class="info-item-label">Фамилия</div><div class="info-item-value"><?= htmlspecialchars($user['last_name']) ?></div></div>
                        <div class="info-item"><div class="info-item-label">Email</div><div class="info-item-value"><?= htmlspecialchars($user['email']) ?></div></div>
                        <div class="info-item"><div class="info-item-label">Телефон</div><div class="info-item-value"><?= htmlspecialchars($user['phone'] ?: 'Не указан') ?></div></div>
                    </div>
                </div>

                <div class="profile-section" id="orders">
                    <h2><i class="fas fa-shopping-bag"></i> Мои заказы</h2>
                    <?php if (empty($orders)): ?>
                    <div style="text-align:center;padding:32px;color:var(--text-3)">
                        <i class="fas fa-box-open" style="font-size:40px;display:block;margin-bottom:12px;color:#ddd"></i>
                        У вас пока нет заказов
                    </div>
                    <?php else: ?>
                    <table class="order-table">
                        <thead><tr><th>№</th><th>Дата</th><th>Товаров</th><th>Сумма</th><th>Статус</th></tr></thead>
                        <tbody>
                        <?php foreach ($orders as $order):
                            $sl = $statusLabels[$order['status']] ?? ['?', '#999'];
                        ?>
                        <tr>
                            <td><b>#<?= $order['id'] ?></b></td>
                            <td><?= (new DateTime($order['created_at']))->format('d.m.Y') ?></td>
                            <td><?= (int)$order['items_count'] ?> шт.</td>
                            <td><b><?= number_format($order['total'], 0, '', ' ') ?> BYN</b></td>
                            <td><span class="order-status" style="background:<?= $sl[1] ?>"><?= $sl[0] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <div class="profile-section" id="addresses">
                    <h2><i class="fas fa-map-marker-alt"></i> Адреса доставки</h2>
                    <div style="text-align:center;padding:24px;color:var(--text-3)">
                        <i class="fas fa-map" style="font-size:32px;display:block;margin-bottom:10px;color:#ddd"></i>
                        Адреса не добавлены
                        <div style="margin-top:16px"><button class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Добавить адрес</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div><div class="footer-brand"><i class="fas fa-lock"></i> GoldenService</div><p class="footer-brand-desc">Электронные замки для дома, офиса и бизнеса.</p></div>
            <div><div class="footer-title">Навигация</div><ul class="footer-links"><li><a href="DoorLocksShop.html">Главная</a></li><li><a href="categoty.html">Каталог</a></li><li><a href="wholesale.html">Оптовая продажа</a></li><li><a href="about.html">О нас</a></li></ul></div>
            <div><div class="footer-title">Контакты</div><div class="footer-contact"><i class="fas fa-phone"></i> +375 (29) 856-50-38</div></div>
            <div><div class="footer-title">Информация</div><ul class="footer-links"><li><a href="#">Доставка</a></li><li><a href="#">Гарантии</a></li></ul></div>
        </div>
        <div class="footer-bottom">&copy; 2024 GoldenService</div>
    </div>
</footer>

<script src="JS/cart.js"></script>
<script src="JS/wishlist.js"></script>
<script src="JS/auth.js"></script>
</body>
</html>
