<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка, авторизован ли администратор
if (!isset($_SESSION['admin_user'])) {
    header('Location: login.php'); // Перенаправление на страницу авторизации
    exit();
}

$db = getDbConnection();

// Получение заказа в текстовый файл
if (isset($_GET['get'])) {
    $orderNumber = (int)$_GET['get'];

    // Получение данных по конкретному заказу
    $stmt = $db->prepare("SELECT * FROM orders WHERE number_order = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT * FROM order_details WHERE number_order = ?");
    $stmt->execute([$orderNumber]);
    $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Проверка, существует ли заказ
    if ($order) {
        // Определяем имя файла
        $filename = 'order_' . $order['number_order'] . '.txt';

        // Открываем файл для записи
        $file = fopen($filename, 'w');

        if ($file) {
            // Записываем данные о заказе
            fwrite($file, "ID заказа: {$order['number_order']}\n");
            fwrite($file, "Имя: {$order['user_name']}\n");
            fwrite($file, "Адрес: {$order['addres_street']} {$order['addres_house']} {$order['addres_apt']}\n");
            fwrite($file, "Дата доставки: {$order['delivery_date']}\n");
            fwrite($file, "Время доставки: {$order['delivery_time']}\n");
            fwrite($file, "Статус: {$order['status']}\n");
            fwrite($file, "Общая стоимость: {$order['total_price']} руб.\n");
            fwrite($file, "Детали заказа:\n");

            foreach ($orderDetails as $detail) {
                fwrite($file, " - {$detail['product_name']} - {$detail['quantity']} шт.\n");
            }

            fclose($file); // Закрываем файл
            echo "Заказ успешно сохранен в файл $filename."; // Уведомление об успешном сохранении
        } else {
            echo "Не удалось открыть файл для записи.";
        }
    } else {
        echo "Заказ не найден.";
    }

    exit(); // Останавливаем выполнение скрипта после обработки
}

// Удаление заказа и его деталей
if (isset($_GET['delete'])) {
    $orderNumber = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM orders WHERE number_order = ?");
    $stmt->execute([$orderNumber]);

    $stmt = $db->prepare("DELETE FROM order_details WHERE number_order = ?");
    $stmt->execute([$orderNumber]);

    header('Location: admin_orders.php'); // Перенаправление после удаления
    exit();
}

// Обновление данных заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNumber = (int)$_POST['number_order'];

    if (isset($_POST['update_order'])) {
        $newStatus = $_POST['status'];
        $newStreet = $_POST['street'];
        $newHouse = $_POST['house'];
        $newApt = $_POST['apt'];
        $newTotalPrice = $_POST['total_price'];
        
        // Получаем дату и время отдельно
        $newDeliveryDate = $_POST['delivery_date'];
        $newDeliveryTime = $_POST['delivery_time'];

        // Обновление основного заказа
        $stmt = $db->prepare("UPDATE orders SET status = ?, addres_street = ?, addres_house = ?, addres_apt = ?, delivery_date = ?, delivery_time = ?, total_price = ? WHERE number_order = ?");
        $stmt->execute([$newStatus, $newStreet, $newHouse, $newApt, $newDeliveryDate, $newDeliveryTime, $newTotalPrice, $orderNumber]);

        // Обновление количества товаров
        if (isset($_POST['product_id'])) {
            foreach ($_POST['product_id'] as $index => $productId) {
                $quantity = (int)$_POST['quantity'][$index];
                $stmt = $db->prepare("UPDATE order_details SET quantity = ? WHERE number_order = ? AND product_id = ?");
                $stmt->execute([$quantity, $orderNumber, $productId]);
            }
        }
    }

    header('Location: admin_orders.php');
    exit();
}

// Получение данных заказов и деталей
$stmt = $db->prepare(
    "SELECT o.number_order, o.user_id, o.user_name, 
     o.addres_street, o.addres_house, o.addres_apt,
     o.delivery_date, o.delivery_time, 
     o.status, o.total_price,  
     od.product_id, od.product_name, od.quantity AS order_quantity,
     od.price
     FROM orders o
     LEFT JOIN order_details od ON o.number_order = od.number_order
     ORDER BY o.number_order"
);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами</title>
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
        .edit-button, .save-button, .delete-button, .get-button {
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            color: white;
        }
        .edit-button { background-color: orange; }
        .save-button { background-color: green; }
        .delete-button { background-color: red; }
        .get-button { background-color: blue; }
        .hidden { display: none; }
    </style>
    <script>
        function toggleEdit(orderNumber) {
            const orderRow = document.getElementById(`order-row-${orderNumber}`);
            const editButton = document.getElementById(`edit-button-${orderNumber}`);
            const saveButton = document.getElementById(`save-button-${orderNumber}`);
            const inputs = orderRow.querySelectorAll('input[type="text"], input[type="date"], input[type="time"], select, input[type="number"]');

            inputs.forEach(input => {
                input.disabled = !input.disabled;
            });

            editButton.classList.toggle('hidden');
            saveButton.classList.toggle('hidden');
        }

        // Функция для пересчета общей стоимости
        function updateTotalPrice(orderNumber) {
            const orderRow = document.getElementById(`order-row-${orderNumber}`);
            const quantityInputs = orderRow.querySelectorAll('input[name="quantity[]"]');
            const priceInputs = orderRow.querySelectorAll('input[name="price[]"]');
            let totalPrice = 0;

            quantityInputs.forEach((input, index) => {
                const quantity = parseInt(input.value) || 0;
                const price = parseFloat(priceInputs[index].value) || 0;
                totalPrice += quantity * price;
            });

            // Обновляем отображение общей стоимости
            const totalPriceCell = orderRow.querySelector('.total-price');
            if (totalPriceCell) {
                totalPriceCell.textContent = totalPrice.toFixed(2) + ' руб.';
            }

            // Обновляем скрытое поле с общей стоимостью
            const totalPriceInput = orderRow.querySelector('input[name="total_price"]');
            if (totalPriceInput) {
                totalPriceInput.value = totalPrice.toFixed(2);
            }
        }

        // Добавляем обработчики событий для всех полей количества
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('input[name="quantity[]"]');
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const orderNumber = this.closest('tr').id.split('-')[2];
                    updateTotalPrice(orderNumber);
                });
            });
        });
    </script>
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
    <h2>Управление заказами</h2>

    <table>
        <thead>
            <tr>
                <th></th>
                <th>Номер заказа</th>
                <th>Пользователь</th>
                <th>Адрес</th>
                <th>Дата доставки</th>
                <th>Время доставки</th>
                <th>Статус</th>
                <th>Общая стоимость</th>
                <th>Детали заказа</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $orderNumber => $orderDetails): ?>
                <?php $first = $orderDetails[0]; ?>
                
                <tr id="order-row-<?= htmlspecialchars($orderNumber) ?>">
                    <form action="admin_orders.php" method="post">
                        <td>
                            <a href="?delete=<?= htmlspecialchars($orderNumber) ?>" onclick="return confirm('Вы уверены, что хотите удалить этот заказ?');">
                                <button type="button" class="delete-button">✖</button>
                            </a>
                        </td>
                        <td rowspan="<?= count($orderDetails) ?>"><?= htmlspecialchars($orderNumber) ?></td>
                        <td rowspan="<?= count($orderDetails) ?>">ID: <?= htmlspecialchars($first['user_id']) ?><br>Имя: <?= htmlspecialchars($first['user_name']) ?></td>
                        <td rowspan="<?= count($orderDetails) ?>" class="address-cell">
                            <input type="text" name="street" value="<?= htmlspecialchars($first['addres_street']) ?>" required disabled>
                            <input type="text" name="house" value="<?= htmlspecialchars($first['addres_house']) ?>" required disabled>
                            <input type="text" name="apt" value="<?= htmlspecialchars($first['addres_apt']) ?>" required disabled>
                        </td>
                        <td rowspan="<?= count($orderDetails) ?>" class="delivery-cell">
                            <input type="date" name="delivery_date" value="<?= htmlspecialchars($first['delivery_date']) ?>" required disabled>
                        </td>
                        <td rowspan="<?= count($orderDetails) ?>" class="delivery-cell">
                            <input type="time" name="delivery_time" value="<?= htmlspecialchars($first['delivery_time']) ?>" required disabled>
                        </td>
                        <td rowspan="<?= count($orderDetails) ?>">
                            <select name="status" disabled>
                                <option value="Принят" <?= $first['status'] === 'Принят' ? 'selected' : '' ?>>Принят</option>
                                <option value="Доставлен" <?= $first['status'] === 'Доставлен' ? 'selected' : '' ?>>Доставлен</option>
                            </select>
                            <input type="hidden" name="number_order" value="<?= htmlspecialchars($orderNumber) ?>">
                        </td>
                        <td rowspan="<?= count($orderDetails) ?>" class="total-price"><?= htmlspecialchars($first['total_price']) ?> руб.</td>
                        <input type="hidden" name="total_price" value="<?= htmlspecialchars($first['total_price']) ?>">
                        <td>
                            <?php foreach ($orderDetails as $index => $detail): ?>
                                <div>
                                    <?= htmlspecialchars($detail['product_name']) ?> - 
                                    <input type="number" name="quantity[]" value="<?= htmlspecialchars($detail['order_quantity']) ?>" min="1" required disabled>
                                    <input type="hidden" name="product_id[]" value="<?= htmlspecialchars($detail['product_id']) ?>">
                                    <input type="hidden" name="price[]" value="<?= htmlspecialchars($detail['price']) ?>">
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td rowspan="<?= count($orderDetails) ?>">
                            <button type="button" class="edit-button" id="edit-button-<?= htmlspecialchars($orderNumber) ?>" onclick="toggleEdit(<?= htmlspecialchars($orderNumber) ?>)">Изменить</button>
                            <button type="submit" name="update_order" class="save-button hidden" id="save-button-<?= htmlspecialchars($orderNumber) ?>">Готово</button>
                            <button type="submit" name="get_order" class="get-button" formaction="admin_orders.php?get=<?= htmlspecialchars($orderNumber) ?>">Получить</button>
                        </td>
                    </form>
                </tr>
                
                <?php // Добавляем отдельные строки для деталей заказа ?>
                <?php foreach ($orderDetails as $index => $detail): ?>
                    <?php if ($index > 0): // Пропускаем первую строку, так как она уже создана ?>
                        <tr>
                            <td colspan="6"></td> <!-- Пустая ячейка для выравнивания -->
                            <td><?= htmlspecialchars($detail['product_name']) ?> - <?= htmlspecialchars($detail['order_quantity']) ?> шт.</td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>&copy; 2024 Кондитерская "Kriter"</p>
</footer>
</body>
</html>