<?php 
session_start(); // Начинаем сессию
require 'db.php';

// Получаем спецпредложения из базы данных
$pdo = getDbConnection();
$stmt = $pdo->query('
    SELECT m.product_id, m.name, m.description, m.price, m.image_url
    FROM menu m
    JOIN special_offers s ON m.product_id = s.product_id
    ORDER BY s.position
');
$specialOffers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем самый популярный продукт
$popularProduct = $pdo->query('
    SELECT m.product_id, m.name, m.image_url, m.description, COUNT(od.product_id) as order_count
    FROM menu m
    JOIN order_details od ON m.product_id = od.product_id
    GROUP BY m.product_id
    ORDER BY order_count DESC
    LIMIT 1
')->fetch(PDO::FETCH_ASSOC);

// Отладочная информация
error_log("Special offers: " . print_r($specialOffers, true));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Кондитерская "Kriter"</title>
  <link rel="stylesheet" href="styles.css">
</head>
<style>
  .menu-item {
            flex: 0 1 calc(33.333% - 25px); /* Каждому элементу по 1/3 ширины с учетом отступа */
            box-sizing: border-box; /* Учитываем отступы и границы */
            text-align: center; /* Центрируем текст */
        }
        .menu-item img {
            max-width: 100%; /* Адаптивное изображение */
            height: auto; /* Сохраняем пропорции */
            border-radius: 15px;
        }
        .popular-product {
            margin: 20px 0;
            padding: 20px;
            background: #f9f5ec;
            border-radius: 10px;
            text-align: center;
        }
        .order-count {
            margin-top: 5px;
            font-size: 0.8em;
            color: #666;
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
    <div class="main-content">
      
      <?php if ($popularProduct): ?>
        <div class="popular-product">
          <h2>Самый популярный продукт</h2>
          <div class="menu-item">
            <a href="menu.php">
              <img src="<?= htmlspecialchars($popularProduct['image_url']) ?>" alt="<?= htmlspecialchars($popularProduct['name']) ?>" width="300" height="200">
              <h3><?= htmlspecialchars($popularProduct['name']) ?></h3>
            </a>
            <p><?= htmlspecialchars($popularProduct['description']) ?></p>
            <p class="order-count">Популярный выбор наших клиентов</p>
          </div>
        </div>
      <?php endif; ?>

      <h2>Специальные предложения</h2>
      <div class="menu-items">
        <?php if (empty($specialOffers)): ?>
            <p>Специальные предложения отсутствуют</p>
        <?php else: ?>
            <?php foreach ($specialOffers as $offer): ?>
              <div class="menu-item">
                <a href="menu.php"> 
                  <img src="<?= htmlspecialchars($offer['image_url']) ?>" alt="<?= htmlspecialchars($offer['name']) ?>" width="300" height="200">
                  <h3><?= htmlspecialchars($offer['name']) ?></h3>
                </a>
                <p><?= htmlspecialchars($offer['description']) ?></p>
                <div class="price"><?= number_format($offer['price'], 2) ?> руб.</div>
              </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <footer>
    <p>&copy; 2024 Кондитерская "Kriter"</p>
  </footer>
</body>
</html>