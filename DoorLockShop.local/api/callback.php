<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail_config.php';

$action = $_GET['action'] ?? 'create';

try {
    match ($action) {
        'create' => createCallback(),
        'list'   => listCallbacks(),
        'update' => updateCallback(),
        default  => jsonError('Unknown action', 400),
    };
} catch (PDOException $e) {
    jsonError($e->getMessage(), 500);
}

function createCallback(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { jsonError('POST required', 405); return; }

    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $name  = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $message = trim($data['message'] ?? '');

    if ($name === '' && $email === '') { jsonError('Укажите имя или email', 422); return; }

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO callbacks (name, phone, email, message, status) VALUES (?, ?, ?, ?, 'new')");
    $stmt->execute([$name ?: null, $phone ?: null, $email ?: null, $message ?: null]);
    $id = (int)$db->lastInsertId();

    // Отправка писем по примеру лабораторной: админу — заявка, пользователю — подтверждение
    global $ADMIN_EMAIL;
    $adminSubject = 'Новая заявка на обратный звонок — DoorLockShop';
    $adminBody = '
        <h2>Заявка на обратный звонок</h2>
        <p><strong>Имя:</strong> ' . htmlspecialchars($name ?: '—', ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($email ?: '—', ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Телефон:</strong> ' . htmlspecialchars($phone ?: '—', ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Сообщение:</strong><br>' . nl2br(htmlspecialchars($message ?: '—', ENT_QUOTES, 'UTF-8')) . '</p>
        <p><em>ID заявки: #' . $id . '</em></p>
    ';

    if (site_send_mail($ADMIN_EMAIL, $adminSubject, $adminBody)) {
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userSubject = 'Ваша заявка принята — GoldenService';
            $userBody = '
                <p>Здравствуйте' . ($name ? ', ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : '') . '!</p>
                <p>Ваша заявка успешно отправлена. Мы получили ваше обращение и уже обрабатываем его.</p>
                <p>Наш менеджер свяжется с вами в ближайшее рабочее время. <strong>Пожалуйста, ожидайте ответа.</strong></p>
                <p>Обычно мы отвечаем в течение 1–2 рабочих дней.</p>
                <p>С уважением,<br>Команда GoldenService<br>info@goldenservice.by<br>+375 (29) 558-84-99</p>
            ';
            site_send_mail($email, $userSubject, $userBody);
        }
    }

    jsonSuccess(['id' => $id], 'Заявка отправлена');
}

function listCallbacks(): void {
    $db = getDB();
    $token = '';
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) $token = $m[1];
    if (!$token) $token = $_COOKIE['auth_token'] ?? '';

    if ($token) {
        $stmt = $db->prepare("SELECT u.role FROM users u JOIN user_sessions s ON u.id = s.user_id WHERE s.token = ? AND s.expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if (!$user || $user['role'] !== 'admin') { jsonError('Forbidden', 403); return; }
    } else {
        jsonError('Unauthorized', 401); return;
    }

    $status = $_GET['status'] ?? '';
    $where = '';
    $params = [];
    if ($status) { $where = 'WHERE status = ?'; $params[] = $status; }

    $rows = $db->prepare("SELECT * FROM callbacks {$where} ORDER BY created_at DESC LIMIT 50");
    $rows->execute($params);

    jsonSuccess(['callbacks' => $rows->fetchAll()]);
}

function updateCallback(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { jsonError('POST required', 405); return; }

    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int)($data['id'] ?? 0);
    $status = $data['status'] ?? '';

    if (!$id || !in_array($status, ['new', 'processing', 'done'])) {
        jsonError('Invalid data', 422); return;
    }

    $db = getDB();
    $db->prepare("UPDATE callbacks SET status = ? WHERE id = ?")->execute([$status, $id]);
    jsonSuccess(null, 'Updated');
}

function jsonSuccess($data, string $msg = 'OK'): void {
    echo json_encode(['success' => true, 'message' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}
