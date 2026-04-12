<?php
include __DIR__ . "/../../config/koneksi.php";
$nama = trim($_POST['nama']);
$merk = trim($_POST['merk']);
$spesifikasi = mysqli_real_escape_string($conn, $_POST['spesifikasi']);
$master_kelompok_id = trim($_POST['master_kelompok_id']);
$master_kategori_id = trim($_POST['master_kategori_id']);
$status = trim($_POST['status']);


$stmt = $conn->prepare("INSERT INTO master_aset (ma_nama, ma_merk, ma_spesifikasi, ma_master_kelompok_id, ma_master_kategori_id, ma_is_active) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssiii', $nama, $merk, $spesifikasi, $master_kelompok_id, $master_kategori_id, $status);
$stmt->execute();
$stmt->close();
header('Location: ../../admin/index.php?page=master_aset&action=tambah&tambah&success=Data+baru+berhasil+ditambahkan');
