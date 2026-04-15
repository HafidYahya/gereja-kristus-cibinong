<?php
include "../../config/koneksi.php";
$jemaatId   = $_POST['jemaat_id'];
$ruangan_id = $_POST['ruangan_id'];
$tanggal    = $_POST['tanggal'];
$mulai      = $_POST['jam_mulai'];
$selesai    = $_POST['jam_selesai'];
$status     = $_POST['status'];
$alasan     = $_POST['alasan'];

// CEK DATA YANG DITERIMA

if (empty($ruangan_id) || empty($tanggal) || empty($mulai) || empty($selesai) || empty($alasan)) {
    header("Location: /gerejakristuscibinong/detail-ruangan?r=$ruangan_id&error=Data+tidak+lengkap");
    exit();
}

// CEK KONFLIK PEMINJAMAN
$stmt = $conn->prepare("
    SELECT * FROM peminjaman_ruangan 
    WHERE pr_ruangan_id = ?
    AND pr_tanggal = ?
    AND (? < pr_jam_selesai AND ? > pr_jam_mulai)
");

$stmt->bind_param("isss", $ruangan_id, $tanggal, $mulai, $selesai);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    header("Location: /gerejakristuscibinong/detail-ruangan?r=$ruangan_id&error=Jadwal+sudah+dibooking+pada+tanggal+dan+jam+tersebut");
    exit();
}

// INSERT DATA
$stmt = $conn->prepare("INSERT INTO peminjaman_ruangan (pr_jemaat_id, pr_ruangan_id, pr_tanggal, pr_jam_mulai, pr_jam_selesai, pr_alasan, pr_status) VALUES (?,?,?,?,?,?,?) ");
$stmt->bind_param('iisssss', $jemaatId, $ruangan_id, $tanggal, $mulai, $selesai, $alasan, $status);
if ($stmt->execute()) {
    header("Location: /gerejakristuscibinong/detail-ruangan?r=$ruangan_id&success=Pengajuan+berhasil,+silahkan+cek+riwayat+untuk+melihat+status+pengajuan+anda.");
} else {
    header("Location: /gerejakristuscibinong/detail-ruangan?r=$ruangan_id&error=Pengajuan+gagal,+silahkan+coba+pengajuan+lagi");
}
