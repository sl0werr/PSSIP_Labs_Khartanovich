<?php
// Переменные для стилей
$color = "green";
$size = "36px";
$font = "'Times New Roman', Times, serif";

// Открываем div с центрированием и стилями
echo "<div style='display: flex; justify-content: center; align-items: center; font-weight: 800; min-height: 100vh; margin: 0; padding: 0; background-color: #f5f5f5;'>";
echo "<div style='text-align: center;'>";

// Выводим текст 
echo "<span style='color: $color; font-size: $size; font-family: $font;'>";
echo "Привет всем!!!<br>";
echo "Разработчик: Хартанович Никита Иванович, группа ПЗТ-40, 4 курс";
echo "</span>";

echo "</div>";
echo "</div>";
