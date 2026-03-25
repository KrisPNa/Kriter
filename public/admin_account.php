<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка, авторизован ли администратор
if (!isset($_SESSION['admin_user'])) {
    header('Location: login_admin.php');
    exit();
}

$adminId = $_SESSION['admin_id'] ?? null;
if ($adminId === null) {
    header('Location: login_admin.php');
    exit();
}
$pdo = getDbConnection();

// Получение данных администратора
$stmt = $pdo->prepare('
    SELECT login, phone, created_at 
    FROM admin_users 
    WHERE admin_id = ?
');
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$admin = is_array($admin) ? $admin : [];

$errors = [];

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newPhone = trim($_POST['phone']);
    $newLogin = trim($_POST['login']);
      
    if (strlen($newLogin) > 20) {
        $errors['name_user'] = 'Имя не должно превышать 20 символов';
    }
    if (preg_match('/[^a-zA-Zа-яА-Я0-9\s]/u', $newLogin)) {
        $errors['name_user'] = 'Имя не должно содержать специальные символы';
    }
    if (preg_match('/^\s/', $newLogin)) {
        $errors['name_user'] = 'Имя не должно начинаться с пробела';
    }
    if (preg_match('/\s{2,}/', $newLogin)) {
        $errors['name_user'] = 'Имя не должно содержать более одного пробела подряд';
    }

        // Валидация телефона
    if (!preg_match('/^\+375\((29|33|44|25)\)\d{3}-\d{2}-\d{2}$/', $newPhone)) {
        $errors['phone'] = 'Телефон должен быть в формате +375(XX)XXX-XX-XX';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare('
            UPDATE admin_users
            SET phone = ?, login = ?
            WHERE admin_id = ?
        ');
        $stmt->execute([$newPhone, $newLogin, $adminId]);
        header('Location: admin_account.php');
        exit();
    }
    
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет администратора</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .profile-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group input:disabled {
            background-color: #f5f5f5;
        }
        .error-message {
            color: red;
            margin-top: 5px;
        }
        .success-message {
            color: green;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
        }
        .edit-button, .save-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            margin-right: 10px;
        }
        .edit-button {
            background-color: #4CAF50;
        }
        .save-button {
            background-color: #2196F3;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="index_admin.php">
            <img src="img/logo.png" alt="логотип" width="100" height="100" />
        </a>
    </div>
    <div class="sidebar">
        <a class="sidebar-1" href="admin_dashboard.php">Админ Меню</a>
        <a class="sidebar-1" href="menu_admin.php">Меню Клиента</a>
        <a class="sidebar-1" href="manage_users.php">Управление Пользователями</a>
        <a class="sidebar-1" href="admin_orders.php">Управление Заказами</a>
    </div>
    <div class="nav">
        <a href="logout.php" onclick="return confirm('Уверены, что хотите выйти?');">Выход</a>
    </div>
</header>

<div class="content">
    <h2>Личный кабинет администратора</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            Данные успешно обновлены
        </div>
    <?php endif; ?>

    <div class="profile-form">
        <form method="POST" action="admin_account.php" id="profileForm" onsubmit="return validateForm()">
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" name="login" value="<?= htmlspecialchars($admin['login'] ?? '') ?>" disabled>
            </div>

            <div class="form-group">
                <label>Номер телефона:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>" disabled
                title="Формат: +375(XX)XXX-XX-XX, где XX - код оператора (29, 33, 44, 25)"
                oninput="formatPhoneNumber(this)"
                maxlength="17">
                <?php if (isset($errors['phone'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['phone']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Дата регистрации:</label>
                <?php
                    $createdAt = $admin['created_at'] ?? null;
                    $createdAtTs = is_string($createdAt) && $createdAt !== '' ? strtotime($createdAt) : false;
                    $createdAtFormatted = $createdAtTs ? date('d.m.Y', $createdAtTs) : '';
                ?>
                <input type="text" value="<?= htmlspecialchars($createdAtFormatted) ?>" disabled>
            </div>

            <button type="button" class="edit-button" onclick="toggleEdit()">Изменить</button>
            <button type="submit" name="update_profile" class="save-button hidden">Сохранить</button>
        </form>
    </div>
</div>

<script>
    function toggleEdit() {
        const form = document.getElementById('profileForm');
        const inputs = document.querySelectorAll('input[name="phone"]');
        const editButton = document.querySelector('.edit-button');
        const saveButton = document.querySelector('.save-button');
        
        if (editButton.classList.contains('hidden')) {
            inputs.forEach(input => {
                input.disabled = !input.disabled;
            });
            editButton.classList.remove('hidden');
            saveButton.classList.add('hidden');
        } else {
            inputs.forEach(input => {
                input.disabled = !input.disabled;
        });
        
            editButton.classList.toggle('hidden');
            saveButton.classList.toggle('hidden');
        }
    }

    function formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.startsWith('375')) {
            value = '+' + value;
        }

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

        if (value.length > 17) {
            value = value.substring(0, 17);
        }

        input.value = value;
    }

    function validatePhoneNumber(phone) {
        const cleanPhone = phone.replace(/\D/g, '');
        
        if (cleanPhone.length !== 12) return false;
        if (!cleanPhone.startsWith('375')) return false;

        const operatorCode = cleanPhone.substring(3, 5);
        const validOperators = ['29', '33', '44', '25'];
        if (!validOperators.includes(operatorCode)) return false;

        return true;
    }

    function validateForm() {
        let isValid = true;
        const form = document.getElementById('profileForm');
        
        // Валидация имени
        const nameInput = form.querySelector('input[name="login"]');
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