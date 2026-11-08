<?php
$act = $_GET['act'] ?? 'list';
$id  = (int)($_GET['id'] ?? 0);
$errors = [];
$data = ['id' => 0, 'nama' => '', 'email' => '', 'telepon' => '', 'alamat' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $pdo->prepare('DELETE FROM customers WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('Customer berhasil dihapus.');
        redirect('index.php?page=customers');
    }

    if ($do === 'save') {
        $data = [
            'id'      => (int)($_POST['id'] ?? 0),
            'nama'    => trim($_POST['nama'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'telepon' => trim($_POST['telepon'] ?? ''),
            'alamat'  => trim($_POST['alamat'] ?? ''),
        ];
        $act = $data['id'] ? 'edit' : 'add';

        if ($data['nama'] === '') $errors[] = 'Nama wajib diisi.';
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';

        if (!$errors) {
            if ($data['id']) {
                $pdo->prepare('UPDATE customers SET nama=?, email=?, telepon=?, alamat=? WHERE id=?')
                    ->execute([$data['nama'], $data['email'], $data['telepon'], $data['alamat'], $data['id']]);
                flash('Customer berhasil diperbarui.');
            } else {
                $pdo->prepare('INSERT INTO customers (nama, email, telepon, alamat) VALUES (?,?,?,?)')
                    ->execute([$data['nama'], $data['email'], $data['telepon'], $data['alamat']]);
                flash('Customer berhasil ditambahkan.');
            }
            redirect('index.php?page=customers');
        }
    }
} elseif ($act === 'edit' && $id) {
    $st = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $st->execute([$id]);
    $data = $st->fetch() ?: redirect('index.php?page=customers');
}

$active = 'customers';
$isForm = in_array($act, ['add', 'edit'], true);
$title  = $isForm ? ($act === 'edit' ? 'Edit Customer' : 'Tambah Customer') : 'Data Customers';
$rows   = $isForm ? [] : $pdo->query('SELECT * FROM customers ORDER BY id DESC')->fetchAll();
require __DIR__ . '/../layout/header.php';
?>

<?php if ($isForm): ?>
  <div class="page-head">
    <div>
      <h2><?= e($title) ?></h2>
      <p class="subtitle">Data kontak pelanggan</p>
    </div>
  </div>

  <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>

  <div class="card-soft form-card">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" value="<?= e($data['nama']) ?>" required>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= e($data['email']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Telepon</label>
          <input type="text" name="telepon" class="form-control" value="<?= e($data['telepon']) ?>">
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" rows="3" class="form-control"><?= e($data['alamat']) ?></textarea>
      </div>
      <button class="btn btn-primary">Simpan</button>
      <a href="index.php?page=customers" class="btn btn-outline-secondary">Batal</a>
    </form>
  </div>

<?php else: ?>
  <div class="page-head">
    <div>
      <h2>Data Customers</h2>
      <p class="subtitle"><?= count($rows) ?> customer terdaftar</p>
    </div>
    <a href="index.php?page=customers&act=add" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Customer</a>
  </div>

  <div class="card-soft table-card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr><th>No</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Alamat</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="empty-row"><i class="bi bi-inbox"></i> Belum ada data customer.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="fw-semibold"><?= e($r['nama']) ?></td>
            <td><?= e($r['email']) ?></td>
            <td><?= e($r['telepon']) ?></td>
            <td><?= e($r['alamat']) ?></td>
            <td class="text-end">
              <a href="index.php?page=customers&act=edit&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
              <form method="post" class="d-inline" onsubmit="return confirm('Hapus customer ini?')">
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
