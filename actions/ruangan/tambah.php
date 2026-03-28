<?php
include __DIR__ . "/../../config/koneksi.php";
$nama_ruangan = trim($_POST['nama_ruangan']);
$keterangan = trim($_POST['keterangan']);
$status = trim($_POST['status']);

$stmt = $conn->prepare("SELECT * FROM ruangan WHERE r_nama=?");
$stmt->bind_param('s', $nama_ruangan);
$stmt->execute();
$result = $stmt->get_result();
$ruangan_exist = $result && $result->num_rows > 0;
$stmt->close();
if ($ruangan_exist) {
    header('Location: ../../admin/index.php?page=ruangan&action=tambah&tambah&error=Ruangan+' . $nama_ruangan . '+sudah+terdaftar');
    exit();
}

if (isset($_FILES['gambar_ruangan']) && $_FILES['gambar_ruangan']['error'] !== 4) {
    $file = $_FILES['gambar_ruangan'];
    $namaFile = $file['name'];
    $tmpName = $file['tmp_name'];
    $size = $file['size'];
    $error = $file['error'];
    $mime = mime_content_type($tmpName);
    $allowedMime = ['image/jpeg', 'image/png', 'image/jpg'];

    // Cek ekstensi

    if ($error === 0) {
        if (!in_array($mime, $allowedMime)) {
            header('Location: ../../admin/index.php?page=ruangan&action=tambah&error=File tidak valid');
            exit();
        }
        if ($size > 20000000) {
            header('Location: ../../admin/index.php?page=ruangan&action=tambah&tambah&error=Maksimal gambar 20 MB');
            exit();
        }
        move_uploaded_file($tmpName, __DIR__ . '/../../assets/uploads/ruangan/' . $namaFile);

        $stmt = $conn->prepare("INSERT INTO ruangan (r_nama, r_foto, r_keterangan, r_is_active) VALUES (?,?,?,?)");
        $stmt->bind_param('sssi', $nama_ruangan, $namaFile, $keterangan, $status);
        $stmt->execute();
        $stmt->close();
        header('Location: ../../admin/index.php?page=ruangan&action=tambah&tambah&success=Ruangan+baru+berhasil+ditambahkan');
    } else {
        header('Location: ../../admin/index.php?page=ruangan&action=tambah&error=Upload gagal');
        exit();
    }
} else {
    $namaFile = "placeholder_ruangan.jpeg";
    $stmt = $conn->prepare("INSERT INTO ruangan (r_nama, r_foto, r_keterangan, r_is_active) VALUES (?,?,?,?)");
    $stmt->bind_param('sssi', $nama_ruangan, $namaFile, $keterangan, $status);
    $stmt->execute();
    $stmt->close();
    header('Location: ../../admin/index.php?page=ruangan&action=tambah&tambah&success=Ruangan+baru+berhasil+ditambahkan');
}
