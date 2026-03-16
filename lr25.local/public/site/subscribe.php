<?php
require_once __DIR__ . '/mail_config.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $status = 'error';
    $statusText = 'Укажите корректный email для подписки.';
} else {
    global $ADMIN_EMAIL;

    $subject = 'Новая подписка на рассылку';
    $body = '
        <h2>Подписка на рассылку</h2>
        <p><strong>Email подписчика:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>
    ';

    if (site_send_mail($ADMIN_EMAIL, $subject, $body)) {
        // Краткое письмо-подтверждение пользователю о подписке
        $userSubject = 'Вы подписались на рассылку Our Place';
        $userBody    = '
            <p>Вы подписались на рассылку сайта Our Place на этот адрес.</p>
            <p>Если это были не вы, просто игнорируйте это письмо.</p>
        ';
        // Если письмо админу ушло, пробуем отправить копию пользователю
        site_send_mail($email, $userSubject, $userBody);

        $status = 'success';
        $statusText = 'Спасибо! Вы успешно подписались на рассылку (заявка отправлена администратору).';
    } else {
        $status = 'error';
        $statusText = 'Не удалось оформить подписку. Попробуйте позже или свяжитесь с администратором.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подписка на рассылку</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="container" style="max-width: 720px; margin: 4rem auto; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Подписка на рассылку</h1>
    <p style="margin-bottom: 1.5rem; color: <?= $status === 'success' ? '#15803d' : '#b91c1c' ?>;">
        <?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8') ?>
    </p>
    <p><a href="index.php" style="color: #2563eb;">← Вернуться на сайт</a></p>
</div>
</body>
</html>

