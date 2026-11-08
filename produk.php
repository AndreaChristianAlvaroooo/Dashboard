<?php
$act = $_GET['act'] ?? 'list';
$id  = (int)($_GET['id'] ?? 0);
$errors = [];
$data = ['id' => 0, 'nama_produk' => '', 'harga' => '', 'stok' => '', 'gambar' => '', 'kategori' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $pid = (int)($_POST['id'] ?? 0);
        $st = $pdo->prepare('SELECT gambar FROM produk WHERE id = ?');
        $st->execute([$pid]);
        if ($row = $st->fetch()) {
            if ($row['gambar'] && is_file(__DIR__ . '/../uploads/' . basename($row['gambar']))) {
                unlink(__DIR__ . '/../uploads/' . basename($row['gambar']));
            }
            $pdo->prepare('DELETE FROM produk WHERE id = ?')->execute([$pid]);
            flash('Produk berhasil dihapus.');
        }
        redirect('index.php?page=produk');
    }

    if ($do === 'save') {
        $data = [
            'id'          => (int)($_POST['id'] ?? 0),
            'nama_produk' => trim($_POST['nama_produk'] ?? ''),
            'harga'       => $_POST['harga'] ?? '',
            'stok'        => $_POST['stok'] ?? '',
            'gambar'      => basename($_POST['gambar_lama'] ?? ''),
            'kategori'    => trim($_POST['kategori'] ?? ''),
        ];
        $act = $data['id'] ? 'edit' : 'add';

        if ($data['nama_produk'] === '') $errors[] = 'Nama produk wajib diisi.';
        if (!is_numeric($data['harga']) || $data['harga'] < 0) $errors[] = 'Harga tidak valid.';
        if (!is_numeric($data['stok']) || $data['stok'] < 0) $errors[] = 'Stok tidak valid.';

        $err = null;
        $baru = upload_gambar($_FILES['gambar'] ?? ['error' => UPLOAD_ERR_NO_FILE], $err);
        if ($err) $errors[] = $err;

        if (!$errors) {
            if ($baru && $data['gambar'] && is_file(__DIR__ . '/../uploads/' . $data['gambar'])) {
                unlink(__DIR__ . '/../uploads/' . $data['gambar']);
            }
            $gambar = $baru ?: $data['gambar'];
            if ($data['id']) {
                $pdo->prepare('UPDATE produk SET nama_produk=?, harga=?, stok=?, gambar=?, kategori=? WHERE id=?')
                    ->execute([$data['nama_produk'], (int)$data['harga'], (int)$data['stok'], $gambar, $data['kategori'], $data['id']]);
                flash('Produk berhasil diperbarui.');
            } else {
                $pdo->prepare('INSERT INTO produk (nama_produk, harga, stok, gambar, kategori) VALUES (?,?,?,?,?)')
                    ->execute([$data['nama_produk'], (int)$data['harga'], (int)$data['stok'], $gambar, $data['kategori']]);
                flash('Produk berhasil ditambahkan.');
            }
            redirect('index.php?page=produk');
        }
    }
} elseif ($act === 'edit' && $id) {
    $st = $pdo->prepare('SELECT * FROM produk WHERE id = ?');
    $st->execute([$id]);
    $data = $st->fetch() ?: redirect('index.php?page=produk');
}

$active = 'produk';
$isForm = in_array($act, ['add', 'edit'], true);
$title  = $isForm ? ($act === 'edit' ? 'Edit Produk' : 'Tambah Produk') : 'Data Produk';
$rows   = $isForm ? [] : $pdo->query('SELECT * FROM produk ORDER BY id DESC')->fetchAll();
require __DIR__ . '/../layout/header.php';
?>

<?php if ($isForm): ?>
  <div class="page-head">
    <div>
      <h2><?= e($title) ?></h2>
      <p class="subtitle">Lengkapi data produk di bawah ini</p>
    </div>
  </div>

  <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>

  <div class="card-soft form-card">
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
      <input type="hidden" name="gambar_lama" value="<?= e($data['gambar']) ?>">

      <div class="mb-3">
        <label class="form-label">Nama Produk</label>
        <input type="text" name="nama_produk" class="form-control" value="<?= e($data['nama_produk']) ?>" required>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Harga (Rp)</label>
          <input type="number" name="harga" min="0" class="form-control" value="<?= e($data['harga']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Stok</label>
          <input type="number" name="stok" min="0" class="form-control" value="<?= e($data['stok']) ?>" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Upload Gambar</label>
        <?php if ($data['gambar']): ?>
          <div class="mb-2"><img src="uploads/<?= e($data['gambar']) ?>" class="thumb" alt=""></div>
        <?php endif; ?>
        <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png">
        <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
      </div>
      <div class="mb-4">
        <label class="form-label">Kategori</label>
        <input type="text" name="kategori" class="form-control" placeholder="Contoh: Senjata" value="<?= e($data['kategori']) ?>">
      </div>
      <button class="btn btn-primary">Simpan</button>
      <a href="index.php?page=produk" class="btn btn-outline-secondary">Batal</a>
    </form>
  </div>

<?php else: ?>
  <div class="page-head">
    <div>
      <h2>Data Produk</h2>
      <p class="subtitle"><?= count($rows) ?> produk terdaftar</p>
    </div>
    <a href="index.php?page=produk&act=add" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
  </div>

  <div class="card-soft table-card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr><th>No</th><th>Gambar</th><th>Nama Barang</th><th>Harga</th><th>Stok</th><th>Kategori</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="empty-row"><i class="bi bi-inbox"></i> Belum ada data produk.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?php if ($r['gambar']): ?><img src="uploads/<?= e($r['gambar']) ?>" class="thumb" alt=""><?php else: ?><div class="thumb thumb-empty"><i class="bi bi-image"></i></div><?php endif; ?></td>
            <td class="fw-semibold"><?= e($r['nama_produk']) ?></td>
            <td class="price"><?= rupiah($r['harga']) ?></td>
            <td><span class="pill"><?= (int)$r['stok'] ?></span></td>
            <td><?= e($r['kategori']) ?></td>
            <td class="text-end">
              <a href="index.php?page=produk&act=edit&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
              <form method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
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
