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

// CRUD тут нет (только просмотр), поэтому не логируем CRUD-события.

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

<form method="get" action="">
    <input type="text" name="search" placeholder="Поиск по ID или клиенту" value="<?= htmlspecialchars($search) ?>" />
    <select name="status">
        <option value="">Вс�� статусы</option>
        <option value="new" <?= $status == 'new' ? 'selected' : '' ?>>Новый</option>
        <option value="processing" <?= $status == 'processing' ? 'selected' : '' ?>>В обработке</option>
        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Выполнен</option>
        <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Отменён</option>
    </select>
    <button type="submit">Фильтровать</button>
</form>

<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <tr>
        <th>ID</th>
        <th>Клиент</th>
        <th>Дата</th>
        <th>Сумма</th>
        <th>Статус</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($orders as $order): ?>
    <tr>
        <td><?= htmlspecialchars($order['id']) ?></td>
        <td><?= htmlspecialchars($order['client_name']) ?></td>
        <td><?= htmlspecialchars($order['order_date']) ?></td>
        <td><?= number_format($order['total'], 2, ',', ' ') ?> ₽</td>
        <td><?= htmlspecialchars($order['status']) ?></td>
        <td><a href="order_detail.php?id=<?= (int)$order['id'] ?>">Подробнее</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>