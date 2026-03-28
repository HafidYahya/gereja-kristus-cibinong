<?php
include __DIR__ . "/../../config/koneksi.php";

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nama_ruangan = trim($_POST['nama_ruangan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$status = isset($_POST['status']) ? (int) $_POST['status'] : 0;
$gambar_lama = trim($_POST['gambar_lama'] ?? '');

if ($id <= 0 || $nama_ruangan === '') {
    header('Location:../../admin/index.php?page=ruangan&action=update&error=Data+tidak+boleh+kosong');
    exit();
}

$stmt = $conn->prepare('SELECT id FROM ruangan WHERE r_nama = ? AND id <> ? LIMIT 1');
$stmt->bind_param('si', $nama_ruangan, $id);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

if ($exists) {
    header('Location:../../admin/index.php?page=ruangan&action=update&error=Nama+ruangan+sudah+terdaftar');
    exit();
}

$namaFile = $gambar_lama;
$uploadDir = __DIR__ . '/../../assets/uploads/ruangan/';

if (isset($_FILES['gambar_ruangan']) && $_FILES['gambar_ruangan']['error'] !== 4) {
    $file = $_FILES['gambar_ruangan'];
    $namaFile = $file['name'];
    $tmpName = $file['tmp_name'];
    $size = $file['size'];
    $error = $file['error'];
    $mime = mime_content_type($tmpName);
    $allowedMime = ['image/jpeg', 'image/png', 'image/jpg'];

    if ($error !== 0) {
        header('Location:../../admin/index.php?page=ruangan&action=update&error=Upload+gagal');
        exit();
    }
    if (!in_array($mime, $allowedMime, true)) {
        header('Location:../../admin/index.php?page=ruangan&action=update&error=File+tidak+valid');
        exit();
    }
    if ($size > 20000000) {
        header('Location:../../admin/index.php?page=ruangan&action=update&error=Maksimal+gambar+20+MB');
        exit();
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    move_uploaded_file($tmpName, $uploadDir . $namaFile);

    if ($gambar_lama !== '' && $gambar_lama !== 'placeholder_ruangan') {
        $oldPath = $uploadDir . $gambar_lama;
        if (is_file($oldPath)) {
            unlink($oldPath);
        }
    }
}

$stmt = $conn->prepare('UPDATE ruangan SET r_nama = ?, r_foto = ?, r_keterangan = ?, r_is_active = ? WHERE id = ?');
$stmt->bind_param('sssii', $nama_ruangan, $namaFile, $keterangan, $status, $id);
$stmt->execute();
$stmt->close();

header('Location:../../admin/index.php?page=ruangan&action=update&success=Data+berhasil+diubah');

