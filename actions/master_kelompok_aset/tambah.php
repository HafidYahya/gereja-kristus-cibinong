<?php
include __DIR__ . "/../../config/koneksi.php";
$nama = trim($_POST['nama']);
$status = trim($_POST['status']);


$stmt = $conn->prepare("INSERT INTO master_kelompok_aset (kel_nama, kel_is_active) VALUES (?,?)");
$stmt->bind_param('si', $nama, $status);
$stmt->execute();
$stmt->close();
header('Location: ../../admin/index.php?page=master_kelompok_aset&action=tambah&tambah&success=Data+baru+berhasil+ditambahkan');
