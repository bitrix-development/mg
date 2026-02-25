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

// Получаем ID заказа
$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) {
    die('Некорректный ID заказа');
}

// Получаем данные заказа — ИСПРАВЛЕНО: o.date → o.order_date
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

<h2>Детали заказа #<?= htmlspecialchars($order['id']) ?></h2>

<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <tr>
        <th width="20%">Поле</th>
        <th width="80%">Значение</th>
    </tr>
    <tr>
        <td><strong>ID заказа</strong></td>
        <td><?= htmlspecialchars($order['id']) ?></td>
    </tr>
    <tr>
        <td><strong>Дата</strong></td>
        <td><?= htmlspecialchars($order['order_date']) ?></td>
    </tr>
    <tr>
        <td><strong>Клиент</strong></td>
        <td><?= htmlspecialchars($order['client_name']) ?> (<?= htmlspecialchars($order['client_email']) ?>)</td>
    </tr>
    <tr>
        <td><strong>Статус</strong></td>
        <td><?= htmlspecialchars($order['status']) ?></td>
    </tr>
    <tr>
        <td><strong>Сумма</strong></td>
        <td><?= number_format($order['total'], 2, ',', ' ') ?> ₽</td>
    </tr>
</table>

<h3>Позиции заказа</h3>
<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <tr>
        <th>Товар</th>
        <th>Количество</th>
        <th>Цена</th>
        <th>Сумма</th>
    </tr>
<?php foreach ($items as $item): ?>
<tr>
    <td><?= htmlspecialchars($item['product_name'] ?? 'Не указано') ?></td>
    <td><?= htmlspecialchars($item['quantity']) ?></td>
    <td><?= number_format($item['price'], 2, ',', ' ') ?> ₽</td>
    <td><?= number_format($item['quantity'] * $item['price'], 2, ',', ' ') ?> ₽</td>
</tr>
<?php endforeach; ?>
</table>

<p><a href="orders.php">← Вернуться к списку заказов</a></p>

<?php include '../includes/footer.php'; ?>