<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Переменная для контроля отображения логов
$showLogs = 0; // 0 — скрывать, 1 — показывать

// Можно управлять через GET-параметр, например ?show_logs=1
if (isset($_GET['show_logs']) && $_GET['show_logs'] == 1) {
    $showLogs = 1;
}

// --- ДЕБАГ: Показываем реальное содержимое лог-файла ---
$logFile = '/home/bitrix/ext_www/cabinet.mg-ceramic.ru/logs/user_actions.log';
if ($showLogs && file_exists($logFile)) {
    $raw = file_get_contents($logFile);
    echo '<pre style="background:#f0f0f0; padding:1rem; margin:1rem 0; border:1px solid #ccc; color:red;">';
    echo "СОДЕРЖИМОЕ ЛОГ-ФАЙЛА (последние 10 строк):<br>";
    echo implode("<br>", array_slice(explode("\n", $raw), -10));
    echo "<br>";
    echo "ДЛИНА СТРОКИ ПЕРВОЙ ЗАПИСИ: " . strlen(trim(explode("\n", $raw)[0])) . " символов<br>";
    echo "ПЕРВАЯ СТРОКА (в HEX): " . bin2hex(trim(explode("\n", $raw)[0])) . "<br>";
    echo "</pre>";
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../pages/login.php');
    exit();
}

$logFile = '/home/bitrix/ext_www/cabinet.mg-ceramic.ru/logs/user_actions.log';

if (!file_exists($logFile)) {
    die('Файл логов не найден.');
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lines = array_reverse($lines); // Свежие записи — в начале списка

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit; // исправлено: * вместо [i]
$total = count($lines);
$totalPages = ceil($total / $limit);

$logs = array_slice($lines, $offset, $limit);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-архив логов</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h2>Архив логов входа</h2>
        <p><strong>Всего записей:</strong> <?= $total ?></p>

        
        <table class="admin-table">
            <thead>
                <tr><th>Дата и время</th><th>Действие</th><th>Пользователь</th><th>IP</th></tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="4">Нет записей</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $line): ?>
                        <?php if ($showLogs): ?>
                        <?php
                        echo '<pre style="background:#fff; padding:0.5rem; margin:0.5rem 0; border:1px solid #ddd; color:#333; font-size:12px;">';
                        echo "Обрабатываем строку: " . $line . "<br>";
                        echo "Длина: " . strlen($line) . " символов<br>";
                        echo "HEX: " . bin2hex($line) . "<br>";
                        echo "</pre>";
   endif; 
                        $line = trim($line);
                        if (preg_match('#^\[(.*?)\]\s+login_(success|failed)\s+\| user:\s+(.*?)\s+\| ip:\s+(.*?)$#', $line, $match)) {
                            $timestamp = $match[1];
                            $status = $match[2];
                            $action = $status === 'success' ? '✅ Вход' : '❌ Неудача';
                            $user = htmlspecialchars($match[3]);
                            $ip = htmlspecialchars($match[4]);
                        } else {
                            continue;
                        }
                        ?>
                        <tr>
                            <td><?= $timestamp ?></td>
                            <td><?= $action ?></td>
                            <td><?= $user ?></td>
                            <td><?= $ip ?></td>
                        </tr>
                    <?php endforeach; ?>
             
            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $showLogs ? '&show_logs=1' : '' ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <p><a href="../pages/logout.php">Выйти</a></p>
    </div>
</body>
</html>