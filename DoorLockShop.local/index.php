<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

if ($path === '/' || $path === '/index.php') {
    header('Location: DoorLocksShop.html');
    exit;
}

http_response_code(404);
echo '404 — Страница не найдена';
