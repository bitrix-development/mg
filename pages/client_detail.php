<?php
require_once '../includes/security.php';
require_once '../db.php';

function h($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatDt($v): string {
    if (!$v) return '';
    $ts = strtotime((string)$v);
    if (!$ts) return (string)$v;
    return date('d.m.Y H:i', $ts);
}

function statusLabel(?string $status): string {
    return match ($status) {
        'new' => 'Новый',
        'processing' => 'В обработке',
        'completed' => 'Выполнен',
        'cancelled' => 'Отменён',
        default => (string)($status ?? ''),
    };
}

function statusBadgeClass(?string $status): string {
    return match ($status) {
        'new' => 'status status--new',
        'processing' => 'status status--processing',
        'completed' => 'status status--completed',
        'cancelled' => 'status status--cancelled',
        default => 'status',
    };
}

function clientStatusLabel(?string $status): string {
    return match ($status) {
        'active' => 'Активный',
        'inactive' => 'Неактивный',
        default => (string)($status ?? ''),
    };
}

function clientStatusBadgeClass(?string $status): string {
    return match ($status) {
        'active' => 'status status--completed',
        'inactive' => 'status status--cancelled',
        default => 'status',
    };
}

$client_id = (int)($_GET['id'] ?? 0);
if ($client_id <= 0) {
    die('Некорректный ID клиента');
}

$stmt = $pdo->prepare("SELECT id, name, email, phone, address, status FROM clients WHERE id = :id");
$stmt->execute(['id' => $client_id]);
$client = $stmt->fetch();

if (!$client) {
    die('Клиен�� не найден');
}

// заказы клиента
$stmt_orders = $pdo->prepare("
    SELECT id, order_date, status, total
    FROM orders
    WHERE client_id = :client_id
    ORDER BY order_date DESC
");
$stmt_orders->execute(['client_id' => $client_id]);
$orders = $stmt_orders->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Детали клиента</h2>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th width="30%">Поле</th>
        <th width="70%">Значение</th>
      </tr>
    </thead>
    <tbody>
      <tr><td><strong>Название</strong></td><td><?= h($client['name']) ?></td></tr>
      <tr><td><strong>Email</strong></td><td><?= h($client['email']) ?></td></tr>
      <tr><td><strong>Телефон</strong></td><td><?= h($client['phone']) ?></td></tr>
      <tr><td><strong>Адрес</strong></td><td><?= h($client['address']) ?></td></tr>
      <tr>
        <td><strong>Статус</strong></td>
        <td>
          <span class="<?= h(clientStatusBadgeClass($client['status'] ?? null)) ?>">
            <?= h(clientStatusLabel($client['status'] ?? null)) ?>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<h3 style="margin-top: 16px;">Заказы клиента</h3>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>ID заказа</th>
        <th>Дата</th>
        <th>Статус</th>
        <th>Сумма</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($orders)): ?>
      <tr><td colspan="5" style="color: rgba(255,255,255,.62);">У клиента пока нет заказов.</td></tr>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
      <tr>
        <td><?= h($order['id']) ?></td>
        <td><?= h(formatDt($order['order_date'] ?? '')) ?></td>
        <td>
          <span class="<?= h(statusBadgeClass($order['status'] ?? null)) ?>">
            <?= h(statusLabel($order['status'] ?? null)) ?>
          </span>
        </td>
        <td><?= number_format((float)($order['total'] ?? 0), 2, ',', ' ') ?> ₽</td>
        <td><a class="link" href="order_detail.php?id=<?= (int)$order['id'] ?>">Подробнее</a></td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<p style="margin-top: 14px;">
  <a class="link" href="clients.php">← Вернуться к списку клиентов</a>
</p>

<?php include '../includes/footer.php'; ?>
