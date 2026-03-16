<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';

session_start();

$action = $_GET['action'] ?? '';

try {
    match ($action) {
        'register' => handleRegister(),
        'login'    => handleLogin(),
        'logout'   => handleLogout(),
        'me'       => handleMe(),
        default    => jsonError('Неизвестное действие', 400),
    };
} catch (PDOException $e) {
    jsonError('Ошибка базы данных: ' . $e->getMessage(), 500);
}

function handleRegister(): void {
    requireMethod('POST');
    $data = getJsonInput();

    $email     = trim($data['email'] ?? '');
    $password  = $data['password'] ?? '';
    $firstName = trim($data['first_name'] ?? '');
    $lastName  = trim($data['last_name'] ?? '');
    $phone     = trim($data['phone'] ?? '');

    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if (mb_strlen($password) < 6) $errors[] = 'Пароль должен быть не менее 6 символов';
    if ($firstName === '') $errors[] = 'Укажите имя';

    if ($errors) {
        jsonError(implode('; ', $errors), 422);
        return;
    }

    $db = getDB();

    $existing = $db->prepare("SELECT id FROM users WHERE email = ?");
    $existing->execute([$email]);
    if ($existing->fetch()) {
        jsonError('Пользователь с таким email уже зарегистрирован', 409);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (email, phone, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'customer')");
    $stmt->execute([$email, $phone ?: null, $hash, $firstName, $lastName ?: null]);
    $userId = (int)$db->lastInsertId();

    $token = createSession($db, $userId);

    $_SESSION['user_id'] = $userId;
    $_SESSION['token'] = $token;

    jsonSuccess([
        'user'  => getUserData($db, $userId),
        'token' => $token,
    ], 'Регистрация успешна');
}

function handleLogin(): void {
    requireMethod('POST');
    $data = getJsonInput();

    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
        jsonError('Укажите email и пароль', 422);
        return;
    }

    $db = getDB();

    $stmt = $db->prepare("SELECT id, password_hash, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonError('Неверный email или пароль', 401);
        return;
    }

    if (!$user['is_active']) {
        jsonError('Аккаунт деактивирован', 403);
        return;
    }

    $token = createSession($db, (int)$user['id']);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['token'] = $token;

    jsonSuccess([
        'user'  => getUserData($db, (int)$user['id']),
        'token' => $token,
    ], 'Вход выполнен');
}

function handleLogout(): void {
    requireMethod('POST');
    $db = getDB();

    $token = getBearerToken() ?? ($_SESSION['token'] ?? '');
    if ($token) {
        $stmt = $db->prepare("DELETE FROM user_sessions WHERE token = ?");
        $stmt->execute([$token]);
    }

    session_destroy();
    jsonSuccess(null, 'Выход выполнен');
}

function handleMe(): void {
    requireMethod('GET');
    $db = getDB();

    $userId = getAuthUserId($db);
    if (!$userId) {
        jsonError('Не авторизован', 401);
        return;
    }

    jsonSuccess(['user' => getUserData($db, $userId)]);
}

// ------- Helpers -------

function createSession(PDO $db, int $userId): string {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $db->prepare("INSERT INTO user_sessions (user_id, token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $token, $ip, $ua, $expires]);

    return $token;
}

function getAuthUserId(PDO $db): ?int {
    $token = getBearerToken() ?? ($_SESSION['token'] ?? '');
    if (!$token) return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $stmt = $db->prepare("SELECT user_id FROM user_sessions WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    return $row ? (int)$row['user_id'] : null;
}

function getUserData(PDO $db, int $userId): array {
    $stmt = $db->prepare("SELECT id, email, phone, first_name, last_name, avatar, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: [];
}

function getBearerToken(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
        return $m[1];
    }
    return null;
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?: [];
}

function requireMethod(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        jsonError("Метод {$_SERVER['REQUEST_METHOD']} не поддерживается", 405);
        exit;
    }
}

function jsonSuccess($data = null, string $message = 'OK'): void {
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
