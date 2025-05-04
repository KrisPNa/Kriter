<?php
session_start();
require 'db.php'; // Подключаем db.php, где создается функция для подключения

// Получаем объект подключения
$pdo = getDbConnection();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php'); // Перенаправление на страницу авторизации
    exit();
}

$userId = $_SESSION['user_id']; // ID пользователя из сессии

// Получение данных из формы
$productId = $_POST['product_id'];
$newQuantity = (int)$_POST['quantity'];

// Проверка, существует ли товар в корзине
$stmt = $pdo->prepare('
    SELECT quantity 
    FROM cart 
    WHERE user_id = ? AND product_id = ?
');
$stmt->execute([$userId, $productId]);
$cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cartItem) {
    $oldQuantity = $cartItem['quantity'];
    
    // Обновляем количество товара в корзине
    $stmt = $pdo->prepare('
        UPDATE cart 
        SET quantity = ? 
        WHERE user_id = ? AND product_id = ?
    ');
    $stmt->execute([$newQuantity, $userId, $productId]);
    
    // Синхронизируем с таблицей menu
    if ($newQuantity > $oldQuantity) {
        // Если количество увеличилось, уменьшаем количество в меню
        $stmt = $pdo->prepare('
            UPDATE menu 
            SET quantity = quantity - ? 
            WHERE product_id = ?
        ');
        $stmt->execute([$newQuantity - $oldQuantity, $productId]);
    } elseif ($newQuantity < $oldQuantity) {
        // Если количество уменьшилось, увеличиваем количество в меню
        $stmt = $pdo->prepare('
            UPDATE menu 
            SET quantity = quantity + ? 
            WHERE product_id = ?
        ');
        $stmt->execute([$oldQuantity - $newQuantity, $productId]);
    }
}

header('Location: cart.php'); // Перенаправление обратно в корзину
exit();
?>
