<?php
include __DIR__ . "/../../config/koneksi.php";

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$p = isset($_POST['p']) ? trim($_POST['p']) : '';
$filterKelompok = isset($_POST['filter_kelompok']) ? $_POST['filter_kelompok'] : '';
$filterKategori = isset($_POST['filter_kategori']) ? $_POST['filter_kategori'] : '';
$maId = isset($_POST['ma_id']) ? (int) $_POST['ma_id'] : 0;

if ($id <= 0) {
    header('Location:../../admin/index.php?page=detail_aset&p=' . urlencode($p) . '&filter_kelompok=' . urlencode($filterKelompok) . '&filter_kategori=' . urlencode($filterKategori) . '&ma_id=' . $maId . '&action=delete&error=Data+tidak+valid');
    exit();
}

$stmt = $conn->prepare('SELECT a_file_dokumen FROM aset WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$stmt->close();

$stmt = $conn->prepare('DELETE FROM aset WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

if ($row && !empty($row['a_file_dokumen'])) {
    $filePath = __DIR__ . '/../../assets/uploads/dokumen_file/' . $row['a_file_dokumen'];
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

header('Location:../../admin/index.php?page=detail_aset&p=' . urlencode($p) . '&filter_kelompok=' . urlencode($filterKelompok) . '&filter_kategori=' . urlencode($filterKategori) . '&ma_id=' . $maId . '&action=delete&success=Data+berhasil+dihapus');
