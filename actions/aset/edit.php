<?php
include __DIR__ . "/../../config/koneksi.php";

// HIDDEN INPUT
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$p = isset($_POST['p']) ? trim($_POST['p']) : '';
$filterKelompok = isset($_POST['filter_kelompok']) ? $_POST['filter_kelompok'] : '';
$filterKategori = isset($_POST['filter_kategori']) ? $_POST['filter_kategori'] : '';
$maId = isset($_POST['ma_id']) ? (int) $_POST['ma_id'] : 0;

// DATA ASET INPUT
$master_aset_id = trim($_POST['master_aset_id'] ?? 0);
$tanggal_perolehan = trim($_POST['tanggal_perolehan'] ?? null);
$harga_perolehan = trim($_POST['harga_perolehan'] ?? 0);
$estimasi_harga = trim($_POST['estimasi_harga'] ?? 0);
$ruangan_id = $_POST['ruangan_id'] !== '' ? (int)$_POST['ruangan_id'] : null;
$lokasi = trim($_POST['lokasi'] ?? '');
$status_aset = trim($_POST['status_aset'] ?? '');
$kondisi_aset = trim($_POST['kondisi_aset'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$boleh_pinjam = (int) $_POST['boleh_pinjam'] ?? '';
$gambar_lama = trim($_POST['gambar_lama'] ?? '');


// Validasi data kosong
if ($id <= 0 || $master_aset_id === '' || $tanggal_perolehan === '' || $harga_perolehan === '' || $estimasi_harga === '' || $status_aset === '' || $kondisi_aset === '' || $boleh_pinjam) {
    header('Location:../../admin/index.php?page=detail_aset&p=' . urlencode($p) . '&filter_kelompok=' . urlencode($filterKelompok) . '&filter_kategori=' . urlencode($filterKategori) . '&ma_id=' . $maId . '&action=update&error=Data+tidak+boleh+kosong');
    exit();
}

// 

$namaFile = $gambar_lama;
$uploadDir = __DIR__ . '/../../assets/uploads/dokumen_file/';

if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] !== 4) {
    $file = $_FILES['file_dokumen'];
    $namaFile = $file['name'];
    $tmpName = $file['tmp_name'];
    $size = $file['size'];
    $error = $file['error'];
    $mime = mime_content_type($tmpName);
    $allowedMime = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];

    if ($error !== 0) {
        header('Location:../../admin/index.php?page=detail_aset&p=' . urlencode($p) . '&filter_kelompok=' . urlencode($filterKelompok) . '&filter_kategori=' . urlencode($filterKategori) . '&ma_id=' . $maId . '&action=update&error=Upload+gagal');
        exit();
    }
    if (!in_array($mime, $allowedMime, true)) {
        header('Location:../../admin/index.php?page=detail_aset&p=' . urlencode($p) . '&filter_kelompok=' . urlencode($filterKelompok) . '&filter_kategori=' . urlencode($filterKategori) . '&ma_id=' . $maId . '&action=update&error=File+tidak+valid');
        exit();
    }
    if ($size > 20000000) {
        header('Location:../../admin/index.php?page=detail_aset&p=' . urlencode($p) . '&filter_kelompok=' . urlencode($filterKelompok) . '&filter_kategori=' . urlencode($filterKategori) . '&ma_id=' . $maId . '&action=update&error=Maksimal+gambar+20+MB');
        exit();
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $namaFile = time() . '_' . preg_replace('/\s+/', '_', $namaFile);
    move_uploaded_file($tmpName, $uploadDir . $namaFile);

    if ($gambar_lama !== '') {
        $oldPath = $uploadDir . $gambar_lama;
        if (is_file($oldPath)) {
            unlink($oldPath);
        }
    }
}

$stmt = $conn->prepare('UPDATE aset SET a_master_aset_id = ?, a_file_dokumen = ?, a_tgl_perolehan = ?, a_harga_perolehan = ?, a_estimasi_harga = ?, a_lokasi_ruangan_id = ?, a_lokasi = ?, a_status_aset = ?, a_kondisi_aset = ?, a_boleh_dipinjam=?, a_keterangan = ? WHERE id = ?');
$stmt->bind_param('issddisssisi', $master_aset_id, $namaFile, $tanggal_perolehan, $harga_perolehan, $estimasi_harga, $ruangan_id, $lokasi, $status_aset, $kondisi_aset, $boleh_pinjam, $keterangan, $id);
$stmt->execute();
$stmt->close();

header('Location:../../admin/index.php?page=detail_aset&p=' . urlencode($p) . '&filter_kelompok=' . urlencode($filterKelompok) . '&filter_kategori=' . urlencode($filterKategori) . '&ma_id=' . $maId . '&action=update&success=Data+berhasil+diubah');
