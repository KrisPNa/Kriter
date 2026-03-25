<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    $userId = $_SESSION['user_id'];
    
    try {
        $pdo = getDbConnection();
        
        // Проверяем, есть ли уже такой товар в корзине
        $stmt = $pdo->prepare('SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingItem) {
            // Если товар уже в корзине, удаляем его
            $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$userId, $productId]);
            echo 'removed';
        } else {
            // Если товара нет в корзине, добавляем его
            $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)');
            $stmt->execute([$userId, $productId]);
            echo 'added';
        }
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Database error');
    }
} else {
    http_response_code(400);
    exit('Bad request');
}

// Проверка, что товар передан
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    header('Location: menu.php'); // Перенаправление на меню, если ID товара не передан
    exit();
}

$productId = (int)$_GET['product_id'];
$userId = $_SESSION['user_id'];

// Получаем данные о товаре
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM menu WHERE product_id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Если товар не найден
if (!$product) {
    header('Location: menu.php'); // Перенаправление на меню, если товар не найден
    exit();
}

// Проверка, есть ли уже этот товар в корзине
$stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cartItem) {
    // Если товар уже в корзине, увеличиваем количество
    $newQuantity = $cartItem['quantity'] + 1;
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$newQuantity, $userId, $productId]);
} else {
    // Если товара нет в корзине, добавляем его
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $productId, 1]);
}

// Уменьшаем количество товара в базе данных только после добавления в корзину
$stmt = $pdo->prepare("UPDATE menu SET quantity = quantity - 1 WHERE product_id = ?");
$stmt->execute([$productId]);

// Проверяем, успешно ли уменьшилось количество
if ($stmt->rowCount() === 0) {
    // Если количество не уменьшилось, откатываем добавление в корзину
    if ($cartItem) {
        // Если товар уже был в корзине, уменьшаем его количество
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
    } else {
        // Удаляем товар из корзины, если он был добавлен
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
    }
}

// Перенаправляем обратно в меню
header('Location: menu.php');
exit();
?>
