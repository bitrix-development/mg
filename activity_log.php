<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../db.php';

// Проверка: только admin может видеть логи
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$role = $stmt->fetchColumn();

if ($role !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->query("SELECT * FROM user_logs ORDER BY created_at DESC LIMIT 100");
$logs = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Логи действий</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Время</th>
        <th>Пользователь</th>
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

<?php include '../includes/footer.php'; ?>
