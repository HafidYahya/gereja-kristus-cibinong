<?php
include __DIR__ . "/config/koneksi.php";

$url = $_GET['url'] ?? 'home';
$page = $url === '' ? 'home' : $url;
$file = __DIR__ . '/public/pages/' . $page . '.php';




// fallback kalau file tidak ada
if (!file_exists($file)) {
    header("Location: index.php?url=404");
    exit();
}

$title = ucwords(str_replace(['-', '_'], ' ', $page));

// layout
include __DIR__ . '/public/layouts/navbar.php';

// isi halaman
include $file;

// Footer
include __DIR__ . '/public/layouts/footer.php';
