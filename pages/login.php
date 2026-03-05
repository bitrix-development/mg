<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Перед показом формы или обработкой, сохраняем реферер
if (!isset($_SESSION['redirect_after_login'])) {
    $ref = $_SERVER['HTTP_REFERER'] ?? '/';

    // защита от внешних редиректов
    if (strpos($ref, $_SERVER['SERVER_NAME']) !== false || $ref == '/' || $ref == '') {
        $_SESSION['redirect_after_login'] = $ref;
    } else {
        $_SESSION['redirect_after_login'] = '/';
    }
}

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    require_once '../db.php';
    require_once '../includes/Logger.php';
    require_once '../includes/RateLimiter.php';

    $logger = new Logger();
    $rateLimiter = new RateLimiter($pdo);

    // Rate limit до проверки пароля (и логируем факт блокировки отдельным событием)
    if (!$rateLimiter->allowLogin($login, $ip)) {
        $logger->logAction($pdo, null, 'login_blocked', "Rate limit block for login={$login}");
        $error = "Слишком много попыток. Попробуйте позже.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE login = :login");
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['is_admin'] = ($user['role'] === 'admin');

                // лог успешного входа (основной — в БД)
                $logger->logAction($pdo, (int)$user['id'], 'login_success', "Login attempt: {$login}");

                // однократное приветствие на dashboard
                $_SESSION['welcome_once'] = 1;

                // После успешной авторизации — редирект
                $redirect = '/'; // по умолчанию

                if (isset($_SESSION['redirect_after_login'])) {
                    $redirectUrl = $_SESSION['redirect_after_login'];

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
            }

            // лог неуспешного входа
            $logger->logAction(
                $pdo,
                $user ? (int)$user['id'] : null,
                'login_failed',
                "Login attempt: {$login}"
            );

            $error = "Неверные логин или пароль";
        } catch (PDOException $e) {
            // техническая ошибка БД — тоже полезно залогировать
            $logger->logAction($pdo, null, 'db_error', 'Login DB error: ' . $e->getMessage());
            $error = "Ошибка базы данных.";
        }
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
