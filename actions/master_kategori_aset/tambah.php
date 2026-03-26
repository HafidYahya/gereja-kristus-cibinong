<?php
include __DIR__ . "/../../config/koneksi.php";
$nama = trim($_POST['nama']);
$status = trim($_POST['status']);

$stmt = $conn->prepare("SELECT * FROM master_kategori_aset WHERE kat_nama=?");
$stmt->bind_param('s', $nama);
$stmt->execute();
$result = $stmt->get_result();
$nama_exist = $result && $result->num_rows > 0;
$stmt->close();
if ($nama_exist) {
    header('Location: ../../index.php?page=master_kategori_aset&action=tambah&tambah&error=Nama+' . $nama . '+sudah+terdaftar');
    exit();
}
$stmt = $conn->prepare("INSERT INTO master_kategori_aset (kat_nama, kat_is_active) VALUES (?,?)");
$stmt->bind_param('si', $nama, $status);
$stmt->execute();
$stmt->close();
header('Location: ../../index.php?page=master_kategori_aset&action=tambah&tambah&success=Data+baru+berhasil+ditambahkan');
