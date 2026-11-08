<?php
// Variabel dari halaman: $title (judul tab), $active (menu aktif)
$menu = [
    'dashboard' => ['Dashboard', 'bi-speedometer2'],
    'orders'    => ['Orders', 'bi-list-check'],
    'produk'    => ['Products', 'bi-box-seam'],
    'customers' => ['Customers', 'bi-people'],
];
$f = flash();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Dashboard') ?> - satamart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR (tidak diubah) -->
<nav class="topbar">
  <a href="index.php" class="brand"><i class="bi bi-shop"></i> satamart</a>
</nav>

<div class="layout">
  <aside class="sidebar">
    <ul class="nav-list">
      <?php foreach ($menu as $key => [$label, $icon]): ?>
        <li>
          <a href="index.php?page=<?= $key ?>" class="nav-item-link <?= ($active ?? '') === $key ? 'active' : '' ?>">
            <i class="bi <?= $icon ?>"></i> <?= $label ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
    <hr>
    <a href="logout.php" class="nav-item-link signout"><i class="bi bi-box-arrow-left"></i> Sign out</a>
  </aside>
  <!-- /NAVBAR -->

  <main class="content">
    <?php if ($f): ?>
      <div class="alert alert-<?= e($f[1]) ?> alert-dismissible fade show" role="alert">
        <?= e($f[0]) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
