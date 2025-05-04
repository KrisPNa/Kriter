<?php
session_start(); // Начинаем сессию
session_destroy(); // Удаляем все сессии
header("Location: index.php"); // Перенаправление на главную страницу
exit(); // Завершаем выполнение скрипта
?>