<?php
require 'db.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT); // Хеширование пароля

    // Обновление пароля в базе данных
    update_user_password($user_id, $new_password);

    echo "Пароль успешно обновлен.";
}
?>