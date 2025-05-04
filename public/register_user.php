<?php
require_once('db.php');

$name = htmlspecialchars(trim($_POST['name_user']), ENT_QUOTES, 'UTF-8');
$login = htmlspecialchars(trim($_POST['login']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$pass = htmlspecialchars(trim($_POST['pass']), ENT_QUOTES, 'UTF-8');
$repeatpass = htmlspecialchars(trim($_POST['repeatpass']), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');


// Проверка валидности имени
// if (mb_strlen($name) < 5 || mb_strlen($name) > 50) {
//     echo "<p>Недопустимая длина имени.</p>
//     <form action='register_user.php' method='get'>
//         <button type='submit'>Вернуться к регистрации</button>
//     </form>";
//     exit();
// }

// Проверка валидности логина
if (mb_strlen($login) < 5 || mb_strlen($login) > 50) {
    echo "<p>Недопустимая длина логина.</p>
    <form action='register_user.php' method='get'>
        <button type='submit'>Вернуться к регистрации</button>
    </form>";
    exit();
}

// Проверка валидности email
if ($email === false || mb_strlen(explode('@', trim($_POST['email']))[0]) < 8) {
    echo "<p>Некорректный email. Локальная часть должна содержать минимум 8 символов.</p>
    <form action='register_user.php' method='get'>
        <button type='submit'>Вернуться к регистрации</button>
    </form>";
    exit();
}

// Проверка номера телефона
if (empty($phone)) {
    echo "<p>Номер телефона обязателен.</p>";
    exit();
}

// Регулярное выражение для проверки формата номера телефона
$phonePattern = '/^(?:\+375(44|29|33|25)|80(44|29|33|25))\d{7}$/';

if (!preg_match($phonePattern, $phone)) {
    echo "<p>Некорректный номер телефона. Он должен начинаться с +375 или 80 и содержать код (44, 29, 33, 25) и ровно 7 цифр.</p>";
    exit();
}

// Проверка длины пароля
if (mb_strlen($pass) < 8 || mb_strlen($pass) > 50) {
    echo "<p>Пароль должен быть от 8 до 50 символов.</p>
    <form action='register_user.php' method='get'>
        <button type='submit'>Вернуться к регистрации</button>
    </form>";
    exit();
}

// Проверка совпадения паролей
if ($pass !== $repeatpass) {
    echo "<p>Пароли не совпадают.</p>
    <form action='register_user.php' method='get'>
        <button type='submit'>Вернуться к регистрации</button>
    </form>";
    exit();
}

// Хеширование пароля
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

// Проверка существования логина и email в базе данных
$db = getDbConnection(); // Получаем подключение через функцию
$sql = "SELECT * FROM `users` WHERE `login` = :login OR `email` = :email";
$stmt = $db->prepare($sql);
$stmt->execute([':login' => $login, ':email' => $email]);

if ($stmt->rowCount() > 0) {
    echo "
    <p>Пользователь с таким логином или email уже существует.</p>
    <form action='register_user.php' method='get'>
        <button type='submit'>Вернуться к регистрации</button>
    </form>
    ";
    exit();
}

// Подготовка SQL запроса
$sql = "INSERT INTO `users` (`name_user`, `login`, `pass`, `email`, `phone`) VALUES (:name_user, :login, :pass, :email, :phone)";
$stmt = $db->prepare($sql);

// Выполнение запроса
try {
    $stmt->execute([
        ':name_user' => $name, // Добавлено
        ':login' => $login,
        ':pass' => $hashedPass,
        ':email' => $email,
        ':phone' => $phone 
    ]);
    header('Location: index.php'); // Удален echo перед header
    exit(); // Добавлен exit для предотвращения дальнейшего выполнения
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>