<?php
session_start();
// Проверка, что пользователь — админ
if ($_SESSION['user_role'] !== 'admin') {
    die('Доступ запрещен');
}

require_once 'db.php'; // подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];

    if (empty($new_password)) {
        $error = "Введите новый пароль.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([':password' => $hashed_password, ':id' => $user_id]);
        $success = "Пароль успешно изменен.";
    }
}

// Получение списка пользователей для отображения
$stmt = $pdo->query("SELECT id, username FROM users");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Изменение пароля пользователя</title>
</head>
<body>
<h1>Изменить пароль пользователя</h1>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

<form method="post" action="">
    <label for="user_id">Пользователь:</label>
    <select name="user_id" id="user_id" required>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?> (ID: <?= $user['id'] ?>)</option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="new_password">Новый пароль:</label>
    <input type="password" name="new_password" id="new_password" required><br><br>

    <button type="submit">Изменить пароль</button>
</form>
</body>
</html>