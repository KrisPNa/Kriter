<?php
require 'db.php'; // Подключение к базе данных

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Проверка токена в базе данных
    $userData = verify_token($token);
    if ($userData) {
        // Отображение формы для ввода нового пароля
        ?>
        <form action="update_password.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $userData['user_id']; ?>">
            <label for="new_password">Введите новый пароль:</label>
            <input type="password" id="new_password" name="new_password" required>
            <button type="submit">Сохранить новый пароль</button>
        </form>
        <?php
    } else {
        echo "Неверный или истекший токен.";
    }
}
?>