<?php
require_once('db.php');

// Sanitize and validate inputs
$login = htmlspecialchars(trim($_POST['login']), ENT_QUOTES, 'UTF-8');
$personal_code = htmlspecialchars(trim($_POST['personal_code']), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');

// Проверка длины логина
if (mb_strlen($login) < 5 || mb_strlen($login) > 50) {
    echo "<p>Недопустимая длина логина.</p>
    <form action='register_admin.php' method='get'>
        <button type='submit'>Вернуться к регистрации администратора</button>
    </form>";
    exit();
}

// Проверка длины личного кода
if (mb_strlen($personal_code) !== 6 || !preg_match('/^\d{6}$/', $personal_code)) {
    echo "<p>Личный код должен содержать 6 цифр.</p>
    <form action='register_admin.php' method='get'>
        <button type='submit'>Вернуться к регистрации администратора</button>
    </form>";
    exit();
}

// Проверка длины телефона
if (!preg_match('/^\+?\d{10,15}$/', $phone)) {
    echo "<p>Некорректный номер телефона.</p>
    <form action='register_admin.php' method='get'>
        <button type='submit'>Вернуться к регистрации администратора</button>
    </form>";
    exit();
}

// Хеширование пароля и личного кода
$hashedPass = password_hash($_POST['pass'], PASSWORD_BCRYPT);
$hashedPersonalCode = password_hash($personal_code, PASSWORD_BCRYPT);
$created_at = date('Y-m-d H:i:s');

// Проверка существования логина и номера телефона в базе данных
$db = getDbConnection(); // Получаем подключение через функцию
$sql = "SELECT * FROM `admin_users` WHERE `login` = :login OR `phone` = :phone";
$stmt = $db->prepare($sql);
$stmt->execute([':login' => $login, ':phone' => $phone]);

if ($stmt->rowCount() > 0) {
    echo "
    <p>Администратор с таким логином или номером телефона уже существует.</p>
    <form action='register_admin.php' method='get'>
        <button type='submit'>Вернуться к регистрации администратора</button>
    </form>
    ";
    exit();
}

// Подготовка SQL запроса
$sql = "INSERT INTO `admin_users` (`login`, `pass_hash`, `personal_code_hash`, `phone`, `created_at`) 
        VALUES (:login, :pass, :personal_code, :phone, :created_at)";
$stmt = $db->prepare($sql);

// Выполнение запроса
try {
    $stmt->execute([
        ':login' => $login,
        ':pass' => $hashedPass,
        ':personal_code' => $hashedPersonalCode,
        ':phone' => $phone,
        ':created_at' => $created_at
    ]);
    echo header('Location: index.php');
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
