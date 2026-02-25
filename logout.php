<?php
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/Logger.php';

$logger = new Logger();
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// логируем ДО уничтожения сессии
$logger->logAction($pdo, $userId, 'logout', 'User logged out');

// Unset all of the session variables.
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to login page
header('Location: /pages/login.php');
exit();
?>