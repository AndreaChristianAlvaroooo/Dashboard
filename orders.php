<?php
$act = $_GET['act'] ?? 'list';
$id  = (int)($_GET['id'] ?? 0);
$errors = [];
$data = ['id' => 0, 'pelanggan' => '', 'produk' => '', 'jumlah' => 1, 'tanggal' => date('Y-m-d')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $pdo->prepare('DELETE FROM orders WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('Order berhasil dihapus.');
        redirect('index.php?page=orders');
    }

    if ($do === 'save') {
        $data = [
            'id'        => (int)($_POST['id'] ?? 0),
            'pelanggan' => trim($_POST['pelanggan'] ?? ''),
            'produk'    => trim($_POST['produk'] ?? ''),
            'jumlah'    => $_POST['jumlah'] ?? '',
            'tanggal'   => $_POST['tanggal'] ?? '',
        ];
        $act = $data['id'] ? 'edit' : 'add';

        if ($data['pelanggan'] === '') $errors[] = 'Pelanggan wajib diisi.';
        if ($data['produk'] === '') $errors[] = 'Produk wajib diisi.';
        if (!ctype_digit((string)$data['jumlah']) || (int)$data['jumlah'] < 1) $errors[] = 'Jumlah minimal 1.';
        if (!DateTime::createFromFormat('Y-m-d', (string)$data['tanggal'])) $errors[] = 'Tanggal tidak valid.';

        if (!$errors) {
            if ($data['id']) {
                $pdo->prepare('UPDATE orders SET pelanggan=?, produk=?, jumlah=?, tanggal=? WHERE id=?')
                    ->execute([$data['pelanggan'], $data['produk'], (int)$data['jumlah'], $data['tanggal'], $data['id']]);
                flash('Order berhasil diperbarui.');
            } else {
                $pdo->prepare('INSERT INTO orders (pelanggan, produk, jumlah, tanggal) VALUES (?,?,?,?)')
                    ->execute([$data['pelanggan'], $data['produk'], (int)$data['jumlah'], $data['tanggal']]);
                flash('Order berhasil ditambahkan.');
            }
            redirect('index.php?page=orders');
        }
    }
} elseif ($act === 'edit' && $id) {
    $st = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $st->execute([$id]);
    $data = $st->fetch() ?: redirect('index.php?page=orders');
}

$active = 'orders';
$isForm = in_array($act, ['add', 'edit'], true);
$title  = $isForm ? ($act === 'edit' ? 'Edit Order' : 'Form Add Order') : 'Data Orders';
$rows   = $isForm ? [] : $pdo->query('SELECT * FROM orders ORDER BY tanggal DESC, id DESC')->fetchAll();
if ($isForm) {
    $listCust = $pdo->query('SELECT nama FROM customers ORDER BY nama')->fetchAll(PDO::FETCH_COLUMN);
    $listProd = $pdo->query('SELECT nama_produk FROM produk ORDER BY nama_produk')->fetchAll(PDO::FETCH_COLUMN);
}
require __DIR__ . '/../layout/header.php';
?>

<?php if ($isForm): ?>
  <div class="page-head">
    <div>
      <h2><?= e($title) ?></h2>
      <p class="subtitle">Catat pesanan pelanggan</p>
    </div>
  </div>

  <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>

  <div class="card-soft form-card">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

      <div class="mb-3">
        <label class="form-label">Pelanggan</label>
        <input type="text" name="pelanggan" list="dl-cust" class="form-control" value="<?= e($data['pelanggan']) ?>" required>
        <datalist id="dl-cust"><?php foreach ($listCust as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
      </div>
      <div class="mb-3">
        <label class="form-label">Produk</label>
        <input type="text" name="produk" list="dl-prod" class="form-control" value="<?= e($data['produk']) ?>" required>
        <datalist id="dl-prod"><?php foreach ($listProd as $p): ?><option value="<?= e($p) ?>"><?php endforeach; ?></datalist>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Jumlah</label>
          <input type="number" name="jumlah" min="1" class="form-control" value="<?= e($data['jumlah']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="<?= e($data['tanggal']) ?>" required>
        </div>
      </div>
      <button class="btn btn-primary">Simpan</button>
      <a href="index.php?page=orders" class="btn btn-outline-secondary">Batal</a>
    </form>
  </div>

<?php else: ?>
  <div class="page-head">
    <div>
      <h2>Data Orders</h2>
      <p class="subtitle"><?= count($rows) ?> order tercatat</p>
    </div>
    <a href="index.php?page=orders&act=add" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Order</a>
  </div>

  <div class="card-soft table-card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr><th>No</th><th>Pelanggan</th><th>Produk</th><th>Jumlah</th><th>Tanggal</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="empty-row"><i class="bi bi-inbox"></i> Belum ada data order.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="fw-semibold"><?= e($r['pelanggan']) ?></td>
            <td><?= e($r['produk']) ?></td>
            <td><span class="pill"><?= (int)$r['jumlah'] ?></span></td>
            <td><?= e(date('d M Y', strtotime($r['tanggal']))) ?></td>
            <td class="text-end">
              <a href="index.php?page=orders&act=edit&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
              <form method="post" class="d-inline" onsubmit="return confirm('Hapus order ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="do" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
