<?php
declare(strict_types=1);

// Задание №1. Использование стандартных функций PHP для работы с файлами и каталогами.
// Примеры №1–4 оформлены как отдельные блоки.

mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');

// Общая рабочая папка для примеров
$baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'files_examples';

if (!is_dir($baseDir)) {
    mkdir($baseDir, 0777, true);
}

echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><title>Задание №1: файлы и каталоги</title></head><body>';
echo '<h1>Задание №1: стандартные функции PHP для работы с файлами и каталогами</h1>';

// Пример 1. Создание каталога и текстового файла, запись строки в файл.
echo '<h2>Пример 1. Создание файла и запись данных</h2>';
$file1 = $baseDir . DIRECTORY_SEPARATOR . 'example1.txt';
$text1 = "Это пример №1.\nСтрока, записанная функциями fopen/fwrite/fclose.";

$handle1 = fopen($file1, 'w');
fwrite($handle1, $text1);
fclose($handle1);

echo '<p>Создан файл: <code>' . htmlspecialchars($file1, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></p>';
echo '<pre>' . htmlspecialchars($text1, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';

// Пример 2. Чтение содержимого файла разными способами (file_get_contents и построчно).
echo '<h2>Пример 2. Чтение содержимого файла</h2>';
$content = file_get_contents($file1);
echo '<p>Чтение с помощью <code>file_get_contents</code>:</p>';
echo '<pre>' . htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';

echo '<p>Чтение построчно с помощью <code>fgets</code>:</p><pre>';
$handle2 = fopen($file1, 'r');
while (!feof($handle2)) {
    $line = fgets($handle2);
    if ($line === false) {
        break;
    }
    echo htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
fclose($handle2);
echo '</pre>';

// Пример 3. Просмотр содержимого каталога (список файлов и подкаталогов).
echo '<h2>Пример 3. Просмотр каталога</h2>';
echo '<p>Содержимое каталога <code>' . htmlspecialchars($baseDir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>:</p>';

echo '<ul>';
if ($dh = opendir($baseDir)) {
    while (($item = readdir($dh)) !== false) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $item;
        $type = is_dir($fullPath) ? 'каталог' : 'файл';
        echo '<li>' . htmlspecialchars($item, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
            ' — ' . $type . '</li>';
    }
    closedir($dh);
}
echo '</ul>';

// Пример 4. Копирование, переименование и удаление файла.
echo '<h2>Пример 4. Копирование, переименование и удаление файла</h2>';

$copyFile = $baseDir . DIRECTORY_SEPARATOR . 'example1_copy.txt';
$renamedFile = $baseDir . DIRECTORY_SEPARATOR . 'example1_renamed.txt';

// Копирование
if (copy($file1, $copyFile)) {
    echo '<p>Файл успешно скопирован в <code>' . htmlspecialchars($copyFile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></p>';
} else {
    echo '<p>Не удалось скопировать файл.</p>';
}

// Переименование
if (rename($copyFile, $renamedFile)) {
    echo '<p>Копия файла переименована в <code>' . htmlspecialchars($renamedFile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></p>';
} else {
    echo '<p>Не удалось переименовать файл.</p>';
}

// Удаление
if (file_exists($renamedFile) && unlink($renamedFile)) {
    echo '<p>Файл <code>' . htmlspecialchars($renamedFile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code> удалён.</p>';
} else {
    echo '<p>Не удалось удалить файл (возможно, он уже удалён).</p>';
}

echo '<hr>';
echo '<p><a href="task2_date.php">Перейти к заданию №2 (дата и время)</a></p>';
echo '</body></html>';

