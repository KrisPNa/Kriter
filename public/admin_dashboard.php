<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка, авторизован ли администратор
if (!isset($_SESSION['admin_user'])) {
    header('Location: login.php'); // Перенаправление на страницу авторизации
    exit();
}

$db = getDbConnection();

// Обработка удаления товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $productId = (int)$_POST['product_id'];

    // Удаление товара из базы данных
    $stmt = $db->prepare("DELETE FROM menu WHERE product_id = ?");
    $stmt->execute([$productId]);

    // Перенаправление после удаления
    header('Location: admin_dashboard.php'); 
    exit();
}

// Определение параметров сортировки
$orderBy = 'm.product_id'; // По умолчанию сортировка по ID товара
$orderDir = 'ASC'; // По умолчанию сортировка по возрастанию

if (isset($_GET['sort_by']) && isset($_GET['order'])) {
    $orderBy = match($_GET['sort_by']) {
        'price' => 'm.price',
        'quantity' => 'm.quantity',
        'category' => 'c.category_name',
        default => 'm.product_id'
    };
    $orderDir = $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
}

// Обновление данных товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $productId = (int)$_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];

    try {
        // Обновление товара в базе данных
        $stmt = $db->prepare("UPDATE menu SET name = ?, description = ?, price = ?, quantity = ? WHERE product_id = ?");
        $result = $stmt->execute([$name, $description, $price, $quantity, $productId]);
        
        if ($result) {
            // Успешное обновление
            header('Location: admin_dashboard.php?success=1');
        } else {
            // Ошибка обновления
            header('Location: admin_dashboard.php?error=1');
        }
        exit();
    } catch (PDOException $e) {
        // Ошибка базы данных
        header('Location: admin_dashboard.php?error=2');
        exit();
    }
}

// Получение данных о товарах и их категориях с учетом сортировки
$stmt = $db->prepare("
    SELECT m.product_id, m.name, m.description, m.price, m.quantity, m.image_url, c.category_name, m.category_id
    FROM menu m
    LEFT JOIN categories c ON m.category_id = c.category_id
    ORDER BY $orderBy $orderDir
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Группировка товаров по категориям
$groupedProducts = [];
foreach ($products as $product) {
    $categoryName = $product['category_name'];
    if (!isset($groupedProducts[$categoryName])) {
        $groupedProducts[$categoryName] = [];
    }
    $groupedProducts[$categoryName][] = $product;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ Панель - Управление Товарами</title>
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
        img {
            max-width: 100px;
            height: auto;
        }
        .sort-buttons {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .sort-buttons span {
            margin-right: 10px;
            font-weight: bold;
        }
        .sort-buttons a {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .sort-buttons a:hover {
            background-color: #0056b3;
        }
        .sort-buttons a.active {
            background-color: #0056b3;
            font-weight: bold;
        }
        .reset-button {
            background-color: #dc3545 !important;
            margin-left: auto;
        }
        .reset-button:hover {
            background-color: #c82333 !important;
        }
        .edit-button, .save-button, .delete-button {
            padding: 5px 10px;
            cursor: pointer;
            color: white;
            border: none;
            border-radius: 4px;
        }
        .edit-button { background-color: orange; }
        .save-button { background-color: green; }
        .delete-button { background-color: red; }
        .hidden { display: none; }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 5px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .add-product-button {
    padding: 10px 15px;
    background-color: #28a745; /* Green color */
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-bottom: 20px;
    display: inline-block;
}

.add-product-button:hover {
    background-color: #218838; /* Darker green on hover */
}
        .hidden {
            display: none;
        }
        .edit-button, .save-button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
        }
        .edit-button {
            background-color: #4CAF50;
            color: white;
        }
        .save-button {
            background-color: #2196F3;
            color: white;
        }
        .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
        }
        input:disabled, textarea:disabled {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
            display: none;
            transition: background-color 0.3s;
            z-index: 1000;
        }
        .scroll-to-top:hover {
            background-color: #45a049;
        }
        .scroll-to-top::before {
            content: "↑";
            font-size: 24px;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="img/logo.png" alt="логотип" width="140" height="140" /></a>
    </div>
    <div class="sidebar">
        <a class="sidebar-1" href="admin_dashboard.php">Админ Меню</a>
        <a class="sidebar-1" href="menu.php">Меню Клиента</a>
        <a class="sidebar-1" href="manage_users.php">Управление Пользователями</a>
        <a class="sidebar-1" href="admin_orders.php">Управление Заказами</a>
    </div>
    <div class="nav">
        <?php if (isset($_SESSION['admin_user'])): ?>
            <a href="account.php">Личный кабинет</a>
        <?php else: ?>
            <a href="register.php">Авторизация</a>
        <?php endif; ?>
    </div>
</header>

<div class="content">
    <?php if (isset($_GET['success'])): ?>
        <div class="success-message" style="color: green; margin-bottom: 20px;">
            Товар успешно обновлен!
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message" style="color: red; margin-bottom: 20px;">
            <?php
            switch ($_GET['error']) {
                case '1':
                    echo 'Ошибка при обновлении товара.';
                    break;
                case '2':
                    echo 'Ошибка базы данных.';
                    break;
                default:
                    echo 'Произошла ошибка.';
            }
            ?>
        </div>
    <?php endif; ?>
    <h2>Список Товаров</h2>
    <a href="add_product.php" class="add-product-button">Добавить Товар</a>
    <div class="sort-buttons">
        <span>Сортировать по:</span>
        <a href="?sort_by=category&order=asc" <?= ($orderBy === 'c.category_name' && $orderDir === 'ASC') ? 'class="active"' : '' ?>>Категории (A-Z)</a>
        <a href="?sort_by=category&order=desc" <?= ($orderBy === 'c.category_name' && $orderDir === 'DESC') ? 'class="active"' : '' ?>>Категории (Z-A)</a>
        <a href="?sort_by=price&order=asc" <?= ($orderBy === 'm.price' && $orderDir === 'ASC') ? 'class="active"' : '' ?>>Цене (от меньшего)</a>
        <a href="?sort_by=price&order=desc" <?= ($orderBy === 'm.price' && $orderDir === 'DESC') ? 'class="active"' : '' ?>>Цене (от большего)</a>
        <a href="?sort_by=quantity&order=asc" <?= ($orderBy === 'm.quantity' && $orderDir === 'ASC') ? 'class="active"' : '' ?>>Количество (от меньшего)</a>
        <a href="?sort_by=quantity&order=desc" <?= ($orderBy === 'm.quantity' && $orderDir === 'DESC') ? 'class="active"' : '' ?>>Количество (от большего)</a>
        <a href="admin_dashboard.php" class="reset-button">Сбросить</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th></th>
                <th>ID товара</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Изображение</th>
                <th>Категория</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groupedProducts as $categoryName => $categoryProducts): ?>
                <tr class="category-header">
                    <td colspan="9" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                        <?= htmlspecialchars($categoryName) ?>
                    </td>
                </tr>
                <?php foreach ($categoryProducts as $product): ?>
                    <tr>
                        <form action="admin_dashboard.php" method="post">
                            <td>
                                <button type="submit" name="delete_product" class="delete-button" onclick="return confirm('Вы уверены, что хотите удалить этот товар?');">✖</button>
                            </td>
                            <td>
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                                <?= htmlspecialchars($product['product_id']) ?>
                            </td>
                            <td>
                                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required disabled>
                            </td>
                            <td>
                                <textarea name="description" required disabled><?= htmlspecialchars($product['description']) ?></textarea>
                            </td>
                            <td>
                                <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" required disabled>
                            </td>
                            <td>
                                <input type="number" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required disabled>
                            </td>
                            <td>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width: 100px; height: auto;">
                            </td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td>
                                <button type="button" class="edit-button" onclick="toggleEdit(this)">Изменить</button>
                                <button type="submit" name="update_product" class="save-button hidden">Готово</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<button class="scroll-to-top" onclick="scrollToTop()"></button>

<footer>
    <p>&copy; 2024 Кондитерская "Kriter"</p>
</footer>

<script>
    function toggleEdit(button) {
        const row = button.closest('tr');
        const inputs = row.querySelectorAll('input[type="text"], input[type="number"], textarea');
        const saveButton = row.querySelector('.save-button');
        const editButton = row.querySelector('.edit-button');
        
        inputs.forEach(input => {
            input.disabled = !input.disabled;
        });
        
        saveButton.classList.toggle('hidden');
        editButton.classList.toggle('hidden');
    }

    // Показываем/скрываем кнопку при прокрутке
    window.onscroll = function() {
        const scrollButton = document.querySelector('.scroll-to-top');
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            scrollButton.style.display = "block";
        } else {
            scrollButton.style.display = "none";
        }
    };

    // Функция плавной прокрутки вверх
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
</script>

</body>
</html>