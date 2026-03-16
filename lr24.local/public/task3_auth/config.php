<?php
/**
 * Конфигурация авторизации — пароль администратора
 * Пароль по умолчанию: admin
 */
session_start();

define('ADMIN_PASSWORD_HASH', password_hash('admin', PASSWORD_DEFAULT));

function isLoggedIn(): bool {
    return !empty($_SESSION['admin_logged']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ../site/index.php');
        exit;
    }
}

function login(string $password): bool {
    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_time'] = time();
        return true;
    }
    return false;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}
