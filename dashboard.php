<?php
$title = 'Dashboard';
$active = 'dashboard';

$stats = [
    ['Produk',     (int)$pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn(),               'bi-box-seam',  'c-blue'],
    ['Total Stok', (int)$pdo->query('SELECT COALESCE(SUM(stok),0) FROM produk')->fetchColumn(), 'bi-stack',     'c-green'],
    ['Orders',     (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),               'bi-bag-check', 'c-amber'],
    ['Customers',  (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn(),            'bi-people',    'c-violet'],
];
require __DIR__ . '/../layout/header.php';
?>
<div class="page-head">
  <div>
    <h2>Dashboard</h2>
    <p class="subtitle">Ringkasan data toko kamu</p>
  </div>
</div>

<div class="row g-3">
  <?php foreach ($stats as [$label, $val, $icon, $color]): ?>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="stat-card card-soft <?= $color ?>">
        <div class="stat-icon"><i class="bi <?= $icon ?>"></i></div>
        <div>
          <div class="stat-label"><?= $label ?></div>
          <div class="stat-value"><?= number_format($val, 0, ',', '.') ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
