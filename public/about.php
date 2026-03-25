<?php 
session_start(); // Начинаем сессию
require 'db.php'; // Подключаем файл с подключением к БД

// Получаем данные о пекарнях из базы данных
$pdo = getDbConnection();
$stmt = $pdo->query('SELECT * FROM adress ORDER BY id_pecar');
$bakeries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Функция для отображения звезд рейтинга
function displayRating($rating) {
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    
    $stars = str_repeat('★', $fullStars);
    if ($hasHalfStar) {
        $stars .= '★';
    }
    $stars .= str_repeat('☆', $emptyStars);
    
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>О нас - Кондитерская "Kriter"</title>
  <link rel="stylesheet" href="styles.css">
  <style>
      .content {
          padding: 0 20px; /* Отступы слева и справа для основного содержимого */
      }
      .founder-info {
          display: flex;
          justify-content: space-between; /* Распределение пространства между столбцами */
          align-items: center; /* Выравнивание по центру по вертикали */
          background-color: #f9f5ec; /* Цвет фона для блока */
          border-radius: 15px; /* Закругленные углы */
          padding: 20px; /* Отступ внутри блока */
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Тень для глубины */
      }
      .founder-text {
          flex: 22; /* Занимает оставшееся пространство */
          text-align: center; /* Выравнивание текста по центру */
      }
      .founder-photo {
          width: 300px; /* Ширина изображения */
          height: auto; /* Автоматическая высота */
          margin-left: 80px; /* Отступ между текстом и изображением */
      }
      .bakery-locations {
          margin-top: 40px;
          padding: 20px;
          background-color: #f9f5ec;
          border-radius: 15px;
      }

      .locations-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 20px;
          margin-top: 20px;
      }

      .location-card {
          background: white;
          padding: 20px;
          border-radius: 10px;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
          text-align: center;
      }

      .location-card h3 {
          color: #333;
          margin-bottom: 10px;
      }

      .location-card p {
          color: #666;
          margin-bottom: 15px;
      }

      .rating {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 10px;
      }

      .stars {
          color: #ffd700;
          font-size: 1.2em;
          letter-spacing: 2px;
      }

      .rating-value {
          color: #333;
          font-weight: bold;
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

  <main class="content">
        <section class="company-history">
            <h2>История компании</h2>
            <p>
                Французская пекарня-кондитерская Kriter была открыта в 2010 году в Вивьене, 
                в районе Юг-Уэст. Мы предлагаем широкий ассортимент свежих хлебобулочных изделий, 
                макарон и тортов, вдохновленных французскими традициями.
            </p>
        </section>
        <section class="founder">
            <h2>Основатель</h2>
            <div class="founder-info">
                <div class="founder-text">
                    <p>
                        Основатель сети Kriter, родился в Нормандии и с 13 лет увлекался 
                        искусством выпечки. Его страсть к французской кулинарии, натуральным ингредиентам 
                        и любви к традициям вдохновляет команду.
                    </p>
                </div>
                <img src="img/photo_2024-12-14_18-47-07.jpg" alt="Основатель Kriter" class="founder-photo">
            </div>
        </section>
        <section class="established-year">
            <h2>Год основания</h2>
            <p>2010 год</p>
        </section>

        <section class="bakery-locations">
            <h2>Наши пекарни в Минске</h2>
            <div class="locations-grid">
                <?php foreach ($bakeries as $bakery): ?>
                <div class="location-card">
                    <h3><?= htmlspecialchars($bakery['location_name']) ?></h3>
                    <p><?= htmlspecialchars($bakery['location']) ?></p>
                    <div class="rating">
                        <span class="stars" title="Рейтинг: <?= $bakery['rating'] ?>">
                            <?= displayRating($bakery['rating']) ?>
                        </span>
                        <span class="rating-value"><?= number_format($bakery['rating'], 1) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Пекарня-кондитерская Kriter</p>
    </footer>
</body>
</html>
</html>