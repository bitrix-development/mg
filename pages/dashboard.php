<?php
require_once '../includes/security.php';
require_once '../db.php';

// --- 1) последние 10 клиентов (название фирмы) ---
$clients = [];
try {
    $stmt = $pdo->query("
        SELECT id, name
        FROM clients
        ORDER BY id DESC
        LIMIT 10
    ");
    $clients = $stmt->fetchAll();
} catch (Throwable $e) {
    $clients = [];
}

// --- 2) последние 10 заказов ---
$lastOrders = [];
try {
    $stmt = $pdo->query("
        SELECT o.id, o.order_date, o.total, o.status, c.name AS client_name
        FROM orders o
        JOIN clients c ON o.client_id = c.id
        ORDER BY o.order_date DESC
        LIMIT 10
    ");
    $lastOrders = $stmt->fetchAll();
} catch (Throwable $e) {
    $lastOrders = [];
}

// --- 3) последние 10 успешных авторизаций ---
$lastLogins = [];
try {
    $stmt = $pdo->query("
        SELECT created_at, ip_address
        FROM user_actions
        WHERE action = 'login_success'
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $lastLogins = $stmt->fetchAll();
} catch (Throwable $e) {
    $lastLogins = [];
}

// one-time welcome modal
$showWelcome = !empty($_SESSION['welcome_once']);
unset($_SESSION['welcome_once']);
?>

<?php include '../includes/header.php'; ?>

<h2>Обзор и ключевые показатели</h2>

<?php if ($showWelcome): ?>
  <div class="modal-backdrop" id="welcomeBackdrop" aria-hidden="false">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="welcomeTitle">
      <button class="modal-close" type="button" id="welcomeClose" aria-label="Закрыть окно">×</button>
      <h3 class="modal-title" id="welcomeTitle">Добро пожаловать!</h3>
      <p class="modal-text">Добро пожаловать, руководитель!</p>
    </div>
  </div>

  <script>
    (function () {
      const backdrop = document.getElementById('welcomeBackdrop');
      const closeBtn = document.getElementById('welcomeClose');

      function closeModal() {
        if (!backdrop) return;
        backdrop.classList.add('is-hidden');
        backdrop.setAttribute('aria-hidden', 'true');
        setTimeout(() => backdrop.remove(), 150);
        document.removeEventListener('keydown', onKeyDown);
      }

      function onKeyDown(e) {
        if (e.key === 'Escape') closeModal();
      }

      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      if (backdrop) backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) closeModal();
      });

      document.addEventListener('keydown', onKeyDown);
    })();
  </script>
<?php endif; ?>

<div class="dashboard-grid dashboard-grid--3">
  <section class="card">
    <div class="card-head">
      <h3>Клиенты</h3>
      <a class="card-link" href="/pages/clients.php">Все клиенты →</a>
    </div>

    <?php if (empty($clients)): ?>
      <p class="empty">Пока нет данных.</p>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($clients as $c): ?>
          <li class="list-item"><?= htmlspecialchars($c['name'] ?? '') ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <section class="card">
    <div class="card-head">
      <h3>Последние 10 заказов</h3>
      <a class="card-link" href="/pages/orders.php">Все заказы →</a>
    </div>

    <?php if (empty($lastOrders)): ?>
      <p class="empty">Пока нет данных.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Клиент</th>
              <th>Дата</th>
              <th>Сумма</th>
              <th>Статус</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lastOrders as $o): ?>
              <tr>
                <td><?= htmlspecialchars($o['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['client_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['order_date'] ?? '') ?></td>
                <td><?= isset($o['total']) ? number_format((float)$o['total'], 2, ',', ' ') . ' ₽' : '' ?></td>
                <td><?= htmlspecialchars($o['status'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <section class="card">
    <div class="card-head">
      <h3>Последние авторизации</h3>
      <a class="card-link" href="/activity_log.php">Логи →</a>
    </div>

    <?php if (empty($lastLogins)): ?>
      <p class="empty">Пока нет данных.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Дата/время</th>
              <th>IP</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lastLogins as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['created_at'] ?? '') ?></td>
                <td><?= htmlspecialchars($l['ip_address'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php include '../includes/footer.php'; ?>
