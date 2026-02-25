<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Если пользователь уже авторизован — редирект
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$dotenv = parse_ini_file(__DIR__ . '/../.env');
if (!$dotenv) {
    die('Ошибка: файл .env не найден.');
}

try {
    $pdo = new PDO(
        'mysql:host=' . $dotenv['DB_HOST'] . ';dbname=' . $dotenv['DB_NAME'] . ';charset=utf8mb4',
        $dotenv['DB_USER'],
        $dotenv['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = "Неверная или отсутствующая ссылка для сброса.";
} else {
    $stmt = $pdo->prepare("SELECT id, reset_token, reset_token_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Ссылка недействительна.";
    } elseif (strtotime($user['reset_token_expires']) < time()) {
        $error = "Ссылка истекла. Запросите новую.";
    } else {
        // Форма для ввода нового пароля
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($new_password)) {
                $error = "Введите новый пароль.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Пароли не совпадают.";
            } elseif (strlen($new_password) < 8) {
                $error = "Пароль должен быть не менее 8 символов.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $updateStmt->execute([$hashed_password, $user['id']]);

                $success = "Пароль успешно изменён. Теперь вы можете войти.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Сброс пароля</title>
</head>
<body>
<h2>Сброс пароля</h2>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

<?php if (empty($error) && empty($success) && $token): ?>
<form method="post" action="">
    <label>Новый пароль:</label><br>
    <input type="password" name="new_password" required minlength="8"><br><br>

    <label>Подтвердите пароль:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Сменить пароль</button>
</form>
<?php endif; ?>

<p><a href="login.php">Войти</a> | <a href="forgot_password.php">Запросить новую ссылку</a></p>
</body>
</html>
