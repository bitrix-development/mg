<?php
require_once '../includes/security.php';
require_once '../db.php';

// Обработка фильтров и поиска
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT * FROM clients WHERE 1=1";

$params = [];

if ($search) {
    $query .= " AND (name LIKE :search OR email LIKE :search)";
    $params['search'] = "%$search%";
}

if ($filter_status) {
    $query .= " AND status = :status";
    $params['status'] = $filter_status;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$clients = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Клиенты</h2>

<form method="get" action="">
    <input type="text" name="search" placeholder="Поиск..." value="<?php echo htmlspecialchars($search); ?>" />
    <select name="status">
        <option value="">Все статусы</option>
        <option value="active" <?php if ($filter_status=='active') echo 'selected'; ?>>Активные</option>
        <option value="inactive" <?php if ($filter_status=='inactive') echo 'selected'; ?>>Неактивные</option>
    </select>
    <button type="submit">Применить</button>
</form>

<table>
<tr>
    <th>ID</th>
    <th>Название</th>
    <th>Email</th>
    <th>Статус</th>
    <th>Действия</th>
</tr>
<?php foreach ($clients as $client): ?>
<tr>
    <td><?php echo $client['id']; ?></td>
    <td><?php echo htmlspecialchars($client['name']); ?></td>
    <td><?php echo htmlspecialchars($client['email']); ?></td>
    <td><?php echo $client['status']; ?></td>
    <td><a href="client_detail.php?id=<?php echo $client['id']; ?>">Подробнее</a></td>
</tr>
<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>