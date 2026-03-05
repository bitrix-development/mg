<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Если пользователь уже авторизован — редирект на дашборд
if (isset($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
    exit();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/Logger.php';

$logger = new Logger();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Введите email.';
    } else {
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $reset_token = bin2hex(random_bytes(16));
            $reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $updateStmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
            $updateStmt->execute([$reset_token, $reset_token_expires, $user['id']]);

            // Логируем запрос сброса (основной лог — в БД)
            $logger->logAction($pdo, (int)$user['id'], 'password_reset_requested', 'Password reset requested by email');

            // В проде лучше PHPMailer, но оставляем как есть
            error_log("Сброс пароля для $email: https://cabinet.mg-ceramic.ru/reset_password.php?token=$reset_token");

            $success = 'Письмо с инструкциями отправлено на ваш email.';
        } else {
            $error = 'Пользователь с таким email не найден.';

            // Можно логировать и это, но без user_id
            $logger->logAction($pdo, null, 'password_reset_requested', "Password reset requested for unknown email={$email}");
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<h2>Забыли пароль?</h2>

<?php if ($error): ?>
  <div class="alert alert--error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert--success" role="status"><?= htmlspecialchars($success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="" class="form">
  <div class="form-row">
    <label for="email">Ваш email</label>
    <input type="email" name="email" id="email" required placeholder="example@mail.com" autocomplete="email">
  </div>

  <button type="submit">Отправить ссылку для сброса</button>
</form>

<p class="help mt-10">
  <a class="link" href="/pages/login.php">← Вернуться к входу</a>
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
