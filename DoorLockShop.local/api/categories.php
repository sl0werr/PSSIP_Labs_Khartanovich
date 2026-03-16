<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? 'list';

try {
    match ($action) {
        'list'    => getCategories(),
        'detail'  => getCategory(),
        'filters' => getCategoryFilters(),
        default   => jsonError('Неизвестное действие', 400),
    };
} catch (PDOException $e) {
    jsonError('Ошибка: ' . $e->getMessage(), 500);
}

function getCategories(): void {
    $db = getDB();

    $stmt = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
                         FROM categories c WHERE c.is_active = 1 ORDER BY c.sort_order");
    $categories = $stmt->fetchAll();

    $tree = buildTree($categories);

    jsonSuccess(['categories' => $tree]);
}

function getCategory(): void {
    $db = getDB();
    $slug = $_GET['slug'] ?? '';
    $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;

    $where = $id ? 'c.id = ?' : 'c.slug = ?';
    $param = $id ?: $slug;

    $stmt = $db->prepare("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
                           FROM categories c WHERE {$where} AND c.is_active = 1");
    $stmt->execute([$param]);
    $category = $stmt->fetch();

    if (!$category) {
        jsonError('Категория не найдена', 404);
        return;
    }

    $childStmt = $db->prepare("SELECT id, name, slug, image FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order");
    $childStmt->execute([$category['id']]);
    $category['children'] = $childStmt->fetchAll();

    $priceStmt = $db->prepare("SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM products WHERE category_id = ? AND is_active = 1");
    $priceStmt->execute([$category['id']]);
    $category['price_range'] = $priceStmt->fetch();

    $brandStmt = $db->prepare("SELECT DISTINCT b.id, b.name, b.slug FROM products p JOIN brands b ON p.brand_id = b.id WHERE p.category_id = ? AND p.is_active = 1 ORDER BY b.name");
    $brandStmt->execute([$category['id']]);
    $category['brands'] = $brandStmt->fetchAll();

    jsonSuccess(['category' => $category]);
}

function getCategoryFilters(): void {
    $db = getDB();
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

    if (!$categoryId) {
        jsonError('Укажите category_id', 422);
        return;
    }

    $priceStmt = $db->prepare("SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM products WHERE category_id = ? AND is_active = 1");
    $priceStmt->execute([$categoryId]);
    $priceRange = $priceStmt->fetch();

    $brandStmt = $db->prepare("SELECT DISTINCT b.id, b.name, b.slug FROM products p JOIN brands b ON p.brand_id = b.id WHERE p.category_id = ? AND p.is_active = 1 ORDER BY b.name");
    $brandStmt->execute([$categoryId]);
    $brands = $brandStmt->fetchAll();

    jsonSuccess([
        'filters'     => [],
        'price_range' => $priceRange,
        'brands'      => $brands,
    ]);
}

function buildTree(array $items, ?int $parentId = null): array {
    $tree = [];
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $children = buildTree($items, (int)$item['id']);
            if ($children) $item['children'] = $children;
            $tree[] = $item;
        }
    }
    return $tree;
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
