<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../pages/login.php');
    exit();
}

function h($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$logFile = '/home/bitrix/ext_www/cabinet.mg-ceramic.ru/logs/user_actions.log';

if (!file_exists($logFile)) {
    die('Файл логов не найден.');
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lines = array_reverse($lines); // свежие — сверху

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$total = count($lines);
$totalPages = max(1, (int)ceil($total / $limit));

$logs = array_slice($lines, $offset, $limit);

// Парсим строки (оставляем только понятные форматы)
$rows = [];
foreach ($logs as $line) {
    $line = trim($line);

    // Поддержка текущего формата Logger.php:
    // [YYYY-mm-dd HH:ii:ss] action | user_id: X | ip: Y | desc
    if (preg_match('#^\[(.*?)\]\s+(.*?)\s+\|\s+user_id:\s+(.*?)\s+\|\s+ip:\s+(.*?)(?:\s+\|\s+(.*))?$#', $line, $m)) {
        $rows[] = [
            'timestamp' => $m[1],
            'action' => $m[2],
            'user_id' => $m[3],
            'ip' => $m[4],
            'desc' => $m[5] ?? '',
        ];
        continue;
    }

    // (опционально) старый формат login_success/failed:
    if (preg_match('#^\[(.*?)\]\s+login_(success|failed)\s+\|\s+user:\s+(.*?)\s+\|\s+ip:\s+(.*?)$#', $line, $m)) {
        $rows[] = [
            'timestamp' => $m[1],
            'action' => 'login_' . $m[2],
            'user_id' => $m[3],
            'ip' => $m[4],
            'desc' => '',
        ];
        continue;
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>Админ: архив логов</h2>

<p class="help">Всего записей в файле: <?= (int)$total ?>. Показаны: <?= count($rows) ?>.</p>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>Дата и время</th>
        <th>Действие</th>
        <th>user_id</th>
        <th>IP</th>
        <th>Описание</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="5" style="color: rgba(255,255,255,.62);">Нет записей.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= h($r['timestamp']) ?></td>
            <td><?= h($r['action']) ?></td>
            <td><?= h($r['user_id']) ?></td>
            <td><?= h($r['ip']) ?></td>
            <td><?= h($r['desc']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
 <div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a class="link" href="?page=<?= $i ?>" <?= $i === $page ? 'aria-current="page"' : '' ?>>
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
<?php endif; ?>

<p class="help mt-10">
  <a class="link" href="/pages/dashboard.php">← Назад в кабинет</a>
</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
