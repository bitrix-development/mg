<?php
require_once '../includes/security.php';
require_once '../db.php';

$client_id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
$stmt->execute(['id' => $client_id]);
$client = $stmt->fetch();

if (!$client) {
    die('Клиент не найден');
}
?>

<?php include '../includes/header.php'; ?>

<h2>Детали клиента</h2>

<p><strong>Название:</strong> <?php echo htmlspecialchars($client['name']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($client['email']); ?></p>
<p><strong>Телефон:</strong> <?php echo htmlspecialchars($client['phone']); ?></p>
<p><strong>Адрес:</strong> <?php echo htmlspecialchars($client['address']); ?></p>

<h3>Заказы клиента</h3>
<!-- Здесь список заказов клиента -->

<?php
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE client_id = :client_id");
$stmt_orders->execute(['client_id' => $client_id]);
$orders = $stmt_orders->fetchAll();
?>

<table>
<tr>
    <th>ID заказа</th>
    <th>Дата</th>
    <th>Статус</th>
    <th>Сумма</th>
    <th>Действия</th>
</tr>
<?php foreach ($orders as $order): ?>
<tr>
    <td><?php echo $order['id']; ?></td>
    <td><?php echo $order['date']; ?></td>
    <td><?php echo $order['status']; ?></td>
    <td><?php echo $order['total']; ?></td>
    <td><a href="order_detail.php?id=<?php echo $order['id']; ?>">Подробнее</a></td>
</tr>
<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>