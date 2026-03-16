<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';

try {
    match ($action) {
        'sync'  => syncCart(),
        default => jsonError('action: sync', 400),
    };
} catch (PDOException $e) {
    jsonError($e->getMessage(), 500);
}

function syncCart(): void {
    $input = json_decode(file_get_contents('php://input'), true);
    $items = $input['items'] ?? [];

    if (!is_array($items) || empty($items)) {
        jsonError('items required', 422);
        return;
    }

    $db = getDB();
    $ids = array_map(function($i) { return (int)$i['id']; }, $items);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $db->prepare("SELECT p.id, p.name, p.price, p.old_price, p.stock, b.name AS brand_name,
                                  (SELECT pi.path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 1 LIMIT 1) AS main_image
                           FROM products p
                           LEFT JOIN brands b ON p.brand_id = b.id
                           WHERE p.id IN ({$placeholders}) AND p.is_active = 1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $productMap = [];
    foreach ($products as $p) $productMap[$p['id']] = $p;

    $result = [];
    $total = 0;
    foreach ($items as $item) {
        $id = (int)$item['id'];
        if (!isset($productMap[$id])) continue;
        $p = $productMap[$id];
        $qty = max(1, min((int)($item['qty'] ?? 1), (int)$p['stock']));
        $result[] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'brand' => $p['brand_name'] ?? '',
            'price' => (float)$p['price'],
            'old_price' => $p['old_price'] ? (float)$p['old_price'] : null,
            'image' => $p['main_image'] ?? '',
            'qty' => $qty,
            'stock' => (int)$p['stock'],
        ];
        $total += (float)$p['price'] * $qty;
    }

    jsonSuccess(['items' => $result, 'total' => $total, 'count' => count($result)]);
}

function jsonSuccess($data): void {
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
