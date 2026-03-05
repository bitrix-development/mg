<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

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

// Получаем ID заказа
$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    die('Некорректный ID заказа');
}

// Получаем данные заказа
$stmt = $pdo->prepare("
    SELECT o.id, o.order_date, o.total, o.status, c.name AS client_name, c.email AS client_email
    FROM orders o
    JOIN clients c ON o.client_id = c.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die('Заказ не найден');
}

// Получаем позиции заказа
$stmt_items = $pdo->prepare("SELECT product_name, quantity, price FROM order_items WHERE order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Детали заказа #<?= h($order['id']) ?></h2>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th width="30%">Поле</th>
                <th width="70%">Значение</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>ID заказа</strong></td>
                <td><?= h($order['id']) ?></td>
            </tr>
            <tr>
                <td><strong>Дата</strong></td>
                <td><?= h(formatDt($order['order_date'] ?? '')) ?></td>
            </tr>
            <tr>
                <td><strong>Клиент</strong></td>
                <td>
                    <?= h($order['client_name']) ?>
                    <?= !empty($order['client_email']) ? ' (' . h($order['client_email']) . ')' : '' ?>
                </td>
            </tr>
            <tr>
                <td><strong>Статус</strong></td>
                <td>
                    <span class="<?= h(statusBadgeClass($order['status'] ?? null)) ?>">
                        <?= h(statusLabel($order['status'] ?? null)) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Сумма</strong></td>
                <td><?= number_format((float)$order['total'], 2, ',', ' ') ?> ₽</td>
            </tr>
        </tbody>
    </table>
</div>

<h3 style="margin-top: 16px;">Позиции заказа</h3>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Товар</th>
                <th>Количество</th>
                <th>Цена</th>
                <th>Сумма</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <tr>
                <td colspan="4" style="color: rgba(255,255,255,.62);">Нет позиций.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= h($item['product_name'] ?? 'Не указано') ?></td>
                <td><?= h($item['quantity'] ?? '') ?></td>
                <td><?= $item['price'] !== null ? number_format((float)$item['price'], 2, ',', ' ') . ' ₽' : '' ?></td>
                <td><?= $item['price'] !== null ? number_format(((float)($item['quantity'] ?? 0)) * ((float)$item['price']), 2, ',', ' ') . ' ₽' : '' ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<p style="margin-top: 14px;">
    <a class="link" href="orders.php">← Вернуться к списку заказов</a>
</p>

<?php include '../includes/footer.php'; ?>
