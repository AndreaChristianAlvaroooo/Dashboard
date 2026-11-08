<?php
require __DIR__ . '/config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (hash_equals(ADMIN_USER, $_POST['username'] ?? '') && hash_equals(ADMIN_PASS, $_POST['password'] ?? '')) {
        session_regenerate_id(true);
        $_SESSION['user'] = ADMIN_USER;
        redirect('index.php');
    }
    $error = 'Username atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - satamart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body class="login-body">
<div class="login-card card-soft">
  <div class="text-center mb-4">
    <div class="login-logo"><i class="bi bi-shop"></i></div>
    <h4 class="fw-bold mt-2 mb-0">satamart</h4>
    <small class="text-muted">Masuk untuk melanjutkan</small>
  </div>
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <label class="form-label">Username</label>
    <input type="text" name="username" class="form-control mb-3" required autofocus>
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control mb-4" required>
    <button class="btn btn-primary w-100">Masuk</button>
  </form>
</div>
</body>
</html>
