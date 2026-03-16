<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Moscow');

// Задание №2. Функции для работы с датой, временем и календарём.

/**
 * Специальная функция, возвращающая название дня недели на русском языке.
 * Можно передать произвольную метку времени, по умолчанию — текущая дата.
 */
function getWeekday(?int $timestamp = null): string
{
    if ($timestamp === null) {
        $timestamp = time();
    }

    // Вспомогательная переменная, как рекомендуют в задании
    $weekday = date('D', $timestamp); // Mon, Tue, Wed, Thu, Fri, Sat, Sun

    if ($weekday == 'Mon') {
        $weekday = 'понедельник';
    } elseif ($weekday == 'Tue') {
        $weekday = 'вторник';
    } elseif ($weekday == 'Wed') {
        $weekday = 'среда';
    } elseif ($weekday == 'Thu') {
        $weekday = 'четверг';
    } elseif ($weekday == 'Fri') {
        $weekday = 'пятница';
    } elseif ($weekday == 'Sat') {
        $weekday = 'суббота';
    } elseif ($weekday == 'Sun') {
        $weekday = 'воскресенье';
    }

    return $weekday;
}

// Обработка нажатия кнопки формы
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_day'])) {
    $message = 'Сегодня день недели: ' . getWeekday();
}

echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><title>Задание №2: дата и время</title></head><body>';
echo '<h1>Задание №2: дата, время и календарь</h1>';

// 1. Вывод текущей даты и времени в три строки
echo '<h2>Пункт 1. Текущая дата и время</h2>';
$now = time();

// Первая строка — дата в кратком формате (например, 1. 04. 2023)
echo '<p>';
echo date('j. m. Y', $now) . '<br>';      // дата
echo date('H:i:s', $now) . '<br>';        // время
echo getWeekday($now) . '<br>';    // день недели
echo '</p>';

// 2. Проверка функции getnWeekday для текущей даты
echo '<h2>Пункт 2. Проверка функции дня недели</h2>';
echo '<p>Сегодня: ' . getWeekday($now) . '</p>';

// 3. Форма с кнопкой и выводом сообщения
echo '<h2>Пункт 3. Форма с кнопкой</h2>';

// Текущая дата в числовом формате
echo '<p>Текущая дата (числовой формат): ' . date('d.m.Y', $now) . '</p>';

echo '<form method="post">';
echo '<button type="submit" name="show_day">Показать день недели</button>';
echo '</form>';

if ($message !== '') {
    echo '<p><strong>' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong></p>';
}

echo '<hr>';
echo '<p><a href="task1_files.php">Перейти к заданию №1 (файлы и каталоги)</a></p>';
echo '</body></html>';

