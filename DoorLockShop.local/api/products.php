<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? 'list';

try {
    match ($action) {
        'list'    => getProducts(),
        'detail'  => getProduct(),
        'search'  => searchProducts(),
        default   => jsonError('Неизвестное действие', 400),
    };
} catch (PDOException $e) {
    jsonError('Ошибка: ' . $e->getMessage(), 500);
}

function getProducts(): void {
    $db = getDB();

    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $brandId    = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;
    $minPrice   = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
    $maxPrice   = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
    $sort       = $_GET['sort'] ?? 'popular';
    $page       = max(1, (int)($_GET['page'] ?? 1));
    $perPage    = min(48, max(1, (int)($_GET['per_page'] ?? 12)));
    $featured   = isset($_GET['featured']) ? 1 : null;

    $where = ['p.is_active = 1'];
    $params = [];

    if ($categoryId) {
        $where[] = '(p.category_id = :cat OR p.category_id IN (SELECT id FROM categories WHERE parent_id = :cat2))';
        $params[':cat'] = $categoryId;
        $params[':cat2'] = $categoryId;
    }
    if ($brandId) {
        $where[] = 'p.brand_id = :brand';
        $params[':brand'] = $brandId;
    }
    if ($minPrice !== null) {
        $where[] = 'p.price >= :min_price';
        $params[':min_price'] = $minPrice;
    }
    if ($maxPrice !== null) {
        $where[] = 'p.price <= :max_price';
        $params[':max_price'] = $maxPrice;
    }
    if ($featured) {
        $where[] = 'p.is_featured = 1';
    }

    $orderBy = match ($sort) {
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'new'        => 'p.created_at DESC',
        'rating'     => 'p.rating_avg DESC',
        'name'       => 'p.name ASC',
        default      => 'p.sales_count DESC',
    };

    $whereSQL = implode(' AND ', $where);
    $offset = ($page - 1) * $perPage;

    $countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSQL}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "SELECT p.*, b.name AS brand_name, c.name AS category_name,
                   (SELECT pi.path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 1 LIMIT 1) AS main_image
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSQL}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$product) {
        $product['tags'] = [];
    }

    jsonSuccess([
        'products'    => $products,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => (int)ceil($total / $perPage),
    ]);
}

function getProduct(): void {
    $db = getDB();
    $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $slug = $_GET['slug'] ?? null;

    if (!$id && !$slug) {
        jsonError('Укажите id или slug товара', 422);
        return;
    }

    $where = $id ? 'p.id = ?' : 'p.slug = ?';
    $param = $id ?: $slug;

    $stmt = $db->prepare("SELECT p.*, b.name AS brand_name, b.slug AS brand_slug, c.name AS category_name, c.slug AS category_slug
                           FROM products p
                           LEFT JOIN brands b ON p.brand_id = b.id
                           LEFT JOIN categories c ON p.category_id = c.id
                           WHERE {$where} AND p.is_active = 1");
    $stmt->execute([$param]);
    $product = $stmt->fetch();

    if (!$product) {
        jsonError('Товар не найден', 404);
        return;
    }

    $db->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = ?")->execute([$product['id']]);

    $imgStmt = $db->prepare("SELECT path, alt, is_main FROM product_images WHERE product_id = ? ORDER BY sort_order");
    $imgStmt->execute([$product['id']]);
    $product['images'] = $imgStmt->fetchAll();

    $product['attributes'] = [];
    $product['tags'] = [];
    $product['reviews'] = [];

    jsonSuccess(['product' => $product]);
}

function searchProducts(): void {
    $db = getDB();
    $query = trim($_GET['q'] ?? '');

    if (mb_strlen($query) < 2) {
        jsonError('Минимум 2 символа для поиска', 422);
        return;
    }

    $stmt = $db->prepare("SELECT p.id, p.name, p.slug, p.price, p.old_price, b.name AS brand_name,
                                  (SELECT pi.path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 1 LIMIT 1) AS main_image
                           FROM products p
                           LEFT JOIN brands b ON p.brand_id = b.id
                           WHERE p.is_active = 1 AND MATCH(p.name, p.short_desc, p.description) AGAINST(? IN BOOLEAN MODE)
                           ORDER BY p.sales_count DESC
                           LIMIT 20");
    $stmt->execute([$query . '*']);
    $results = $stmt->fetchAll();

    if (empty($results)) {
        $like = '%' . $query . '%';
        $stmt = $db->prepare("SELECT p.id, p.name, p.slug, p.price, p.old_price, b.name AS brand_name,
                                      (SELECT pi.path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 1 LIMIT 1) AS main_image
                               FROM products p
                               LEFT JOIN brands b ON p.brand_id = b.id
                               WHERE p.is_active = 1 AND (p.name LIKE ? OR p.short_desc LIKE ?)
                               ORDER BY p.sales_count DESC
                               LIMIT 20");
        $stmt->execute([$like, $like]);
        $results = $stmt->fetchAll();
    }

    jsonSuccess(['results' => $results, 'query' => $query]);
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
