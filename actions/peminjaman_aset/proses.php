<?php
include "../../config/koneksi.php";

session_start();

// ambil data
$id     = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$filter = $_POST['filter'] ?? '';
$alasan_ditolak = $_POST['alasan_ditolak'] ?? '';

// validasi dasar
if (!$id || !$status) {
    header("Location: ../../admin?page=approval_aset&error=Data+tidak+valid");
    exit;
}

// validasi status yang diperbolehkan
$allowedStatus = ['disetujui', 'ditolak', 'dikembalikan'];
if (!in_array($status, $allowedStatus)) {
    header("Location: ../../admin?page=approval_aset&filter=$filter&error=Status+tidak+valid");
    exit;
}

// ambil data lama (biar aman)
$stmt = $conn->prepare("SELECT pa_status FROM peminjaman_aset WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: ../../admin?page=approval_aset&filter=$filter&error=Data+tidak+ditemukan");
    exit;
}

// VALIDASI WAJIB INPUT ALASAN JIKA DITOLAK
if ($status === 'ditolak' && $alasan_ditolak === '') {
    header("Location: ../../admin?page=approval_aset&filter=$filter&error=Alasan+ditolak+harus+diisi");
    exit();
}

// TRANSAKSI
if ($status === 'disetujui') {
    $conn->begin_transaction();

    try {

        $peminjaman_id = $_POST['id'];

        // 1. ambil semua aset dari detail
        $sql = "SELECT dpa_aset_id FROM detail_peminjaman_aset 
            WHERE dpa_peminjaman_aset_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $peminjaman_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $aset_ids = [];
        while ($row = $result->fetch_assoc()) {
            $aset_ids[] = $row['dpa_aset_id'];
        }

        // 2. update status aset jadi dipinjam
        $sql_update_aset = "UPDATE aset SET a_this_dipinjam = 1 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update_aset);

        foreach ($aset_ids as $aset_id) {
            $stmt_update->bind_param("i", $aset_id);
            $stmt_update->execute();
        }

        // 3. update status peminjaman
        $sql_update_peminjaman = "UPDATE peminjaman_aset 
                             SET pa_status = 'disetujui',
                             pa_tgl_pinjam = NOW() 
                             WHERE id = ?";
        $stmt = $conn->prepare($sql_update_peminjaman);
        $stmt->bind_param("i", $peminjaman_id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../../admin?page=approval_aset&filter=$filter&error=Terjadi+kesalahan");
        exit();
    }
    header("Location: ../../admin?page=approval_aset&filter=$filter&success=Berhasil+disetujui");
    exit();
} elseif ($status === 'ditolak') {
    $peminjaman_id = $_POST['id'];
    $alasan = $_POST['alasan_ditolak'] ?? '';
    $sql = "UPDATE peminjaman_aset 
        SET pa_status = 'ditolak', pa_alasan_ditolak = ? 
        WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $alasan, $peminjaman_id);
    $stmt->execute();
    header("Location: ../../admin?page=approval_aset&filter=$filter&success=Berhasil+ditolak");
    exit();
} else {
    $conn->begin_transaction();

    try {

        $peminjaman_id = $_POST['id'];

        // 1. ambil aset
        $sql = "SELECT dpa_aset_id FROM detail_peminjaman_aset 
            WHERE dpa_peminjaman_aset_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $peminjaman_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $aset_ids = [];
        while ($row = $result->fetch_assoc()) {
            $aset_ids[] = $row['dpa_aset_id'];
        }

        // 2. update aset jadi tersedia
        $sql_update = "UPDATE aset SET a_this_dipinjam = 0 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);

        foreach ($aset_ids as $aset_id) {
            $stmt_update->bind_param("i", $aset_id);
            $stmt_update->execute();
        }

        // 3. update status peminjaman
        $sql_update_peminjaman = "UPDATE peminjaman_aset 
                             SET pa_status = 'dikembalikan',
                                 pa_tgl_kembali = NOW()
                             WHERE id = ?";
        $stmt = $conn->prepare($sql_update_peminjaman);
        $stmt->bind_param("i", $peminjaman_id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../../admin?page=approval_aset&filter=$filter&error=Terjadi+kesalahan");
        exit();
    }
    header("Location: ../../admin?page=approval_aset&filter=$filter&success=Berhasil+dikembalikan");
    exit();
}
