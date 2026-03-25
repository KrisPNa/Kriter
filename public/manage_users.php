<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка, авторизован ли администратор
if (!isset($_SESSION['admin_user'])) {
    header('Location: login.php'); // Перенаправление на страницу авторизации
    exit();
}

$db = getDbConnection();

// Удаление пользователя из базы данных
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM users WHERE id_user = :id");
    $stmt->execute([':id' => $userId]);
    header('Location: manage_users.php'); // Перенаправление после удаления
    exit();
}

// Обновление данных пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $updateId = (int)$_POST['update_id'];
    $newName = $_POST['name_user'] ?? '';
    $newLogin = $_POST['login'] ?? '';
    $newEmail = $_POST['email'] ?? '';
    $newPhone = $_POST['phone'] ?? '';
    

    $stmt = $db->prepare("UPDATE users SET name_user = ?, login = ?, email = ?, phone = ? WHERE id_user = ?");
    $stmt->execute([$newName, $newLogin, $newEmail, $newPhone, $updateId]);

    header('Location: manage_users.php'); // Перенаправление после обновления
    exit();
}

// Получение всех пользователей из базы данных
$stmt = $db->query("SELECT id_user, name_user, login, email, phone FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .delete-button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: darkred;
        }
        .edit-button {
            background-color: orange;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .edit-button:hover {
            background-color: darkorange;
        }
        .save-button {
            background-color: green;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .save-button:hover {
            background-color: darkgreen;
        }
        .read-mode span {
            display: inline;
        }
        .read-mode input {
            display: none;
        }
        .edit-mode span {
            display: none;
        }
        .edit-mode input {
            display: inline;
        }
        .edit-mode .edit-button {
            display: none;
        }
        .edit-mode .save-button {
            display: inline;
        }
        .edit-mode .delete-button {
            display: none;
        }
    </style>
    <script>
        function enableEditMode(rowId) {
            const row = document.getElementById(rowId);
            row.classList.add('edit-mode');
            row.classList.remove('read-mode');

            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => input.disabled = false);

            const saveButton = row.querySelector('.save-button');
            saveButton.style.display = 'inline';
        }
    </script>
</head>
<body>
<header>
    <div class="logo">
        <a href="index_admin.php">
            <img src="img/logo.png" alt="логотип" width="140" height="140" />
        </a>
    </div>
    <div class="sidebar">
        <a class="sidebar-1" href="admin_dashboard.php">Админ Меню</a>
        <a class="sidebar-1" href="menu_admin.php">Меню Клиента</a>
        <a class="sidebar-1" href="manage_users.php">Управление Пользователями</a>
        <a class="sidebar-1" href="admin_orders.php">Управление Заказами</a>
    </div>
    <div class="nav">
        <?php if (isset($_SESSION['admin_user'])): ?>
            <a href="admin_account.php">Личный кабинет</a>
        <?php else: ?>
            <a href="register.php">Авторизация</a>
        <?php endif; ?>
    </div>
</header>

<div class="content">
    <h2>Управление пользователями</h2>

    <table>
        <thead>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Имя</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr id="user-row-<?= $user['id_user'] ?>" class="read-mode">
                    <form action="manage_users.php" method="post">
                        <td>
                            <a href="?delete=<?= $user['id_user'] ?>" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                <button type="button" class="delete-button">✖</button>
                            </a>
                        </td>
                        <td><span><?= htmlspecialchars($user['id_user']) ?></span></td>
                        <td>
                            <span><?= htmlspecialchars($user['name_user']) ?></span>
                            <input type="text" name="name_user" value="<?= htmlspecialchars($user['name_user']) ?>" disabled required>
                        </td>
                        <td>
                            <span><?= htmlspecialchars($user['login']) ?></span>
                            <input type="text" name="login" value="<?= htmlspecialchars($user['login']) ?>" disabled required>
                        </td>
                        <td>
                            <span><?= htmlspecialchars($user['email']) ?></span>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled required>
                        </td>
                        <td>
                            <span><?= htmlspecialchars($user['phone'] ?? '') ?></span>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" disabled required>
                        </td>
                        <td>
                            <input type="hidden" name="update_id" value="<?= $user['id_user'] ?>">
                            <button type="button" class="edit-button" onclick="enableEditMode('user-row-<?= $user['id_user'] ?>')">Изменить</button>
                            <button type="submit" class="save-button" style="display: none;">Готово</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>&copy; 2024 Кондитерская "Kriter"</p>
</footer>
</body>
</html>
