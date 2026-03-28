<?php
include __DIR__ . "/../../config/koneksi.php";
$nama = trim($_POST['nama']);
$status = trim($_POST['status']);

$stmt = $conn->prepare("SELECT * FROM master_kelompok_aset WHERE kel_nama=?");
$stmt->bind_param('s', $nama);
$stmt->execute();
$result = $stmt->get_result();
$nama_exist = $result && $result->num_rows > 0;
$stmt->close();
if ($nama_exist) {
    header('Location: ../../admin/index.php?page=master_kelompok_aset&action=tambah&tambah&error=Nama+' . $nama . '+sudah+terdaftar');
    exit();
}
$stmt = $conn->prepare("INSERT INTO master_kelompok_aset (kel_nama, kel_is_active) VALUES (?,?)");
$stmt->bind_param('si', $nama, $status);
$stmt->execute();
$stmt->close();
header('Location: ../../admin/index.php?page=master_kelompok_aset&action=tambah&tambah&success=Data+baru+berhasil+ditambahkan');

