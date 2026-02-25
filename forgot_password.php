<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Если пользователь уже авторизован — редирект на дашборд
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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Введите email.";
    } else {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Генерируем токен
            $reset_token = bin2hex(random_bytes(16));
            $reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Сохраняем токен в БД
            $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $updateStmt->execute([$reset_token, $reset_token_expires, $user['id']]);

            // Отправляем письмо (заглушка — в проде подключи PHPMailer)
            $subject = "Сброс пароля";
            $message = "Перейдите по ссылке для сброса пароля:

";
            $message .= "https://cabinet.mg-ceramic.ru/reset_password.php?token=" . $reset_token . "

";
            $message .= "Ссылка действительна 1 час.";

            // В реальности: mail($email, $subject, $message);
            // Пока — просто логируем
            error_log("Сброс пароля для $email: https://cabinet.mg-ceramic.ru/reset_password.php?token=$reset_token");

            $success = "Письмо с инструкциями отправлено на ваш email.";
        } else {
            $error = "Пользователь с таким email не найден.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Забыли пароль?</title>
</head>
<body>
<h2>Забыли пароль?</h2>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

<form method="post" action="">
    <label>Ваш email:</label><br>
    <input type="email" name="email" required placeholder="example@mail.com"><br><br>
    <button type="submit">Отправить ссылку для сброса</button>
</form>

<p><a href="login.php">Вернуться к входу</a></p>
</body>
</html>
