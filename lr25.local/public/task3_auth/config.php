<?php
// Конфигурация и функции авторизации для личного сайта (задание №3).

session_start();

// Задайте здесь пароль для входа на сайт.
// Сейчас пароль: admin (можете поменять на свой).
const SITE_PASSWORD = 'admin';

/**
 * Успешный вход по паролю.
 */
function login(string $password): bool
{
    if ($password === SITE_PASSWORD) {
        $_SESSION['site_logged_in'] = true;
        return true;
    }

    return false;
}

/**
 * Проверка, авторизован ли пользователь.
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['site_logged_in']);
}

