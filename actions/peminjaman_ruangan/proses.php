<?php
include "../../config/koneksi.php";

session_start();

// ambil data
$id     = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$filter = $_POST['filter'] ?? '';

// validasi dasar
if (!$id || !$status) {
    header("Location: ../../admin?page=approval_ruangan&error=Data+tidak+valid");
    exit;
}

// validasi status yang diperbolehkan
$allowedStatus = ['approved', 'cancel', 'finish'];
if (!in_array($status, $allowedStatus)) {
    header("Location: ../../admin?page=approval_ruangan&filter=$filter&error=Status+tidak+valid");
    exit;
}

// ambil data lama (biar aman)
$stmt = $conn->prepare("SELECT pr_status FROM peminjaman_ruangan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: ../../admin?page=approval_ruangan&filter=$filter&error=Data+tidak+ditemukan");
    exit;
}


// ================== UPDATE ==================
$stmt = $conn->prepare("
    UPDATE peminjaman_ruangan 
    SET pr_status = ?
    WHERE id = ?
");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    header("Location: ../../admin?page=approval_ruangan&filter=$filter&success=Status+berhasil+diperbarui");
} else {
    header("Location: ../../admin?page=approval_ruangan&filter=$filter&error=Gagal+memperbarui+status");
}
