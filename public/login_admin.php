<?php
require 'db.php'; // Подключаем db.php, где создается функция для подключения

session_start();

// Если страницу открыли без отправки формы — не трогаем БД и показываем ссылку на правильный экран
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php?form_type=admin');
    exit();
}

// Начало сессии
// Получение и экранирование данных из POST-запроса
$login = trim($_POST['login'] ?? '');
$pass = trim($_POST['pass'] ?? '');
$personal_code = trim($_POST['personal_code'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($login === '' || $pass === '' || $personal_code === '' || $phone === '') {
    header('Location: register.php?form_type=admin');
    exit();
}

// Получаем подключение к базе данных
$pdo = getDbConnection();

// Подготовка SQL запроса для проверки администратора
// Сначала пытаемся найти по логину. Если не нашли — пробуем по телефону.
$query = "SELECT * FROM `admin_users` WHERE `login` = :login";
$user = $pdo->prepare($query);
$user->execute([':login' => $login]);

if ($user->rowCount() === 0) {
    $query = "SELECT * FROM `admin_users` WHERE `phone` = :phone";
    $user = $pdo->prepare($query);
    $user->execute([':phone' => $phone]);
}

// Проверка, найден ли пользователь
if ($user->rowCount() === 0) {
    echo "
    <p>Такой пользователь не найден</p>
    <a href='register.php?form_type=admin'>Попробовать снова</a>
    ";
    exit();
}

// Получение данных администратора
$adminData = $user->fetch(PDO::FETCH_ASSOC);

// Проверка пароля
if (!password_verify($pass, $adminData['pass_hash'])) {
    echo "<p>Неправильный пароль</p>
    <a href='register.php?form_type=admin'>Попробовать снова</a>
    ";
    exit();
}

// Проверка хэша личного кода и номера телефона
if (!password_verify($personal_code, $adminData['personal_code_hash'])) {
    echo "<p>Неправильный личный код</p>
    <a href='register.php?form_type=admin'>Попробовать снова</a>
    ";
    exit();
}

if ($phone !== $adminData['phone']) {
    echo "<p>Неправильный номер телефона</p>
    <a href='register.php?form_type=admin'>Попробовать снова</a>
    ";
    exit();
}

// Если все проверки пройдены, устанавливаем сессионную переменную
$_SESSION['admin_user'] = $adminData['login'] ?? $login; // Сохраняем логин администратора
$_SESSION['admin_id'] = $adminData['admin_id'] ?? null; // Устанавливаем admin_id в сессию
if ($_SESSION['admin_id'] === null) {
    // Если в таблице вдруг другая PK-колонка (на случай рассинхрона), не пускаем дальше
    echo "<p>Ошибка: не удалось получить ID администратора.</p>";
    exit();
}

// Перенаправление на админскую страницу или отображение нужного контента
header('Location: index_admin.php');
exit();
?>
