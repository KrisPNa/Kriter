<?php
session_start();
require 'db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header('Location: login.php');
    exit();
}

// Если авторизован, можно показывать контент
echo "Добро пожаловать, " . htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $products = json_decode($_POST['products'], true); // Декодируем JSON

    foreach ($products as $product) {
        $sql = "INSERT INTO orders (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product['id'], $product['quantity']);
        $stmt->execute();
    }

    echo "Заказ оформлен!";
    $stmt->close();
}
$conn->close();
?>