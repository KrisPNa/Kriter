<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        .update-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .remove-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .total-price {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<?php
session_start();
require 'db.php'; // Подключаем db.php, где создается функция для подключения

// Получаем объект подключения
$pdo = getDbConnection(); // Теперь у нас есть объект $pdo

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    
    header('Location: register.php'); // Перенаправление на страницу авторизации
    exit();
}

$userId = $_SESSION['user_id']; // ID пользователя из сессии

// Получение данных корзины
$stmt = $pdo->prepare('
    SELECT 
        cart.product_id, 
        cart.quantity, 
        menu.name, 
        menu.price 
    FROM cart 
    JOIN menu ON cart.product_id = menu.product_id 
    WHERE cart.user_id = ?
');
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Подсчет общей суммы
$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}
?>
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
    <h1>Корзина</h1>


<div class="content">
    <?php if (empty($cartItems)): ?>
        <p>Ваша корзина пуста. Вернитесь в <a href="menu.php">меню</a>, чтобы добавить товары.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr data-product-id="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>">
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td class="price"><?= number_format($item['price'], 2) ?> руб.</td>
                        <td>
                            <form action="update_cart.php" method="post" class="update-form">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input
                                    type="number"
                                    name="quantity"
                                    value="<?= $item['quantity'] ?>"
                                    min="1"
                                    max="999"
                                    class="quantity-input"
                                >
                                <button type="submit" class="update-button">Обновить</button>
                            </form>
                        </td>
                        <td class="item-total"><?= number_format($item['price'] * $item['quantity'], 2) ?> руб.</td>
                        <td>
                            <form action="remove_from_cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" class="remove-button">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="total-price">Итого: <span id="total-price"><?= number_format($totalPrice, 2) ?></span> руб.</p>
        <a href="order_form.php" class="btn">Оформить заказ</a>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function clampQuantity(input) {
        if (input.value === '') return;
        let value = parseInt(input.value, 10);
        if (isNaN(value) || value < 1) value = 1;
        if (value > 999) value = 999;
        input.value = value;
    }

    function updateRowTotal(row) {
        const price = parseFloat(row.dataset.price);
        const quantityInput = row.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput.value, 10) || 0;
        const itemTotalCell = row.querySelector('.item-total');
        const total = price * quantity;
        itemTotalCell.textContent = total.toFixed(2) + ' руб.';
    }

    function updateTotalPrice() {
        let total = 0;
        document.querySelectorAll('tr[data-product-id]').forEach(function (row) {
            const price = parseFloat(row.dataset.price);
            const quantityInput = row.querySelector('.quantity-input');
            const quantity = parseInt(quantityInput.value, 10) || 0;
            total += price * quantity;
        });
        const totalSpan = document.getElementById('total-price');
        if (totalSpan) {
            totalSpan.textContent = total.toFixed(2);
        }
    }

    document.querySelectorAll('.update-form').forEach(function (form) {
        const quantityInput = form.querySelector('.quantity-input');
        const row = form.closest('tr');

        if (!quantityInput || !row) return;

        quantityInput.addEventListener('change', function () {
            clampQuantity(quantityInput);
            updateRowTotal(row);
            updateTotalPrice();
        });

        form.addEventListener('submit', function (event) {
            if (!window.fetch) {
                return; // стандартная отправка формы (PHP всё обработает)
            }

            event.preventDefault();
            clampQuantity(quantityInput);
            updateRowTotal(row);
            updateTotalPrice();

            const formData = new FormData(form);

            fetch('update_cart.php', {
                method: 'POST',
                body: formData
            }).catch(function (error) {
                console.error('Ошибка обновления корзины:', error);
                form.submit(); // на всякий случай, если fetch не удался
            });
        });
    });
});
</script>

<footer>
    <p>&copy; 2023 Кондитерская "Kriter"</p>
</footer>
</body>
</html>
