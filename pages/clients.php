<?php
require_once '../includes/security.php';
require_once '../db.php';

function h($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
        'active' => 'status status--completed',   // зелёный
        'inactive' => 'status status--cancelled', // красный
        default => 'status',
    };
}

// Фильтры
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT id, name, email, status FROM clients WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE :search OR email LIKE :search)";
    $params['search'] = "%$search%";
}

if ($filter_status) {
    $query .= " AND status = :status";
    $params['status'] = $filter_status;
}

$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$clients = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Клиенты</h2>

<form method="get" action="" class="filters">
    <input type="text" name="search" placeholder="Поиск..." value="<?= h($search) ?>" />
    <select name="status">
        <option value="">Все статусы</option>
        <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Активные</option>
        <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>Неактивные</option>
    </select>
    <button type="submit">Применить</button>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Email</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($clients)): ?>
            <tr>
                <td colspan="5" style="color: rgba(255,255,255,.62);">Ничего не найдено.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($clients as $client): ?>
            <tr>
                <td><?= h($client['id']) ?></td>
                <td><?= h($client['name']) ?></td>
                <td><?= h($client['email']) ?></td>
                <td>
                    <span class="<?= h(clientStatusBadgeClass($client['status'] ?? null)) ?>">
                        <?= h(clientStatusLabel($client['status'] ?? null)) ?>
                    </span>
                </td>
                <td>
                    <a class="link" href="client_detail.php?id=<?= (int)$client['id'] ?>">Подробнее</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
