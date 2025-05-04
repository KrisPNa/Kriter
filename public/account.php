<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pdo = getDbConnection();

// Получение данных пользователя
$stmt = $pdo->prepare('
    SELECT name_user, login, email, phone 
    FROM users 
    WHERE id_user = ?
');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newName = trim($_POST['name_user']);
    $newPhone = trim($_POST['phone']);
    $newEmail = trim($_POST['email']);
    
    // Валидация имени
    if (strlen($newName) > 20) {
        $errors['name_user'] = 'Имя не должно превышать 20 символов';
    }
    if (preg_match('/[^a-zA-Zа-яА-Я0-9\s]/u', $newName)) {
        $errors['name_user'] = 'Имя не должно содержать специальные символы';
    }
    if (preg_match('/^\s/', $newName)) {
        $errors['name_user'] = 'Имя не должно начинаться с пробела';
    }
    if (preg_match('/\s{2,}/', $newName)) {
        $errors['name_user'] = 'Имя не должно содержать более одного пробела подряд';
    }
    
    // Валидация email
    if (strlen($newEmail) > 50) {
        $errors['email'] = 'Email не должен превышать 50 символов';
    }
    if (preg_match('/\s/', $newEmail)) {
        $errors['email'] = 'Email не должен содержать пробелы';
    }
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Неверный формат email';
    }
    
    // Проверка уникальности email
    $stmt = $pdo->prepare('SELECT id_user FROM users WHERE email = ? AND id_user != ?');
    $stmt->execute([$newEmail, $userId]);
    if ($stmt->fetch()) {
        $errors['email'] = 'Этот email уже используется другим пользователем';
    }
    
    // Валидация телефона
    if (!preg_match('/^\+375\((29|33|44|25)\)\d{3}-\d{2}-\d{2}$/', $newPhone)) {
        $errors['phone'] = 'Телефон должен быть в формате +375(XX)XXX-XX-XX';
    }
    
    // Если нет ошибок, обновляем данные
    if (empty($errors)) {
        $stmt = $pdo->prepare('
            UPDATE users 
            SET name_user = ?, phone = ?, email = ? 
            WHERE id_user = ?
        ');
        $stmt->execute([$newName, $newPhone, $newEmail, $userId]);
        
        header('Location: account.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .account-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .profile-section {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-form {
            max-width: 400px;
            margin: 0 auto;
            text-align: left;
        }
        .profile-form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .profile-form input:disabled {
            background-color: #f5f5f5;
            color: #666;
        }
        .profile-form input.error {
            border-color: #ff0000;
        }
        .error-message {
            color: #ff0000;
            font-size: 12px;
            margin-top: -8px;
            margin-bottom: 10px;
        }
        .nav-button {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            transition: background 0.3s;
        }
        .nav-button:hover {
            background: #45a049;
        }
        .edit-button, .save-button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .edit-button:hover, .save-button:hover {
            background: #45a049;
        }
        .save-button {
            display: none;
        }
        .edit-mode .save-button {
            display: block;
        }
        .edit-mode .edit-button {
            display: none;
        }
    </style>
</head>
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
            <a href="logout.php">Выход</a>
        </div>
    </header>

    <div class="account-container">
        <h1>Личный кабинет</h1>
        
        <!-- Профиль пользователя -->
        <div class="profile-section">
            <h2>Мой профиль</h2>
            <form class="profile-form" method="POST" action="account.php" id="profileForm" onsubmit="return validateForm()">
                <input type="hidden" name="update_profile" value="1">
                <div>
                    <label>Имя:</label>
                    <input type="text" name="name_user" value="<?= htmlspecialchars($user['name_user']) ?>" disabled required
                           maxlength="20" pattern="[a-zA-Zа-яА-Я0-9\s]+" title="Только буквы, цифры и один пробел">
                    <?php if (isset($errors['name_user'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['name_user']) ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>Логин:</label>
                    <input type="text" value="<?= htmlspecialchars($user['login']) ?>" disabled>
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled required
                           maxlength="50" pattern="[^\s]+" title="Email не должен содержать пробелы">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>Телефон:</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" disabled
                           title="Формат: +375(XX)XXX-XX-XX, где XX - код оператора (29, 33, 44, 25)"
                           oninput="formatPhoneNumber(this)"
                           maxlength="17">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>
                <button type="button" class="edit-button" onclick="toggleEdit()">Изменить личные данные</button>
                <button type="submit" class="save-button">Сохранить изменения</button>
            </form>
        </div>

        <!-- Кнопки навигации -->
        <div>
            <a href="cart.php" class="nav-button">Перейти в корзину</a>
            <a href="order.php" class="nav-button">Перейти к заказам</a>
        </div>
    </div>

    <script>
        function toggleEdit() {
            const form = document.getElementById('profileForm');
            const inputs = form.querySelectorAll('input[name="name_user"], input[name="email"], input[name="phone"]');
            const editButton = form.querySelector('.edit-button');
            const saveButton = form.querySelector('.save-button');
            
            if (form.classList.contains('edit-mode')) {
                // Выход из режима редактирования
                inputs.forEach(input => input.disabled = true);
                form.classList.remove('edit-mode');
            } else {
                // Вход в режим редактирования
                inputs.forEach(input => input.disabled = false);
                form.classList.add('edit-mode');
            }
        }

        function formatPhoneNumber(input) {
            // Удаляем все нецифровые символы
            let value = input.value.replace(/\D/g, '');
            
            // Если номер начинается с 375, добавляем +
            if (value.startsWith('375')) {
                value = '+' + value;
            }
            
            // Форматируем номер
            if (value.length > 3) {
                value = value.substring(0, 4) + '(' + value.substring(4);
            }
            if (value.length > 7) {
                value = value.substring(0, 7) + ')' + value.substring(7);
            }
            if (value.length > 11) {
                value = value.substring(0, 11) + '-' + value.substring(11);
            }
            if (value.length > 14) {
                value = value.substring(0, 14) + '-' + value.substring(14);
            }
            
            // Ограничиваем длину
            if (value.length > 17) {
                value = value.substring(0, 17);
            }
            
            input.value = value;
        }

        function validatePhoneNumber(phone) {
            // Удаляем все нецифровые символы
            const cleanPhone = phone.replace(/\D/g, '');
            
            // Проверяем длину и начало номера
            if (cleanPhone.length !== 12) return false;
            if (!cleanPhone.startsWith('375')) return false;
            
            // Проверяем код оператора
            const operatorCode = cleanPhone.substring(3, 5);
            const validOperators = ['29', '33', '44', '25'];
            if (!validOperators.includes(operatorCode)) return false;
            
            return true;
        }

        function validateForm() {
            let isValid = true;
            const form = document.getElementById('profileForm');
            
            // Валидация имени
            const nameInput = form.querySelector('input[name="name_user"]');
            const nameValue = nameInput.value.trim();
            
            if (nameValue.length > 20) {
                showError(nameInput, 'Имя не должно превышать 20 символов');
                isValid = false;
            }
            if (/[^a-zA-Zа-яА-Я0-9\s]/.test(nameValue)) {
                showError(nameInput, 'Имя не должно содержать специальные символы');
                isValid = false;
            }
            if (/^\s/.test(nameValue)) {
                showError(nameInput, 'Имя не должно начинаться с пробела');
                isValid = false;
            }
            if (/\s{2,}/.test(nameValue)) {
                showError(nameInput, 'Имя не должно содержать более одного пробела подряд');
                isValid = false;
            }
            
            // Валидация email
            const emailInput = form.querySelector('input[name="email"]');
            const emailValue = emailInput.value.trim();
            
            if (emailValue.length > 50) {
                showError(emailInput, 'Email не должен превышать 50 символов');
                isValid = false;
            }
            if (/\s/.test(emailValue)) {
                showError(emailInput, 'Email не должен содержать пробелы');
                isValid = false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
                showError(emailInput, 'Неверный формат email');
                isValid = false;
            }
            
            // Валидация телефона
            const phoneInput = form.querySelector('input[name="phone"]');
            const phoneValue = phoneInput.value.trim();
            
            if (!validatePhoneNumber(phoneValue)) {
                showError(phoneInput, 'Телефон должен быть в формате +375(XX)XXX-XX-XX, где XX - код оператора (29, 33, 44, 25)');
                isValid = false;
            }
            
            return isValid;
        }

        function showError(input, message) {
            input.classList.add('error');
            let errorDiv = input.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('error-message')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
            errorDiv.textContent = message;
        }
    </script>

    <footer>
        <p>&copy; 2023 Кондитерская "Kriter"</p>
    </footer>
</body>
</html>
