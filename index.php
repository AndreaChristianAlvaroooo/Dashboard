<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user'])) redirect('login.php');

$pages = ['dashboard', 'produk', 'orders', 'customers'];
$page = $_GET['page'] ?? 'dashboard';
if (!in_array($page, $pages, true)) $page = 'dashboard';

require __DIR__ . "/pages/$page.php";
