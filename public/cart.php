

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="styles.css">
</head>
<?php
session_start();
require 'db.php'; // Подключаем db.php, где создается функция для подключения

// Получаем объект подключения
$pdo = getDbConnection(); // Теперь у нас есть объект $pdo

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    
    echo "<p>Вы не авторизованы. Пожалуйста, выполните вход.</p>";
    echo "<a href='register.php'>Войти</a>";
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
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['price']) ?> руб.</td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= htmlspecialchars($item['price'] * $item['quantity']) ?> руб.</td>
                        <td>
                            <form action="update_cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
                                <button type="submit">Обновить</button>
                            </form>
                            <form action="remove_from_cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Итого: <?= $totalPrice ?> руб.</strong></p>
        <a href="order_form.php" class="btn">Оформить заказ</a>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2023 Кондитерская "Kriter"</p>
</footer>
</body>
</html>
