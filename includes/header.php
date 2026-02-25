<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // подключение безопасности
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Личный кабинет руководителя</title>
<link rel="stylesheet" href="/assets/css/style.css" />
</head>
<body>
<header>
  <h1>Личный кабинет руководителя отдела продаж</h1>
  <nav>
    <a href="/pages/dashboard.php">Главная</a>
    <a href="/pages/clients.php">Клиенты</a>
    <a href="/pages/orders.php">Заказы</a>
    <a href="/pages/profile.php">Профиль</a>
    <a href="/pages/logout.php">Выход</a>
  </nav>
  <?php
// В начале файла уже есть session_start() — всё ок
// Проверяем, чтобы не перезаписывать, если уже есть
if (!isset($_SESSION['redirect_after_login'])) {
    // Получаем текущий URL страницы
    $current_url = $_SERVER['REQUEST_URI'];

    // Можно дополнительно фильтровать, например, исключить страницы входа
    // Также можно сохранять только если пользователь не залогинен
    if (!isset($_SESSION['user_id'])) {
        // Сохраняем текущий URL для редиректа после логина
        $_SESSION['redirect_after_login'] = $current_url;
    }
}
?>
</header>
<main>