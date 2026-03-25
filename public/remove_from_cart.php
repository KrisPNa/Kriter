<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo "<p>Вы не авторизованы. Пожалуйста, выполните вход.</p>";
    echo "<a href='register.php'>Войти</a>";
    exit();
}

$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'] ?? null;

if (!$productId || !is_numeric($productId)) {
    echo "<p>Неверные данные.</p>";
    exit();
}

// Подключаемся к базе данных
$pdo = getDbConnection();

// Получаем информацию о товаре из корзины
$stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cartItem) {
    echo "<p>Товар не найден в корзине.</p>";
    exit();
}

// Получаем информацию о товаре из меню
$stmt = $pdo->prepare("SELECT quantity FROM menu WHERE product_id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<p>Товар не найден в меню.</p>";
    exit();
}

// Восстанавливаем количество товара в меню
$newQuantity = $product['quantity'] + $cartItem['quantity'];
$stmt = $pdo->prepare("UPDATE menu SET quantity = ? WHERE product_id = ?");
$stmt->execute([$newQuantity, $productId]);

// Удаляем товар из корзины
$stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);

// Перенаправляем на страницу корзины
header('Location: cart.php');
exit();
?>
