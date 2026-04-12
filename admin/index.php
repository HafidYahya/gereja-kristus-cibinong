<?php
session_start();
include __DIR__ . "/../config/koneksi.php";
if (!isset($_SESSION['user']) && empty($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$allowed = ['dashboard', 'users', '404', 'master_kelompok_aset', 'master_kategori_aset', 'ruangan', 'master_aset', 'jemaat', 'aset', 'approval_ruangan', 'approval_aset'];
$page = $_GET['page'] ?? 'dashboard';
if (!in_array($page, $allowed, true)) {
    $page = '404';
}
// tentukan content
$content = __DIR__ . "/pages/$page.php";

// fallback kalau file tidak ada
if (!file_exists($content)) {
    header("Location: index.php?page=404");
    exit();
}

$title = ucwords(str_replace(['-', '_'], ' ', $page . " | GKC"));

// layout
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
include __DIR__ . '/../layouts/navbar.php';

// isi halaman
include $content;

include __DIR__ . '/../layouts/footer.php';
