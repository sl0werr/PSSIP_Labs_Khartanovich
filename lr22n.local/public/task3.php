<?php
function task3()
{
    $fullName = "Хартанович Никита";
    $count = 17 + 5;
    // Используем цикл for 
    for ($i = 1; $i <= $count; $i++) {
        echo "<div>($i) $fullName</div>";
    }

    // Выводим дополнительную информацию
    echo "<div><strong>Номер варианта (n):</strong> 17</div>";
    echo "<div><strong>Количество повторений (n + 5):</strong> $count</div>";
}
?>
