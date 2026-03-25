<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Контакты - Кондитерская "Kriter"</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .container {
        max-width: 1200px; /* Ограничение максимальной ширины */
        margin: 0 auto; /* Центрирование контейнера */
        padding: 20px;
    }

    h1 {
        font-size: 50px; /* Увеличьте размер шрифта по вашему выбору */
        font-weight: bold; /* Делаем текст жирным */
        text-align: center; /* Центрируем текст */
        margin-bottom: 20px; /* Отступ снизу */
    }

    table {
        width: 100%; /* Ширина таблицы */
        border-collapse: collapse; /* Убираем двойные границы */
        margin-bottom: 40px; /* Отступ снизу */
    }

    th, td {
        padding: 15px; /* Отступы внутри ячеек */
        text-align: left; /* Выравнивание текста влево */
        border-bottom: 1px solid #ddd; /* Нижняя граница ячеек */
    }

    th {
        background-color: #f2f2f2; /* Цвет фона заголовков */
    }

    .social-media {
        margin-top: 20px; /* Отступ сверху для секции социальных сетей */
    }
  </style>
</head>
<body>
<?php 
session_start(); // Начинаем сессию
?>
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

<div class="container">
    <h1>Контакты</h1>

    <?php
    require 'db.php'; // Подключаем файл с функцией getDbConnection()

    // Получаем все телефоны из базы данных
    $phones = get_phones_all();

    // Получаем все email-адреса из базы данных
    $emails = get_emails_all();

    // Получаем все ссылки на социальные сети
    $social_networks = get_social_networks_all();
    ?>

    <h2>Реквизиты</h2>
    <table>
      <tr>
        <th>Полное название</th>
        <td>ООО "Kriter Gourmet Desserts"</td>
      </tr>
      <tr>
        <th>Телефоны</th>
        <td>
          <?php foreach ($phones as $phone): ?>
            <?php echo $phone["phone"]; ?><br>
          <?php endforeach; ?>
        </td>
      </tr>
      <tr>
        <th>Почта</th>
        <td>
          <?php foreach ($emails as $email): ?>
            <?php echo $email["email_name"]; ?><br>
          <?php endforeach; ?>
        </td>
      </tr>
    </table>

    <h2>Наши Социальные Сети</h2>
    <table class="social-media">
      <tr>
        <th>Социальная сеть</th>
        <th>Ссылка</th>
      </tr>
      <?php foreach ($social_networks as $social_network): ?>
      <tr>
        <td><?php echo $social_network["social_network_name"]; ?></td>
        <td><a href="<?php echo $social_network["social_network_url"]; ?>"><?php echo $social_network["social_network_url"]; ?></a></td>
      </tr>
      <?php endforeach; ?>
    </table>
</div>
<footer>
        <p>&copy; 2024 Пекарня-кондитерская Kriter</p>
    </footer>
</body>
</html>