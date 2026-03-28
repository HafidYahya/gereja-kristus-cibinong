<?php
include __DIR__ . '/../../config/koneksi.php';
$id = $_POST['id'] ?? null;

$stmt = $conn->prepare("SELECT * FROM master_kategori_aset WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    header('Location: ../../admin/index.php?page=master_kategori_aset&action=Ubah+status&error=Data+tidak+ditemukan');
    exit();
}
$status = $row['kat_is_active'] == 1 ? 0 : 1;


$stmt_update = $conn->prepare('UPDATE master_kategori_aset SET kat_is_active=? WHERE id=?');
$stmt_update->bind_param('ii', $status, $id);
$stmt_update->execute();
$stmt_update->close();
header('Location: ../../admin/index.php?page=master_kategori_aset&action=Ubah+status&success=Status+berhasil+diubah');

