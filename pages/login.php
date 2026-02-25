<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Перед показом формы или обработкой, сохраняем реферер
if (!isset($_SESSION['redirect_after_login'])) {
    // Получаем URL, с которого пришли
    $ref = $_SERVER['HTTP_REFERER'] ?? '/';

    // Можно фильтровать или ограничивать допустимые URL
    // Например, чтобы не было внешних перенаправлений
    if (strpos($ref, $_SERVER['SERVER_NAME']) !== false || $ref == '/' || $ref == '') {
        $_SESSION['redirect_after_login'] = $ref;
    } else {
        $_SESSION['redirect_after_login'] = '/';
    }
}

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Проверка лимита — предполагается, что RateLimiter реализован
    // (если нужно, добавьте сюда вызов RateLimiter)

    // Проверка учётных данных
    require_once '../includes/Logger.php';
    require_once '../includes/RateLimiter.php';

    // Подключение к БД — предполагается, что есть connection, или используйте ваш PDO
    require_once '../db.php'; // или ваш метод подключения

    try {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE login = :login");
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = ($user['role'] === 'admin');

            // После успешной авторизации — редирект
            $redirect = '/'; // по умолчанию

            if (isset($_SESSION['redirect_after_login'])) {
                $redirectUrl = $_SESSION['redirect_after_login'];

                // фильтр URL
                if (strpos($redirectUrl, $_SERVER['SERVER_NAME']) !== false || $redirectUrl == '/' || $redirectUrl == '') {
                    $redirect = $redirectUrl;
                }
                unset($_SESSION['redirect_after_login']);
            } else {
                $redirect = $_SESSION['is_admin'] ? '../admin/logs.php' : '/pages/dashboard.php';
            }

            // Специальный случай: если админ попал с /admin/logs.php
            if ($_SESSION['is_admin'] && strpos($redirect, '/admin/logs.php') !== false) {
                $redirect = '../admin/logs.php';
            }

            header('Location: ' . $redirect);
            exit();
        } else {
            $error = "Неверные логин или пароль";
        }
    } catch (PDOException $e) {
        $error = "Ошибка базы данных.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Вход в личный кабинет</h2>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post" action="">
    <label>Логин:</label>
    <input type="text" name="login" required autocomplete="username" />
    <label>Пароль:</label>
    <input type="password" name="password" required autocomplete="current-password" />
    <!-- CSRF токен, если есть, добавьте сюда -->
    <button type="submit">Войти</button>
</form>

<?php include '../includes/footer.php'; ?>