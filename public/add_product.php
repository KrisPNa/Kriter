<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка, авторизован ли администратор
if (!isset($_SESSION['admin_user'])) {
    header('Location: login.php'); 
    exit();
}

$db = getDbConnection();

// Обработка добавления нового товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);
    $categoryId = (int)$_POST['category_id'];
    $imageUrl = trim($_POST['image_url']);

    $errors = [];

    // Валидация названия
    if (strlen($name) > 30) {
        $errors[] = "Название не должно превышать 30 символов.";
    }

    // Валидация описания
    if (strlen($description) > 100) {
        $errors[] = "Описание не должно превышать 100 символов.";
    }

    // Валидация цены
    if (!preg_match('/^\d{1,5}$/', $price)) {
        $errors[] = "Цена должна содержать только цифры (максимум 5 цифр).";
    }

    // Валидация количества
    if (!preg_match('/^\d{1,4}$/', $quantity)) {
        $errors[] = "Количество должно содержать только цифры (максимум 4 цифры).";
    }

    // Валидация URL изображения
    if (!preg_match('/^img\\\\.{1,500}$/', $imageUrl)) {
        $errors[] = "URL изображения должен начинаться с 'img\\' и содержать не более 500 символов после слеша.";
    }

    if (empty($errors)) {
        // Проверка на существующий товар с таким же именем и категорией
        $stmt = $db->prepare("SELECT COUNT(*) FROM menu WHERE name = ? AND category_id = ?");
        $stmt->execute([$name, $categoryId]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $errors[] = "Товар с таким названием уже существует в данной категории.";
        } else {
            // Вставка нового товара в базу данных
            $stmt = $db->prepare("INSERT INTO menu (name, description, price, quantity, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $quantity, $categoryId, $imageUrl]);

            // Перенаправление после добавления
            header('Location: admin_dashboard.php?success=1');
            exit();
        }
    }
}

// Получение категорий для выбора
$stmt = $db->query("SELECT category_id, category_name FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить Товар</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error-message p {
            margin: 5px 0;
        }

        .field-error {
            color: #c62828;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-footer {
            margin-top: 30px;
            text-align: center;
        }

        .form-footer a {
            color: #4CAF50;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="img/logo.png" alt="логотип" width="140" height="140" /></a>
    </div>
</header>

<div class="content">
    <div class="form-container">
        <h2>Добавить Новый Товар</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="add_product.php" method="post" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="name">Название:</label>
                <input type="text" id="name" name="name" placeholder="Введите название товара" required 
                       maxlength="30" oninput="validateName(this)">
                <span id="name-error" class="field-error">Максимум 30 символов</span>
            </div>

            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" placeholder="Введите описание товара" required 
                          maxlength="100" oninput="validateDescription(this)"></textarea>
                <span id="description-error" class="field-error">Максимум 100 символов</span>
            </div>

            <div class="form-group">
                <label for="price">Цена:</label>
                <input type="text" id="price" name="price" placeholder="Введите цену" required 
                       pattern="\d{1,5}" oninput="validatePrice(this)">
                <span id="price-error" class="field-error">Только цифры (максимум 5)</span>
            </div>

            <div class="form-group">
                <label for="quantity">Количество:</label>
                <input type="text" id="quantity" name="quantity" placeholder="Введите количество" required 
                       pattern="\d{1,4}" oninput="validateQuantity(this)">
                <span id="quantity-error" class="field-error">Только цифры (максимум 4)</span>
            </div>

            <div class="form-group">
                <label for="image_url">URL Изображения:</label>
                <input type="text" id="image_url" name="image_url" placeholder="img\путь_к_изображению" required 
                       oninput="validateImageUrl(this)">
                <span id="image-error" class="field-error">Должно начинаться с 'img\' и содержать не более 500 символов после слеша</span>
            </div>

            <div class="form-group">
                <label for="category_id">Категория:</label>
                <select name="category_id" required>
                    <option value="">Выберите категорию</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['category_id']) ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="add_product">Добавить Товар</button>
        </form>

        <div class="form-footer">
            <a href="admin_dashboard.php">← Вернуться к списку товаров</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2024 Кондитерская "Kriter"</p>
</footer>

<script>
function validateName(input) {
    const errorSpan = document.getElementById('name-error');
    if (input.value.length > 30) {
        errorSpan.style.display = 'inline';
        return false;
    } else {
        errorSpan.style.display = 'none';
        return true;
    }
}

function validateDescription(input) {
    const errorSpan = document.getElementById('description-error');
    if (input.value.length > 100) {
        errorSpan.style.display = 'inline';
        return false;
    } else {
        errorSpan.style.display = 'none';
        return true;
    }
}

function validatePrice(input) {
    const errorSpan = document.getElementById('price-error');
    if (!/^\d{1,5}$/.test(input.value)) {
        errorSpan.style.display = 'inline';
        return false;
    } else {
        errorSpan.style.display = 'none';
        return true;
    }
}

function validateQuantity(input) {
    const errorSpan = document.getElementById('quantity-error');
    if (!/^\d{1,4}$/.test(input.value)) {
        errorSpan.style.display = 'inline';
        return false;
    } else {
        errorSpan.style.display = 'none';
        return true;
    }
}

function validateImageUrl(input) {
    const errorSpan = document.getElementById('image-error');
    if (!/^img\\.{1,500}$/.test(input.value)) {
        errorSpan.style.display = 'inline';
        return false;
    } else {
        errorSpan.style.display = 'none';
        return true;
    }
}

function validateForm() {
    const name = document.getElementById('name');
    const description = document.getElementById('description');
    const price = document.getElementById('price');
    const quantity = document.getElementById('quantity');
    const imageUrl = document.getElementById('image_url');

    return validateName(name) &&
           validateDescription(description) &&
           validatePrice(price) &&
           validateQuantity(quantity) &&
           validateImageUrl(imageUrl);
}
</script>

</body>
</html>