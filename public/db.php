<?php
function getDbConnection() {
    $host = 'mysql-8.0'; // Ваш хост (обычно localhost)
    $dbname = 'kriter'; // Название вашей базы данных
    $username = 'root'; // Ваше имя пользователя для базы данных
    $password = ''; // Ваш пароль для базы данных
    

    try {
        // Создаем объект PDO для подключения
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

function get_menu_all() {
    $pdo = getDbConnection(); // Получаем подключение через функцию
    try {
        $menu = $pdo->query("SELECT * FROM menu");
        return $menu;
    } catch (PDOException $e) {
        echo "Ошибка запроса: " . $e->getMessage();
        return false;
    }
}

function get_category_by_id($id) {
    $pdo = getDbConnection(); // Получаем подключение через функцию
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Возвращаем один результат
    } catch (PDOException $e) {
        echo "Ошибка запроса: " . $e->getMessage();
        return false;
    }
}

function get_phones_all() {
    $pdo = getDbConnection(); // Получаем подключение через функцию
    try {
        $phones = $pdo->query("SELECT * FROM phones");
        return $phones;
    } catch (PDOException $e) {
        echo "Ошибка запроса: " . $e->getMessage();
        return false;
    }
}

function get_emails_all() {
    $pdo = getDbConnection(); // Получаем подключение через функцию
    try {
        $emails = $pdo->query("SELECT * FROM email");
        return $emails;
    } catch (PDOException $e) {
        echo "Ошибка запроса: " . $e->getMessage();
        return false;
    }
}

function get_social_networks_all() {
    $pdo = getDbConnection(); // Получаем подключение через функцию
    try {
        $social_networks = $pdo->query("SELECT * FROM social_network");
        return $social_networks;
    } catch (PDOException $e) {
        echo "Ошибка запроса: " . $e->getMessage();
        return false;
    }
}
?>
