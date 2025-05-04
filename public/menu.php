<!DOCTYPE html>
<html lang="ru">
<head>
<?php 
session_start(); // Начинаем сессию
require 'db.php';

// Получаем список товаров в корзине пользователя
$cartItems = [];
if (isset($_SESSION['user_id'])) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT product_id FROM cart WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню - Кондитерская "Kriter"</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Функция для установки начального состояния кнопок
        function initializeButtons() {
            const buttons = document.querySelectorAll('button[onclick^="toggleButton"]');
            const cartItems = <?php echo json_encode($cartItems); ?>;
            
            buttons.forEach(button => {
                const productId = button.getAttribute('data-product-id');
                if (cartItems.includes(parseInt(productId))) {
                    button.textContent = "В корзине";
                    button.style.backgroundColor = "#4CAF50";
                }
            });
        }

        // Вызываем функцию при загрузке страницы
        document.addEventListener('DOMContentLoaded', initializeButtons);

        function toggleButton(button, productId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_to_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
            if (button.textContent === "В корзину") {
                        button.textContent = "В корзине";
                        button.style.backgroundColor = "#4CAF50";
            } else {
                button.textContent = "В корзину";
                        button.style.backgroundColor = "#e74c3c";
                    }
                }
            };
            
            xhr.send('product_id=' + productId);
        }

        function validateSearchInput(input) {
            // Удаляем специальные символы
            let value = input.value.replace(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/g, '');
            
            // Заменяем множественные пробелы на один
            value = value.replace(/\s+/g, ' ');
            
            // Удаляем пробел в начале строки
            value = value.replace(/^\s+/, '');
            
            // Ограничиваем длину до 20 символов
            value = value.substring(0, 20);
            
            // Обновляем значение поля
            input.value = value;
        }

        function searchProduct() {
            const searchInput = document.getElementById('searchInput');
            const value = searchInput.value.trim();
            
            if (value.length > 0) {
                window.location.href = `?search=${encodeURIComponent(value)}`;
            }
        }

        function resetFilters() {
            // Очищаем поле поиска
            document.getElementById('searchInput').value = '';
            
            // Перенаправляем на страницу без параметров
            window.location.href = 'menu.php';
        }
    </script>
    <style>
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Центрирование элементов */
            gap: 20px; /* Промежуток между продуктами */
        }
        .product-item {
            flex: 0 1 calc(33.333% - 20px); /* Каждому элементу по 1/3 ширины с учетом отступа */
            box-sizing: border-box; /* Учитываем отступы и границы */
            text-align: center; /* Центрируем текст */
            padding: 15px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .product-item img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .go-to-cart {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .go-to-cart a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .go-to-cart a:hover {
            background-color: #2980b9;
        }
        button {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #c0392b;
        }
        .category {
            font-size: 28px; /* Увеличьте размер шрифта по вашему выбору */
            font-weight: bold;
            border-bottom: 3px solid #ff5733; /* Цвет линии и её толщина */
            padding-bottom: 10px; /* Отступ под заголовком */
            margin-bottom: 15px; /* Отступ ниже заголовка */
        }
        h2 {
            font-size: 36px; /* Увеличьте размер шрифта по вашему выбору */
            font-weight: bold; /* Делаем текст жирным */
            text-align: center; /* Центрируем текст */
            margin-bottom: 20px; /* Отступ снизу */
        }
        .category-navigation {
            text-align: center;
            margin-bottom: 20px;
        }
        .category-button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 5px;
            transition: background-color 0.3s;
        }
        .category-button:hover {
            background-color: #2980b9;
        }
        .search-container {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .search-container input {
            padding: 10px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-container input:focus {
            outline: none;
            border-color: #3498db;
        }
        .search-container input::placeholder {
            color: #999;
        }
        .search-container button {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-container button:hover {
            background-color: #2980b9;
        }
        .reset-button {
            background-color: #e74c3c !important;
        }
        .reset-button:hover {
            background-color: #c0392b !important;
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
      <a href="index.php">
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
          <a class="sidebar-1" href="menu.php">Меню Клиента</a>
          <a class="sidebar-1" href="manage_users.php">Управление Пользователями</a>
          <a class="sidebar-1" href="admin_orders.php">Управление Заказами</a>
      <?php endif; ?>
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
    <h2>Наше меню</h2>

    <!-- Поле поиска -->
    <div class="search-container">
        <input type="text" id="searchInput" 
               placeholder="Поиск по имени товара" 
               maxlength="20"
               oninput="validateSearchInput(this)"
               onkeypress="if(event.key === 'Enter') searchProduct()">
        <button onclick="searchProduct()">Поиск</button>
        <button onclick="resetFilters()" class="reset-button">Сбросить</button>
    </div>

    <!-- Навигация по категориям -->
    <div class="category-navigation">
        <a href="?category_id=1" class="category-button">Торты</a>
        <a href="?category_id=2" class="category-button">Даниш</a>
        <a href="?category_id=3" class="category-button">Эклеры</a>
        <a href="?category_id=4" class="category-button">Слойки</a>
        <a href="?category_id=5" class="category-button">Макароны</a>
        <a href="?category_id=6" class="category-button">Пирожные</a>
    </div>

    <div class="product-grid">
        <?php
        // Получаем меню из базы данных
        $stmt = get_menu_all(); // Предполагается, что это возвращает PDOStatement
        $menu = $stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем все результаты в виде массива

        // Проверяем наличие параметра поиска
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

        // Фильтруем продукты по имени
        if ($searchTerm) {
            $menu = array_filter($menu, function($product) use ($searchTerm) {
                return stripos($product['name'], $searchTerm) !== false;
            });
        }

        // Массив для хранения продуктов по категориям
        $categories = [];

        // Группируем продукты по категориям
        foreach ($menu as $product) {
            $categoryId = $product["category_id"];
            $categories[$categoryId][] = $product;
        }

        // Получаем идентификатор категории из параметров URL
        $selectedCategoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

        // Выводим продукты по категориям
        foreach ($categories as $categoryId => $products):
            // Если выбрана категория и она не совпадает, пропускаем
            if ($selectedCategoryId && $categoryId !== $selectedCategoryId) {
                continue;
            }
        ?>
            <div class="product-category" id="category_<?php echo $categoryId; ?>">
                <h3 class="category"><?php echo get_category_by_id($categoryId)["category_name"]; ?></h3>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <p><img src="<?php echo $product["image_url"]; ?>" alt="Торт"></p>
                            <p><?php echo $product["name"]; ?> - <strong><?php echo $product["price"]; ?> руб.</strong></p>
                            <p><?php echo $product["description"]; ?></p>
                            <button onclick="toggleButton(this, <?= $product['product_id'] ?>)" data-product-id="<?= $product['product_id'] ?>">В корзину</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="go-to-cart">
        <a href="cart.php">Перейти в корзину</a>
    </div>
    
    <button class="scroll-to-top" onclick="scrollToTop()"></button>
</div>

<footer>
    <p>&copy; 2023 Кондитерская "Kriter"</p>
</footer>

<script>
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