
<?php
// Пример использования предопределенных констант и переменных
echo '<div style="color: green;   font-family: \'Times New Roman\', serif;  text-align: center;  font-weight: bold;  font-size: 44px;">';

echo "PHP version: " . PHP_VERSION . "<br>";
echo "Operating System: " . PHP_OS . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "<br>";
echo '</div>';
?>
