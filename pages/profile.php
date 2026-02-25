<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php'; // Используем ТВОЙ db.php

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

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
    $new_password = $_POST['new_password'] ?? '';
    if (empty($new_password)) {
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

<p><strong>Имя:</strong> <?= htmlspecialchars($user['name']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<h3>Смена пароля</h3>
<form method="POST" action="">
    <label for="new_password">Новый пароль:</label><br>
    <input type="password" name="new_password" id="new_password" required minlength="8"><br><br>
    <button type="submit" name="change_password">Сменить пароль</button>
</form>

<?php include '../includes/footer.php'; ?>