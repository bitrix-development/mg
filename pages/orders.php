<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

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

// Фильтры
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$query = "SELECT o.id, o.order_date, o.total, o.status, c.name AS client_name
         FROM orders o
         JOIN clients c ON o.client_id = c.id
         WHERE 1=1";

$params = [];

if ($search) {
    $query .= " AND (c.name LIKE ? OR o.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $query .= " AND o.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Заказы</h2>

<form method="get" action="" class="filters">
    <input type="text" name="search" placeholder="Поиск по ID или клиенту" value="<?= h($search) ?>" />
    <select name="status">
        <option value="">Все статусы</option>
        <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>Новый</option>
        <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>В обработке</option>
        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Выполнен</option>
        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Отменён</option>
    </select>
    <button type="submit">Фильтровать</button>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Клиент</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
            <tr>
                <td colspan="6" style="color: rgba(255,255,255,.62);">Ничего не найдено.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= h($order['id']) ?></td>
                <td><?= h($order['client_name']) ?></td>
                <td><?= h(formatDt($order['order_date'] ?? '')) ?></td>
                <td><?= number_format((float)$order['total'], 2, ',', ' ') ?> ₽</td>
                <td>
                    <span class="<?= h(statusBadgeClass($order['status'] ?? null)) ?>">
                        <?= h(statusLabel($order['status'] ?? null)) ?>
                    </span>
                </td>
                <td>
                    <a class="link" href="order_detail.php?id=<?= (int)$order['id'] ?>">Подробнее</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
