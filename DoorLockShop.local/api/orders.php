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
        'create'  => createOrder(),
        'list'    => getOrders(),
        'detail'  => getOrder(),
        default   => jsonError('Неизвестное действие', 400),
    };
} catch (PDOException $e) {
    jsonError('Ошибка: ' . $e->getMessage(), 500);
}

function createOrder(): void {
    requireMethod('POST');
    $data = getJsonInput();
    $db = getDB();

    $firstName  = trim($data['first_name'] ?? '');
    $lastName   = trim($data['last_name'] ?? '');
    $email      = trim($data['email'] ?? '');
    $phone      = trim($data['phone'] ?? '');
    $deliveryId = isset($data['delivery_method_id']) ? (int)$data['delivery_method_id'] : null;
    $paymentId  = isset($data['payment_method_id']) ? (int)$data['payment_method_id'] : null;
    $deliveryMethod = trim($data['delivery_method'] ?? '');
    $paymentMethod  = trim($data['payment_method'] ?? '');
    $address    = trim($data['address'] ?? $data['delivery_address'] ?? '');
    $comment    = trim($data['comment'] ?? '');
    $items      = $data['items'] ?? [];

    $errors = [];
    if ($firstName === '') $errors[] = 'Укажите имя';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if ($phone === '') $errors[] = 'Укажите телефон';
    if (empty($items)) $errors[] = 'Корзина пуста';

    if ($errors) {
        jsonError(implode('; ', $errors), 422);
        return;
    }

    $userId = getAuthUserId($db);

    $orderNumber = 'DLS-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $subtotal = 0;
    $orderItems = [];
    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $qty       = max(1, (int)($item['quantity'] ?? 1));

        $stmt = $db->prepare("SELECT id, name, sku, price, stock FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) continue;
        if ($product['stock'] < $qty) {
            jsonError("Товар \"{$product['name']}\" — недостаточно на складе (осталось {$product['stock']})", 422);
            return;
        }

        $lineTotal = $product['price'] * $qty;
        $subtotal += $lineTotal;
        $orderItems[] = [
            'product_id'   => $product['id'],
            'product_name' => $product['name'],
            'product_sku'  => $product['sku'],
            'price'        => $product['price'],
            'quantity'     => $qty,
            'total'        => $lineTotal,
        ];
    }

    if (empty($orderItems)) {
        jsonError('Ни один товар не найден', 422);
        return;
    }

    $deliveryPrice = 0;
    if ($deliveryId) {
        $dStmt = $db->prepare("SELECT price, free_from FROM delivery_methods WHERE id = ?");
        $dStmt->execute([$deliveryId]);
        $delivery = $dStmt->fetch();
        if ($delivery) {
            $deliveryPrice = ($delivery['free_from'] && $subtotal >= $delivery['free_from']) ? 0 : $delivery['price'];
        }
    } elseif ($deliveryMethod) {
        $dStmt = $db->prepare("SELECT id, price, free_from FROM delivery_methods WHERE slug = ? OR name LIKE ?");
        $dStmt->execute([$deliveryMethod, '%' . $deliveryMethod . '%']);
        $delivery = $dStmt->fetch();
        if ($delivery) {
            $deliveryId = (int)$delivery['id'];
            $deliveryPrice = ($delivery['free_from'] && $subtotal >= $delivery['free_from']) ? 0 : $delivery['price'];
        }
    }
    if ($paymentMethod && !$paymentId) {
        $pStmt = $db->prepare("SELECT id FROM payment_methods WHERE slug = ? OR name LIKE ?");
        $pStmt->execute([$paymentMethod, '%' . $paymentMethod . '%']);
        $pm = $pStmt->fetch();
        if ($pm) $paymentId = (int)$pm['id'];
    }

    $total = $subtotal + $deliveryPrice;

    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, first_name, last_name, email, phone, delivery_method_id, delivery_address, delivery_price, payment_method_id, subtotal, total, comment) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$userId, $orderNumber, $firstName, $lastName ?: null, $email, $phone, $deliveryId, $address ?: null, $deliveryPrice, $paymentId, $subtotal, $total, $comment ?: null]);
    $orderId = (int)$db->lastInsertId();

    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_sku, price, quantity, total) VALUES (?,?,?,?,?,?,?)");
    foreach ($orderItems as $oi) {
        $itemStmt->execute([$orderId, $oi['product_id'], $oi['product_name'], $oi['product_sku'], $oi['price'], $oi['quantity'], $oi['total']]);
        $db->prepare("UPDATE products SET stock = stock - ?, sales_count = sales_count + ? WHERE id = ?")->execute([$oi['quantity'], $oi['quantity'], $oi['product_id']]);
    }

    $db->commit();

    jsonSuccess([
        'order_id'     => $orderId,
        'order_number' => $orderNumber,
        'total'        => $total,
    ], 'Заказ оформлен');
}

function getOrders(): void {
    requireMethod('GET');
    $db = getDB();
    $userId = getAuthUserId($db);

    if (!$userId) {
        jsonError('Не авторизован', 401);
        return;
    }

    $stmt = $db->prepare("SELECT id, order_number, status, payment_status, total, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);

    jsonSuccess(['orders' => $stmt->fetchAll()]);
}

function getOrder(): void {
    requireMethod('GET');
    $db = getDB();
    $userId = getAuthUserId($db);
    $orderId = (int)($_GET['id'] ?? 0);

    if (!$userId) {
        jsonError('Не авторизован', 401);
        return;
    }

    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        jsonError('Заказ не найден', 404);
        return;
    }

    $itemStmt = $db->prepare("SELECT oi.*, (SELECT pi.path FROM product_images pi WHERE pi.product_id = oi.product_id AND pi.is_main = 1 LIMIT 1) AS image FROM order_items oi WHERE oi.order_id = ?");
    $itemStmt->execute([$orderId]);
    $order['items'] = $itemStmt->fetchAll();

    $order['history'] = [];

    jsonSuccess(['order' => $order]);
}

// ------- Helpers -------

function getAuthUserId(PDO $db): ?int {
    $token = getBearerToken();
    if ($token) {
        $stmt = $db->prepare("SELECT user_id FROM user_sessions WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) return (int)$row['user_id'];
    }
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function getBearerToken(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) return $m[1];
    return null;
}

function getJsonInput(): array {
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function requireMethod(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        jsonError("Метод не поддерживается", 405);
        exit;
    }
}

function jsonSuccess($data, string $message = 'OK'): void {
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
