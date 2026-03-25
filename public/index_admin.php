<?php 
session_start(); // Начинаем сессию
require 'db.php';

// Проверка авторизации администратора
if (!isset($_SESSION['admin_user'])) {
    header('Location: login.php');
    exit();
}

$pdo = getDbConnection();

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_offers'])) {
    try {
        // Отладочная информация
        error_log("POST data: " . print_r($_POST, true));
        
        // Удаляем старые спецпредложения
        $pdo->query('DELETE FROM special_offers');
        
        // Добавляем новые
        $stmt = $pdo->prepare('INSERT INTO special_offers (product_id, position) VALUES (?, ?)');
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_POST["product_$i"]) && is_numeric($_POST["product_$i"])) {
                $product_id = (int)$_POST["product_$i"];
                $stmt->execute([$product_id, $i]);
                error_log("Added special offer: product_id=$product_id, position=$i");
            }
        }
        
        $_SESSION['success'] = 'Специальные предложения успешно обновлены';
    } catch (PDOException $e) {
        error_log("Error updating special offers: " . $e->getMessage());
        $_SESSION['error'] = 'Ошибка при обновлении предложений: ' . $e->getMessage();
    }
    header('Location: index_admin.php');
    exit();
}

// Получаем все товары для выпадающего списка
$products = $pdo->query('SELECT product_id, name FROM menu ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
error_log("Available products: " . print_r($products, true));

// Получаем текущие спецпредложения
$currentOffers = $pdo->query('
    SELECT m.product_id, m.name, m.image_url, m.description, s.position 
    FROM menu m
    JOIN special_offers s ON m.product_id = s.product_id
    ORDER BY s.position
')->fetchAll(PDO::FETCH_ASSOC);
error_log("Current special offers: " . print_r($currentOffers, true));

// Получаем самый популярный продукт
$popularProduct = $pdo->query('
    SELECT m.product_id, m.name, m.image_url, m.description, COUNT(od.product_id) as order_count
    FROM menu m
    JOIN order_details od ON m.product_id = od.product_id
    GROUP BY m.product_id
    ORDER BY order_count DESC
    LIMIT 1
')->fetch(PDO::FETCH_ASSOC);

// Создаем массив для удобного доступа к текущим предложениям
$currentOffersMap = [];
foreach ($currentOffers as $offer) {
    $currentOffersMap[$offer['position']] = $offer['product_id'];
}
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
        .special-offers-form {
            margin: 20px 0;
            padding: 20px;
            background: #f9f5ec;
            border-radius: 10px;
        }
        .offer-select {
            margin: 10px 0;
        }
        .offer-select select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .update-button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .update-button:hover {
            background: #45a049;
        }
        .success-message {
            color: #4CAF50;
            margin: 10px 0;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 5px;
        }
        .error-message {
            color: #f44336;
            margin: 10px 0;
            padding: 10px;
            background: #ffebee;
            border-radius: 5px;
        }
        .popular-product {
            margin-bottom: 20px;
            padding: 20px;
            background: #f9f5ec;
            border-radius: 10px;
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
      <a href="index_admin.php">
        <img src="img/logo.png" alt="логотип" width="140" height="140" />
      </a>
    </div>
    <div class="sidebar">
      <!-- Меню клиента -->
      <?php if (!isset($_SESSION['admin_user'])): ?>
          <a class="sidebar-1" href="about.php">О нас</a>
          <a class="sidebar-1" href="menu.php">Меню</a>
          <a class="sidebar-1" href="cart.php">Корзина</a>
          <a class="sidebar-1" href="cont.php">Контакты</a>
          <a class="sidebar-1" href="order.php">Заказы</a>
      <?php endif; ?>
      
      <!-- Меню администратора -->
      <?php if (isset($_SESSION['admin_user'])): ?>
          <a class="sidebar-1" href="admin_dashboard.php">Админ Меню</a>
          <a class="sidebar-1" href="menu_admin.php">Меню Клиента</a>
          <a class="sidebar-1" href="manage_users.php">Управление Пользователями</a>
          <a class="sidebar-1" href="admin_orders.php">Управление Заказами</a>
      <?php endif; ?>
    </div>
    <div class="nav">
    <?php if (isset($_SESSION['user']) || isset($_SESSION['admin_user'])): ?>
    <a href="admin_account.php">Личный кабинет</a>
<?php else: ?>
    <a href="register.php">Авторизация</a>
<?php endif; ?>

    </div>
  </header>

  <div class="content">
    <div class="main-content">
      
      <h2>Управление специальными предложениями</h2>
      
      <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <?php if ($popularProduct): ?>
        <div class="popular-product">
          <h3>Самый популярный продукт</h3>
          <div class="menu-item">
            <img src="<?= htmlspecialchars($popularProduct['image_url']) ?>" alt="<?= htmlspecialchars($popularProduct['name']) ?>" width="300" height="200">
            <h3><?= htmlspecialchars($popularProduct['name']) ?></h3>
            <p><?= htmlspecialchars($popularProduct['description']) ?></p>
            <p class="order-count">Количество заказов: <?= $popularProduct['order_count'] ?></p>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" class="special-offers-form">
        <input type="hidden" name="update_offers" value="1">
        
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <div class="offer-select">
            <label for="product_<?= $i ?>">Позиция <?= $i ?>:</label>
            <select name="product_<?= $i ?>" id="product_<?= $i ?>" required>
              <option value="">Выберите товар</option>
              <?php foreach ($products as $product): ?>
                <option value="<?= $product['product_id'] ?>" 
                        <?= isset($currentOffersMap[$i]) && $currentOffersMap[$i] == $product['product_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($product['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endfor; ?>

        <button type="submit" class="update-button">Обновить предложения</button>
      </form>

      <h3>Текущие специальные предложения</h3>
      <div class="menu-items">
        <?php foreach ($currentOffers as $offer): ?>
          <div class="menu-item">
            <img src="<?= htmlspecialchars($offer['image_url']) ?>" alt="<?= htmlspecialchars($offer['name']) ?>" width="300" height="200">
            <h3><?= htmlspecialchars($offer['name']) ?></h3>
            <p><?= htmlspecialchars($offer['description']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  
  <footer>
    <p>&copy; 2024 Кондитерская "Kriter"</p>
  </footer>
</body>
</html>