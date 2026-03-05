<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

function h($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$user_id = (int)$_SESSION['user_id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die('Пользователь не найден');
}

// Обработка смены пароля
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $new_password = (string)($_POST['new_password'] ?? '');

    if ($new_password === '') {
        $error = "Введите новый пароль.";
    } elseif (strlen($new_password) < 8) {
        $error = "Пароль должен быть не менее 8 символов.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashed_password, $user_id]);
        $success = "Пароль успешно изменён.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Профиль</h2>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th width="30%">Поле</th>
        <th width="70%">Значение</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Имя</strong></td>
        <td><?= h($user['name']) ?></td>
      </tr>
      <tr>
        <td><strong>Email</strong></td>
        <td><?= h($user['email']) ?></td>
      </tr>
    </tbody>
  </table>
</div>

<?php if ($error): ?>
  <div class="alert alert--error" role="alert"><?= h($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert--success" role="status"><?= h($success) ?></div>
<?php endif; ?>

<h3 style="margin-top: 16px;">Смена пароля</h3>

<form method="POST" action="" class="form">
  <div class="form-row">
    <label for="new_password">Новый пароль</label>
    <input type="password" name="new_password" id="new_password" required minlength="8" autocomplete="new-password">
    <p class="help">Минимум 8 символов.</p>
  </div>

  <button type="submit" name="change_password">Сменить пароль</button>
</form>

<?php include '../includes/footer.php'; ?>
