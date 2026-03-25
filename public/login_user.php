<?php
require 'db.php'; // Подключаем db.php, где создается функция для подключения

// Начало сессии
session_start();

// Получение и экранирование данных из POST-запроса
$login = htmlspecialchars(trim($_POST['login']), ENT_QUOTES, 'UTF-8');
$pass = htmlspecialchars(trim($_POST['pass']), ENT_QUOTES, 'UTF-8');

// Получаем подключение к базе данных
$pdo = getDbConnection();

// Подготовка SQL запроса для проверки пользователя
$query = "SELECT * FROM `users` WHERE `login` = :login";
$user = $pdo->prepare($query);
$user->execute([':login' => $login]);

// Проверка, найден ли пользователь
if ($user->rowCount() === 0) {
    echo "
    <p>Такой пользователь не найден</p>
    <form action='register.php' method='get'>
        <button type='submit'>Вернуться к авторизации</button>
    </form>
    ";
    exit();
}

// Получение данных пользователя
$userData = $user->fetch(PDO::FETCH_ASSOC);

// Проверка пароля
if (!password_verify($pass, $userData['pass'])) {
    echo "<p>Неправильный пароль</p>
    <form action='register.php' method='post'>
        <button type='submit'>Попробовать снова</button>
    </form>
    ";
    exit();
}

// Если все проверки пройдены, устанавливаем сессионную переменную
$_SESSION['user'] = $login; // Вы можете сохранить логин, если нужно
$_SESSION['user_id'] = $userData['id_user']; // Устанавливаем id_user в сессию

// Перенаправление на главную страницу или отображение сообщения
header('Location: index.php');
exit();
?>
