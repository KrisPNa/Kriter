<?php
require 'db.php'; // Подключаем db.php, где создается функция для подключения

// Начало сессии
session_start();

// Получение и экранирование данных из POST-запроса
$login = htmlspecialchars(trim($_POST['login']), ENT_QUOTES, 'UTF-8');
$pass = htmlspecialchars(trim($_POST['pass']), ENT_QUOTES, 'UTF-8');
$personal_code = htmlspecialchars(trim($_POST['personal_code']), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');

// Получаем подключение к базе данных
$pdo = getDbConnection();

// Подготовка SQL запроса для проверки администратора
$query = "SELECT * FROM `admin_users` WHERE `login` = :login";
$user = $pdo->prepare($query);
$user->execute([':login' => $login]);

// Проверка, найден ли пользователь
if ($user->rowCount() === 0) {
    echo "
    <p>Такой пользователь не найден</p>
    <form action='admin_login.php' method='post'>
        <button type='submit'>Попробовать снова</button>
    </form>
    ";
    exit();
}

// Получение данных администратора
$adminData = $user->fetch(PDO::FETCH_ASSOC);

// Проверка пароля
if (!password_verify($pass, $adminData['pass_hash'])) {
    echo "<p>Неправильный пароль</p>
    <form action='register.php' method='post'>
        <button type='submit'>Попробовать снова</button>
    </form>
    ";
    exit();
}

// Проверка хэша личного кода и номера телефона
if (!password_verify($personal_code, $adminData['personal_code_hash'])) {
    echo "<p>Неправильный личный код</p>
    <form action='register.php' method='post'>
        <button type='submit'>Попробовать снова</button>
    </form>
    ";
    exit();
}

if ($phone !== $adminData['phone']) {
    echo "<p>Неправильный номер телефона</p>
    <form action='register.php' method='post'>
        <button type='submit'>Попробовать снова</button>
    </form>
    ";
    exit();
}

// Если все проверки пройдены, устанавливаем сессионную переменную
$_SESSION['admin_user'] = $login; // Сохраняем логин администратора
$_SESSION['admin_id'] = $adminData['id_admin']; // Устанавливаем id_admin в сессию

// Перенаправление на админскую страницу или отображение нужного контента
header('Location: index.php');
exit();
?>
