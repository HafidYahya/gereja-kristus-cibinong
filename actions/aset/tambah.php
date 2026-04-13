<?php
include __DIR__ . "/../../config/koneksi.php";
$master_aset_id = trim($_POST['master_aset_id'] ?? 0);
$tanggal_perolehan = trim($_POST['tanggal_perolehan'] ?? null);
$harga_perolehan = trim($_POST['harga_perolehan'] ?? 0);
$estimasi_harga = trim($_POST['estimasi_harga'] ?? 0);
$ruangan_id = $_POST['ruangan_id'] !== '' ? (int)$_POST['ruangan_id'] : null;
$lokasi = trim($_POST['lokasi'] ?? '');
$status_aset = trim($_POST['status_aset'] ?? '');
$kondisi_aset = trim($_POST['kondisi_aset'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');



if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] !== 4) {
    $file = $_FILES['file_dokumen'];
    $namaFile = $file['name'];
    $tmpName = $file['tmp_name'];
    $size = $file['size'];
    $error = $file['error'];
    $mime = mime_content_type($tmpName);
    $allowedMime = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $namaFile = time() . '_' . $namaFile; // Tambahkan timestamp untuk menghindari nama file yang sama
    // Cek ekstensi

    if ($error === 0) {
        if (!in_array($mime, $allowedMime)) {
            header('Location: ../../admin/index.php?page=aset&action=tambah&error=File tidak valid');
            exit();
        }
        if ($size > 20000000) {
            header('Location: ../../admin/index.php?page=aset&action=tambah&error=Maksimal gambar 20 MB');
            exit();
        }
        move_uploaded_file($tmpName, __DIR__ . '/../../assets/uploads/dokumen_file/' . $namaFile);

        $stmt = $conn->prepare("INSERT INTO aset (a_master_aset_id, a_file_dokumen, a_tgl_perolehan, a_harga_perolehan, a_estimasi_harga, a_lokasi_ruangan_id, a_lokasi, a_status_aset, a_kondisi_aset, a_keterangan) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('issddissss', $master_aset_id, $namaFile, $tanggal_perolehan, $harga_perolehan, $estimasi_harga, $ruangan_id, $lokasi, $status_aset, $kondisi_aset, $keterangan);
        $stmt->execute();
        $stmt->close();
        header('Location: ../../admin/index.php?page=aset&action=tambah&success=Aset+baru+berhasil+ditambahkan');
    } else {
        header('Location: ../../admin/index.php?page=aset&action=tambah&error=Upload gagal');
        exit();
    }
} else {
    $stmt = $conn->prepare("INSERT INTO aset (a_master_aset_id, a_tgl_perolehan, a_harga_perolehan, a_estimasi_harga, a_lokasi_ruangan_id, a_lokasi, a_status_aset, a_kondisi_aset, a_keterangan) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('isddissss', $master_aset_id, $tanggal_perolehan, $harga_perolehan, $estimasi_harga, $ruangan_id, $lokasi, $status_aset, $kondisi_aset, $keterangan);
    $stmt->execute();
    $stmt->close();
    header('Location: ../../admin/index.php?page=aset&action=tambah&success=Aset+baru+berhasil+ditambahkan');
}
