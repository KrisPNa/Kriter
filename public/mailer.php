<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $subject, $body) {
    $mail = new PHPMailer(true); // Создаем экземпляр PHPMailer

    // Пример адреса отправителя
$senderEmail = 'your_email@example.com'; // Ваш email для отправки
$senderPassword = 'your_email_password'; // Ваш пароль для отправки

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com'; // SMTP сервер
    $mail->SMTPAuth = true;
    $mail->Username = $senderEmail; // Ваш email
    $mail->Password = $senderPassword; // Ваш пароль
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($senderEmail, 'Your Name'); // Отправитель
    $mail->addAddress($to); // Получатель, введенный пользователем

    $mail->isHTML(true);
    $mail->Subject = 'Тема письма';
    $mail->Body    = 'Содержимое письма';

    $mail->send();
    echo 'Письмо отправлено';
} catch (Exception $e) {
    echo "Ошибка отправки: {$mail->ErrorInfo}";
}
}
?>