<?php
session_start();

// Определяем тип формы для отображения
$formType = isset($_POST['form_type']) ? $_POST['form_type'] : 'user';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация - Кондитерская "Kriter"</title>
    <link rel="stylesheet" href="styles.css">
    </head>

    <style>
        .content {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
        }
        input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #c0392b;
        }
        .toggle-form {
            text-align: center;
            margin-top: 20px;
        }
        .hidden {
            display: none;
        }
    </style>

<body>

<header>
    <div class="logo">
        <a href="index.php">
            <img src="img/logo.png" alt="логотип" width="140" height="140" />
        </a>
    </div>
    <div class="sidebar">
        <a class="sidebar-1" href="about.php">О нас</a>
        <a class="sidebar-1" href="menu.php">Меню</a>
        <a class="sidebar-1" href="cart.php">Корзина</a>
        <a class="sidebar-1" href="cont.php">Контакты</a>
        <a class="sidebar-1" href="order.php">Заказы</a>
    </div>

    <div class="nav">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="account.php">Личный кабинет</a>
        <?php else: ?>
            <a href="register.php">Авторизация</a>
        <?php endif; ?>
    </div>
</header>

<div class="content">
    <?php if ($formType === 'user'): ?>
        <h2>Авторизация пользователя</h2>
<form action="login_user.php" method="post">
    <label for="username">Имя пользователя:</label>
    <input type="text" name="login" placeholder="Логин" required>

    <label for="password">Пароль:</label>
    <input type="password" name="pass" placeholder="Пароль" required>

    <button type="submit">Войти</button>
</form>



<div class="toggle-form">
    <form method="post" style="display: inline;">
        <input type="hidden" name="form_type" value="register">
        <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">Нет аккаунта? Зарегистрироваться</button>
    </form>
    <form method="post" style="display: inline;">
        <input type="hidden" name="form_type" value="admin">
        <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">Авторизация администратора</button>
    </form>
</div>
    <?php elseif ($formType === 'admin'): ?>
        <h2>Авторизация администратора</h2>
        <form action="login_admin.php" method="post">
    <label for="login">Имя пользователя:</label>
    <input type="text" id="login" name="login" placeholder="Логин" required>

    <label for="pass">Пароль:</label>
    <input type="password" id="pass" name="pass" placeholder="Пароль" required>

    <label for="personal_code">Личный код:</label>
    <input type="text" id="personal_code" name="personal_code" placeholder="Личный код" maxlength="6" pattern="\d{6}" required>

    <label for="phone">Номер телефона:</label>
    <input type="text" id="phone" name="phone"  placeholder="Номер телефона"required>

    <button type="submit">Войти</button>
</form>

        <div class="toggle-form">
            <form method="post" style="display: inline;">
                <input type="hidden" name="form_type" value="user">
                <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">Вернуться к авторизации пользователя</button>
            </form>
            <form method="post" style="display: inline;">
                <input type="hidden" name="form_type" value="admin_register">
                <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">Регистрация администратора</button>
            </form>
        </div>
        <?php elseif ($formType === 'admin_register'): ?>
        <h2>Регистрация администратора</h2>
        <form action="register_admin.php" method="post">
            <label for="login">Имя пользователя:</label>
            <input type="text" id="login" name="login" placeholder="Логин" required>

            <label for="pass">Пароль:</label>
            <input type="password" id="pass" name="pass" placeholder="Пароль" required>

            <label for="personal_code">Личный код:</label>
            <input type="text" id="personal_code" name="personal_code" placeholder="Личный код" maxlength="6" pattern="\d{6}" required>

            <label for="phone">Номер телефона:</label>
            <input type="text" id="phone" name="phone" placeholder="Номер телефона" required>

            <button type="submit">Зарегистрироваться</button>
        </form>
        <div class="toggle-form">
            <form method="post" style="display: inline;">
                <input type="hidden" name="form_type" value="admin">
                <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">Вернуться к авторизации администратора</button>
            </form>
        </div>
    

    <?php elseif ($formType === 'register'): ?>
        <h2>Регистрация пользователя</h2>
        <form action="register_user.php" method="post">
            <label for="username">Никнейм:</label>
            <input type="text" name="username" placeholder="Никнейм" required>
            <label for="login">Имя пользователя:</label>
            <input type="text" name="login" placeholder="Логин" required>
            <label for="email">Email:</label>
            <input type="email" name="email" placeholder="Email" required>
            <label for="phone">Номер телефона:</label>
        <input type="text" name="phone" placeholder="Номер телефона" required>
            <label for="password">Пароль:</label>
            <input type="password" name="pass" placeholder="Пароль" required>
            <label for="confirm-password">Подтвердите пароль:</label>
            <input type="password" name="repeatpass" placeholder="Повторите пароль" required>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <div class="toggle-form">
            <form method="post" style="display: inline;">
                <input type="hidden" name="form_type" value="user">
                <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">Уже есть аккаунт? Войти</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
