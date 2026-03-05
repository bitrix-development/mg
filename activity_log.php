<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

require_once __DIR__ . '/db.php';

function h($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Проверка: только admin может видеть логи
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$role = $stmt->fetchColumn();

if ($role !== 'admin') {
    header('Location: /pages/dashboard.php');
    exit();
}

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

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>Время</th>
        <th>Пол��зователь (user_id)</th>
        <th>Действие</th>
        <th>Описание</th>
        <th>IP</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($logs)): ?>
        <tr>
          <td colspan="5" style="color: rgba(255,255,255,.62);">Пока нет записей.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td><?= h($log['created_at']) ?></td>
            <td><?= h($log['user_id'] ?? 'system') ?></td>
            <td><?= h($log['action']) ?></td>
            <td><?= h($log['description']) ?></td>
            <td><?= h($log['ip_address']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<p class="help" style="margin-top: 10px;">
  <a class="link" href="/pages/dashboard.php">← Назад в кабинет</a>
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
