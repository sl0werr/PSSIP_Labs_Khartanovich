<?php
function task2()
{
    // Устанавливаем часовой пояс
    date_default_timezone_set('Europe/Moscow');
    $current_datetime = new DateTime();

    echo "<div>";
    $days = date('t');
    echo "В текущем месяце $days дней";
    echo "</div>";
}

