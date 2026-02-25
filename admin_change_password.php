<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/Logger.php';

$logger = new Logger();

// Проверка, что пользователь — админ
// В проекте используется $_SESSION['is_admin'] (см. login.php)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    die('Доступ запрещен');
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $new_password = (string)($_POST['new_password'] ?? '');

    if ($user_id <= 0) {
        $error = "Некорректный пользователь.";
    } elseif ($new_password === '') {
        $error = "Введите новый пароль.";
    } elseif (strlen($new_password) < 8) {
        $error = "Пароль должен быть не менее 8 символов.";
    } else {
        try {
            // (опционально) убедимся, что пользователь существует
            $stmt = $pdo->prepare("SELECT id, login FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $targetUser = $stmt->fetch();

            if (!$targetUser) {
                $error = "Пользователь не найден.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute([':password' => $hashed_password, ':id' => $user_id]);

                $success = "Пароль успешно изменён.";

                // Логируем действие администратора (основной лог — в user_actions)
                $logger->logAction(
                    $pdo,
                    (int)$_SESSION['user_id'],
                    'admin_password_changed',
                    "Admin changed password for user_id={$user_id} login={$targetUser['login']}"
                );
            }
        } catch (PDOException $e) {
            $error = "Ошибка базы данных.";
            $logger->logAction($pdo, (int)$_SESSION['user_id'], 'db_error', 'admin_change_password DB error: ' . $e->getMessage());
        }
    }
}

// Получение списка пользователей для отображения
// В БД поле называется login (не username)
$stmt = $pdo->query("SELECT id, login, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Изменение пароля пользователя</title>
</head>
<body>
<h1>Изменить пароль пользователя</h1>

<?php if ($error) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>" . htmlspecialchars($success) . "</p>"; ?>

<form method="post" action="">
    <label for="user_id">Пользователь:</label>
    <select name="user_id" id="user_id" required>
        <?php foreach ($users as $user): ?>
            <option value="<?= (int)$user['id'] ?>">
                <?= htmlspecialchars($user['login']) ?> (<?= htmlspecialchars($user['role']) ?>, ID: <?= (int)$user['id'] ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="new_password">Новый пароль:</label>
    <input type="password" name="new_password" id="new_password" required minlength="8"><br><br>

    <button type="submit">Изменить пароль</button>
</form>
</body>
</html>