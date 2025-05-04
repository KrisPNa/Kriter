<?php
session_start();
require 'db.php'; // Подключение к базе данных

if (!isset($_SESSION['user_id'])) {
    header('Location: register.php'); // Перенаправление на страницу авторизации
    exit();
}

$userId = $_SESSION['user_id']; // ID пользователя из сессии

$pdo = getDbConnection(); // Получаем соединение с базой данных

// Обработка обновления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $orderNumber = $_POST['number_order'];
    
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    try {
        // Обновляем количество товаров в заказе
        if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
            foreach ($_POST['product_id'] as $index => $productId) {
                $newQuantity = (int)$_POST['quantity'][$index];
                
                // Получаем текущее количество товара в заказе
                $stmt = $pdo->prepare('
                    SELECT quantity 
                    FROM order_details 
                    WHERE number_order = ? AND product_id = ?
                ');
                $stmt->execute([$orderNumber, $productId]);
                $orderItem = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($orderItem) {
                    $oldQuantity = $orderItem['quantity'];
                    
                    // Получаем цену товара из меню
                    $stmt = $pdo->prepare('
                        SELECT price 
                        FROM menu 
                        WHERE product_id = ?
                    ');
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($product) {
                        $pricePerUnit = $product['price'];
                        
                        // Обновляем количество в order_details, сохраняя цену за единицу
                        $stmt = $pdo->prepare('
                            UPDATE order_details 
                            SET quantity = ?, 
                                price = ? 
                            WHERE number_order = ? AND product_id = ?
                        ');
                        $stmt->execute([$newQuantity, $pricePerUnit, $orderNumber, $productId]);
                        
                        // Синхронизируем с таблицей menu
                        if ($newQuantity > $oldQuantity) {
                            $stmt = $pdo->prepare('
                                UPDATE menu 
                                SET quantity = quantity - ? 
                                WHERE product_id = ?
                            ');
                            $stmt->execute([$newQuantity - $oldQuantity, $productId]);
                        } elseif ($newQuantity < $oldQuantity) {
                            $stmt = $pdo->prepare('
                                UPDATE menu 
                                SET quantity = quantity + ? 
                                WHERE product_id = ?
                            ');
                            $stmt->execute([$oldQuantity - $newQuantity, $productId]);
                        }
                    }
                }
            }
        }
        
        // Обновляем адрес и дату доставки
        if (isset($_POST['street']) && isset($_POST['house']) && isset($_POST['apt']) && 
            isset($_POST['delivery_date']) && isset($_POST['delivery_time'])) {
            
            $stmt = $pdo->prepare('
                UPDATE orders 
                SET addres_street = ?, 
                    addres_house = ?, 
                    addres_apt = ?, 
                    delivery_date = ?, 
                    delivery_time = ? 
                WHERE number_order = ? AND user_id = ?
            ');
            $stmt->execute([
                $_POST['street'],
                $_POST['house'],
                $_POST['apt'],
                $_POST['delivery_date'],
                $_POST['delivery_time'],
                $orderNumber,
                $userId
            ]);
        }
        
        // После всех обновлений, пересчитываем общую стоимость заказа
        $stmt = $pdo->prepare('
            SELECT SUM(price * quantity) as total 
            FROM order_details 
            WHERE number_order = ?
        ');
        $stmt->execute([$orderNumber]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Обновляем общую стоимость в таблице orders
        $stmt = $pdo->prepare('
            UPDATE orders 
            SET total_price = ? 
            WHERE number_order = ?
        ');
        $stmt->execute([$total['total'], $orderNumber]);
        
        // Подтверждаем транзакцию
        $pdo->commit();
        
    } catch (Exception $e) {
        // В случае ошибки откатываем транзакцию
        $pdo->rollBack();
        throw $e;
    }
    
    header('Location: order.php');
    exit();
}

// Получаем все заказы пользователя, включая поле number_order
$stmt = $pdo->prepare('
    SELECT number_order, delivery_date, delivery_time, addres_street, addres_house, addres_apt, status
    FROM orders 
    WHERE user_id = ? 
    ORDER BY 
        CASE 
            WHEN status = "Принят" THEN 1
            ELSE 2
        END,
        delivery_date ASC,
        delivery_time ASC
');
$stmt->execute([$userId]);
$orderInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ваши заказы</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .order-form {
            margin-bottom: 20px;
        }
        .order-details input {
            margin: 5px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .order-details input:disabled {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .edit-button, .save-button {
            padding: 8px 16px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .edit-button {
            background-color: #4CAF50;
            color: white;
        }
        .save-button {
            background-color: #2196F3;
            color: white;
        }
        .hidden {
            display: none;
        }
        .order-details p {
            margin: 10px 0;
        }
    </style>
    <script>
        function toggleEdit(orderNumber) {
            const form = document.querySelector(`form[data-order="${orderNumber}"]`);
            const inputs = form.querySelectorAll('input[type="text"], input[type="date"], input[type="time"], input[type="number"]');
            const editButton = form.querySelector('.edit-button');
            const saveButton = form.querySelector('.save-button');
            
            inputs.forEach(input => {
                input.disabled = !input.disabled;
            });
            
            editButton.classList.toggle('hidden');
            saveButton.classList.toggle('hidden');
        }
    </script>
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
            <?php if (isset($_SESSION['user'])): ?>
                <a href="account.php">Личный кабинет</a>
            <?php else: ?>
                <a href="register.php">Авторизация</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
    <?php if (!empty($orderInfo)): ?>
        <h2>Ваши заказы</h2>
        <?php foreach ($orderInfo as $order): ?>
            <div class="order">
            <p><strong>Номер заказа:</strong> <?= htmlspecialchars($order['number_order']) ?></p>
                <h3>Товары в заказе:</h3>
                <?php
                // Проверяем, есть ли номер заказа в массиве $order
                if (isset($order['number_order'])) {
                    $stmt = $pdo->prepare('
                        SELECT od.product_id, od.product_name, od.quantity, od.price 
                        FROM order_details od 
                        WHERE od.number_order = ?
                    ');
                    $stmt->execute([$order['number_order']]);
                    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $totalPrice = 0; // Общая стоимость для этого заказа

                    if ($orderItems): ?>
                        <form method="POST" action="order.php" class="order-form" data-order="<?= htmlspecialchars($order['number_order']) ?>">
                            <input type="hidden" name="number_order" value="<?= htmlspecialchars($order['number_order']) ?>">
                        <ul>
                            <?php foreach ($orderItems as $item):
                                $itemTotal = $item['price'] * $item['quantity'];
                                $totalPrice += $itemTotal;
                            ?>
                                <li>
                                        <?= htmlspecialchars($item['product_name']) ?> - 
                                        <input type="number" name="quantity[]" value="<?= $item['quantity'] ?>" min="1" disabled>
                                        <input type="hidden" name="product_id[]" value="<?= $item['product_id'] ?>">
                                    <br>
                                        Цена за единицу: <?= number_format($item['price'], 2) ?> BUN
                                    <br>
                                    Общая стоимость: <?= number_format($itemTotal, 2) ?> BUN
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <h4>Общая стоимость: <?= number_format($totalPrice, 2) ?> BUN</h4>
                            
                            <div class="order-details">
                                <p><strong>Дата доставки:</strong> 
                                    <input type="date" name="delivery_date" value="<?= htmlspecialchars($order['delivery_date']) ?>" disabled>
                                </p>
                                <p><strong>Время доставки:</strong> 
                                    <input type="time" name="delivery_time" value="<?= htmlspecialchars($order['delivery_time']) ?>" disabled>
                                </p>
                                <p><strong>Адрес доставки:</strong> 
                                    <input type="text" name="street" value="<?= htmlspecialchars($order['addres_street']) ?>" disabled>
                                    <input type="text" name="house" value="<?= htmlspecialchars($order['addres_house']) ?>" disabled>
                                    <input type="text" name="apt" value="<?= htmlspecialchars($order['addres_apt']) ?>" disabled>
                                </p>
                                <p><strong>Статус заказа:</strong> <?= htmlspecialchars($order['status'] ?? 'Неизвестно') ?></p>
                            </div>
                            
                            <?php if ($order['status'] !== 'Доставлен'): ?>
                                <button type="button" class="edit-button" onclick="toggleEdit(<?= htmlspecialchars($order['number_order']) ?>)">Изменить</button>
                                <button type="submit" name="update_order" class="save-button hidden" id="save-button-<?= htmlspecialchars($order['number_order']) ?>">Сохранить</button>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <p>Товары не найдены для этого заказа.</p>
                    <?php endif;
                } else {
                    echo "<p>Не удалось найти номер заказа.</p>";
                }
                ?>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>У вас пока нет оформленных заказов.</p>
    <?php endif; ?>
    </main>
</body>

</html>
