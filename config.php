<?php
session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'satamart_db';   // dibuat otomatis, tidak perlu import manual
const DB_USER = 'root';
const DB_PASS = '';

// Login sederhana (ganti sebelum dipakai sungguhan)
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'admin123';

$schema = [
    'CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) DEFAULT NULL,
        telepon VARCHAR(30) DEFAULT NULL,
        alamat TEXT
    )',
    'CREATE TABLE IF NOT EXISTS produk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_produk VARCHAR(150) NOT NULL,
        harga BIGINT NOT NULL DEFAULT 0,
        stok INT NOT NULL DEFAULT 0,
        gambar VARCHAR(255) DEFAULT NULL,
        kategori VARCHAR(100) DEFAULT NULL
    )',
    'CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pelanggan VARCHAR(100) NOT NULL,
        produk VARCHAR(150) NOT NULL,
        jumlah INT NOT NULL DEFAULT 1,
        tanggal DATE NOT NULL
    )',
];

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $pdo->exec('USE `' . DB_NAME . '`');
    foreach ($schema as $sql) $pdo->exec($sql);
} catch (PDOException $ex) {
    http_response_code(500);
    exit('<h3>Koneksi database gagal</h3><p>Pesan error: <code>' . htmlspecialchars($ex->getMessage()) . '</code></p>'
       . '<p>Cek: MySQL di Laragon sudah Start, dan user/password di <code>config.php</code> sesuai.</p>');
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Token tidak valid, silakan refresh halaman.');
    }
}

function flash(?string $msg = null, string $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function upload_gambar(array $f, ?string &$err): ?string {
    if ($f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'Upload gambar gagal.'; return null; }
    if ($f['size'] > 2 * 1024 * 1024) { $err = 'Ukuran gambar maksimal 2MB.'; return null; }
    $info = @getimagesize($f['tmp_name']);
    $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'][$info[2] ?? 0] ?? null;
    if (!$ext) { $err = 'Format gambar harus JPG, JPEG, atau PNG.'; return null; }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $name)) {
        $err = 'Gagal menyimpan gambar ke folder uploads.'; return null;
    }
    return $name;
}
