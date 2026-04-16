<?php
include "../../config/koneksi.php";
$jemaatId   = $_POST['jemaat_id'];
$master_aset_id = $_POST['master_aset'];
$qty = $_POST['qty'];

// MENDAPATKAN STOK TERSEDIA
$sql_stok = "SELECT COUNT(id) AS stok FROM aset 
    WHERE a_master_aset_id = ? 
    AND a_boleh_dipinjam = 1 
    AND a_status_aset IN ('terpakai', 'tidak_terpakai') 
    AND a_kondisi_aset NOT IN ('rusak_berat', 'rusak_sedang')";

$stmt = $conn->prepare($sql_stok);
$stmt->bind_param("i", $master_aset_id);
$stmt->execute();
$result = $stmt->get_result();
$stok = $result->fetch_assoc()['stok'];


// CEK DATA YANG DITERIMA
if (empty($master_aset_id) || empty($qty)) {
    header("Location: /gerejakristuscibinong/request-aset?error=Data+tidak+lengkap");
    exit();
}

// CEK QTY MIN 1
if ($qty <= 0) {
    header("Location: /gerejakristuscibinong/request-aset?error=Minimal+QTY+1+untuk+melakukan+pengajuan");
    exit();
}

// VALIDASI STOK YANG DAPAT DIAJUKAN
if ($qty > $stok) {
    header("Location: /gerejakristuscibinong/request-aset?error=QTY+yang+dapat+diajukan+untuk+aset+ini+adalah+$stok");
    exit();
}

// MULAI TRANSACTION    
$conn->begin_transaction();

try {

    // 1. INSERT KE PEMINJAMAN (HEADER)
    $sql_insert_peminjaman = "INSERT INTO peminjaman_aset (pa_jemaat_id, pa_tgl_pinjam, pa_tgl_kembali, pa_status, pa_alasan_ditolak) 
                             VALUES (?, NULL, NULL, 'pending', NULL)";

    $stmt = $conn->prepare($sql_insert_peminjaman);
    $stmt->bind_param("i", $jemaatId);
    $stmt->execute();

    // ambil id peminjaman
    $peminjaman_id = $conn->insert_id;


    // 2. AMBIL ASET YANG TERSEDIA SESUAI QTY
    $sql_ambil_aset = "SELECT id FROM aset 
        WHERE a_master_aset_id = ? 
        AND a_boleh_dipinjam = 1 
        AND a_status_aset IN ('terpakai', 'tidak_terpakai') 
        AND a_kondisi_aset NOT IN ('rusak_berat', 'rusak_sedang')
        LIMIT ?";

    $stmt = $conn->prepare($sql_ambil_aset);
    $stmt->bind_param("ii", $master_aset_id, $qty);
    $stmt->execute();
    $result = $stmt->get_result();

    $aset_ids = [];
    while ($row = $result->fetch_assoc()) {
        $aset_ids[] = $row['id'];
    }


    // 3. INSERT KE DETAIL + UPDATE STATUS ASET
    $sql_insert_detail = "INSERT INTO detail_peminjaman_aset (dpa_peminjaman_aset_id, dpa_aset_id) VALUES (?, ?)";
    $stmt_detail = $conn->prepare($sql_insert_detail);

    foreach ($aset_ids as $aset_id) {
        // insert detail
        $stmt_detail->bind_param("ii", $peminjaman_id, $aset_id);
        $stmt_detail->execute();
    }


    // 4. COMMIT atau PUSH QUERY
    $conn->commit();

    header("Location: /gerejakristuscibinong/request-aset?success=Pengajuan+berhasil");
    exit();
} catch (Exception $e) {

    // kalau gagal rollback
    $conn->rollback();

    header("Location: /gerejakristuscibinong/request-aset?error=Terjadi+kesalahan");
    exit();
}
