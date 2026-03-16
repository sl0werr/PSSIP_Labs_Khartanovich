<?php
require_once __DIR__ . '/mail_config.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$lastname   = trim($_POST['lastname'] ?? '');
$firstname  = trim($_POST['firstname'] ?? '');
$middlename = trim($_POST['middlename'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$address    = trim($_POST['address'] ?? '');
$email      = trim($_POST['email'] ?? '');
$message    = trim($_POST['message'] ?? '');

// Простая проверка обязательных полей
if ($lastname === '' || $firstname === '' || $phone === '' || $email === '') {
    $status = 'error';
    $statusText = 'Пожалуйста, заполните все обязательные поля анкеты.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $status = 'error';
    $statusText = 'Некорректный адрес электронной почты.';
} else {
    global $ADMIN_EMAIL;

    $subject = 'Новая анкета с сайта Our Place';
    $body = '
        <h2>Анкета с сайта</h2>
        <p><strong>Фамилия:</strong> ' . htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Имя:</strong> ' . htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Отчество:</strong> ' . htmlspecialchars($middlename, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Телефон:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Адрес:</strong> ' . htmlspecialchars($address, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Сообщение:</strong><br>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>
    ';

    if (site_send_mail($ADMIN_EMAIL, $subject, $body)) {
        // Краткое письмо-подтверждение пользователю
        $userSubject = 'Мы получили вашу анкету на сайте Our Place';
        $userBody    = '
            <p>Мы получили вашу анкету на сайте Our Place.</p>
        ';
        // Если письмо админу ушло, пробуем отправить копию пользователю
        site_send_mail($email, $userSubject, $userBody);

        $status = 'success';
        $statusText = 'Анкета успешно отправлена на электронную почту администратора.';
    } else {
        $status = 'error';
        $statusText = 'Не удалось отправить анкету. Попробуйте позже или свяжитесь с администратором.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отправка анкеты</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="container" style="max-width: 720px; margin: 4rem auto; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Отправка анкеты</h1>
    <p style="margin-bottom: 1.5rem; color: <?= $status === 'success' ? '#15803d' : '#b91c1c' ?>;">
        <?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8') ?>
    </p>
    <p><a href="index.php" style="color: #2563eb;">← Вернуться на сайт</a></p>
</div>
</body>
</html>

