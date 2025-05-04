<?php
require 'db.php'; // Подключение к базе данных
require 'mailer.php'; // Файл для отправки почты

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Проверка на существование email
    $user = get_user_by_email($email);
    if ($user) {
        // Генерация токена
        $token = bin2hex(random_bytes(16));
        $expires = date('U') + 3600; // Токен действителен 1 час

        // Сохранение токена в базе данных
        save_token($user['id'], $token, $expires);

        // Подготовка ссылки для восстановления пароля
        $resetLink = "http://yourwebsite.com/reset_password.php?token=" . $token;

        // Отправка email
        send_email($email, "Ссылка для восстановления пароля", "Перейдите по следующей ссылке, чтобы восстановить пароль: $resetLink");
        
        echo "Ссылка для восстановления пароля отправлена на ваш email.";
    } else {
        echo "Пользователь с таким email не найден.";
    }
}
?>