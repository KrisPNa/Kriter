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
</style>
<?php 
session_start(); // Начинаем сессию
require 'db.php';
?>
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
    <?php if (isset($_SESSION['user']) || isset($_SESSION['admin_user'])): ?>
    <a href="account.php">Личный кабинет</a>
<?php else: ?>
    <a href="register.php">Авторизация</a>
<?php endif; ?>

    </div>
  </header>

  <div class="content">
    <div class="main-content">
      <h2>Популярные кондитерские изделия</h2>
      <div class="menu-items">
        <tr>
          
          <td>
            <div class="menu-item">
              <a href="menu.php"> 
                <img src="img/ecler.png" alt="Эклер" width="300" height="200">
                <h3>Эклеры</h3>
              </a>
              <p>Классические эклеры с ванильным кремом</p>
            </div>
          </td>
          <td>
            <div class="menu-item">
              <a href="menu.php"> 
                <img src="img/macaroon.png" alt="Макароны" width="300" height="200">
                <h3>Макароны</h3>
              </a>
              <p>Разноцветные макароны с различными начинками</p>
            </div>
          </td>
          <td>
            <div class="menu-item">
              <a href="menu.php"> 
                <img src="img/cheeezcake.png" alt="Чизкейк" width="300" height="200">
                <h3>Торты</h3>
              </a>
              <p>Нежный торт с ягодами</p>
            </div>
          </td>
        </tr>
      </div>
    </div>
  </div>
  
  <footer>
    <p>&copy; 2024 Кондитерская "Kriter"</p>
  </footer>
</body>
</html>