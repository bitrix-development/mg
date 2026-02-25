<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

require_once __DIR__ . '/db.php';

// Проверка: только admin может видеть логи
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$role = $stmt->fetchColumn();

if ($role !== 'admin') {
    header('Location: /pages/dashboard.php');
    exit();
}

// ВАЖНО: user_logs -> user_actions
$stmt = $pdo->query("
    SELECT ua.*
    FROM user_actions ua
    ORDER BY ua.created_at DESC
    LIMIT 200
");
$logs = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<h2>Логи действий</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Время</th>
        <th>Пользователь (user_id)</th>
        <th>Действие</th>
        <th>Описание</th>
        <th>IP</th>
    </tr>
    <?php foreach ($logs as $log): ?>
    <tr>
        <td><?= htmlspecialchars($log['created_at']) ?></td>
        <td><?= htmlspecialchars($log['user_id'] ?? 'system') ?></td>
        <td><?= htmlspecialchars($log['action']) ?></td>
        <td><?= htmlspecialchars($log['description']) ?></td>
        <td><?= htmlspecialchars($log['ip_address']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include __DIR__ . '/includes/footer.php'; ?>