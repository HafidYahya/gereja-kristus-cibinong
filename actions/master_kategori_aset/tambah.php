<?php
include __DIR__ . "/../../config/koneksi.php";
$nama = trim($_POST['nama']);
$status = trim($_POST['status']);


$stmt = $conn->prepare("INSERT INTO master_kategori_aset (kat_nama, kat_is_active) VALUES (?,?)");
$stmt->bind_param('si', $nama, $status);
$stmt->execute();
$stmt->close();
header('Location: ../../admin/index.php?page=master_kategori_aset&action=tambah&tambah&success=Data+baru+berhasil+ditambahkan');
