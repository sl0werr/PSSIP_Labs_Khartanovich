<?php

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>";
echo "<body style='margin: 0; padding: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; background-color: #f5f5f5;'>";
echo "<div style='font-family: \"Times New Roman\", Times, serif; font-size: 25px; color: green; text-align: center; line-height: 1.6; padding: 20px;'>";




// Определение константы
define("NUM_E", 2.71828);
echo "Число e равно " . NUM_E . "<br>";

// Отображаем тип переменной
$num_e1 = NUM_E;
echo "Тип переменной num_e1: " . gettype($num_e1) . "<br>";

// Изменение типов переменной
$num_e1 = (string)$num_e1;
echo "Строка: " . $num_e1 . ", тип: " . gettype($num_e1) . "<br>";

$num_e1 = (int)$num_e1;
echo "Целое: " . $num_e1 . ", тип: " . gettype($num_e1) . "<br>";

$num_e1 = (bool)$num_e1;
echo "Булевский: " . $num_e1 . ", тип: " . gettype($num_e1) . "<br>";

echo "</div></body></html>";
