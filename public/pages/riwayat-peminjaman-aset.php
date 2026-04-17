<?php
if (!isset($_SESSION['jemaat_id']) || empty($_SESSION['jemaat_id'])) {
    header("Location: login?error=Silahkan+login+terlebih+dahulu");
    exit();
}

$jemaatId = (int) $_SESSION['jemaat_id'];
$dataRiwayat = [];

$stmt = $conn->prepare("
    SELECT 
        pa.id AS pa_id, 
        pa.pa_tgl_pinjam, 
        pa.pa_tgl_kembali, 
        pa.pa_alasan_ditolak, 
        pa.pa_status,
        
        ma.id AS ma_id,
        ma.ma_nama,

        COUNT(a.id) AS qty

    FROM peminjaman_aset pa

    LEFT JOIN detail_peminjaman_aset dpa 
        ON dpa.dpa_peminjaman_aset_id = pa.id

    LEFT JOIN aset a 
        ON dpa.dpa_aset_id = a.id

    LEFT JOIN master_aset ma 
        ON a.a_master_aset_id = ma.id
        
    WHERE pa.pa_jemaat_id = ?

    GROUP BY pa.id, ma.id
    
    ORDER BY pa.id DESC
    LIMIT 7
");
$stmt->bind_param("i", $jemaatId);
$stmt->execute();
$result = $stmt->get_result();
$dataRiwayat = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function formatTanggalSlash(string $tanggal): string
{
    $timestamp = strtotime($tanggal);
    if ($timestamp === false) {
        return $tanggal;
    }

    return date('d/m/Y', $timestamp);
}
?>

<main class="container-profile container mt-5 pt-5 mb-5">
    <section id="riwayat-peminjaman-aset-page">
        <div class="row g-4">
            <div class="col-lg-4 col-xl-3">
                <aside class="profile-sidebar">
                    <div class="profile-sidebar__header d-flex align-items-center gap-3 mb-3">
                        <img src="assets/uploads/jemaat/<?= htmlspecialchars($_SESSION['jemaat_foto'] ?? 'profile-default.jpg') ?>"
                            alt="Foto Profile" class="profile-sidebar__avatar">
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($_SESSION['jemaat_name'] ?? 'Jemaat') ?></h5>
                            <small><?= htmlspecialchars($_SESSION['jemaat_email'] ?? '-') ?></small>
                        </div>
                    </div>

                    <ul class="list-group profile-sidebar__menu">
                        <li class="list-group-item">
                            <a href="profile" class="text-decoration-none d-block">
                                <i class="fas fa-user me-2"></i> Informasi Pribadi
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="ganti_pasword" class="text-decoration-none d-block">
                                <i class="fas fa-lock me-2"></i> Ganti Password
                            </a>
                        </li>
                        <li class="list-group-item p-0">
                            <div class="accordion profile-sidebar__accordion" id="accordionRiwayat">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingRiwayat">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseRiwayat" aria-expanded="true"
                                            aria-controls="collapseRiwayat">
                                            <i class="fas fa-clock-rotate-left me-2"></i> Riwayat
                                        </button>
                                    </h2>
                                    <div id="collapseRiwayat" class="accordion-collapse collapse show"
                                        aria-labelledby="headingRiwayat" data-bs-parent="#accordionRiwayat">
                                        <div class="accordion-body p-0">
                                            <a href="riwayat-peminjaman-ruangan"
                                                class="profile-sidebar__child-link d-block">
                                                Peminjaman Ruangan
                                            </a>
                                            <a href="riwayat-peminjaman-aset"
                                                class="profile-sidebar__child-link d-block profile-sidebar__child-link--active">
                                                Peminjaman Aset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <a href="logout" class="text-decoration-none d-block text-danger">
                                <i class="fas fa-right-from-bracket me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>

            <div class="col-lg-8 col-xl-9">
                <div class="profile-content">
                    <h1 class="mb-3">Riwayat Peminjaman Aset</h1>
                    <small class="text-muted">Hanya menampilkan 7 riwayat terakhir</small>
                    <?php if (empty($dataRiwayat)): ?>
                    <p class="mb-0">Belum ada riwayat peminjaman aset.</p>

                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle text-nowrap">
                            <thead>
                                <tr>
                                    <th scope="col">Nama Aset</th>
                                    <th scope="col">QTY</th>
                                    <th scope="col">Tanggal Pinjam</th>
                                    <th scope="col">Tanggal Kembali</th>
                                    <th scope="col">Alasan Ditolak</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataRiwayat as $riwayat): ?>
                                <tr>
                                    <!-- Nama Aset  -->
                                    <td>
                                        <?= htmlspecialchars($riwayat['ma_nama'] ?? '') ?>
                                    </td>

                                    <td><?= htmlspecialchars($riwayat['qty'] ?? 0) ?></td>

                                    <!-- Tanggal Pinjam -->
                                    <td>
                                        <?php if (empty($riwayat['pa_tgl_pinjam']) && $riwayat['pa_status'] === 'pending') : ?>
                                        <small class="text-muted">Belum dikonfirmasi</small>
                                        <?php elseif (empty($riwayat['pa_tgl_pinjam']) && $riwayat['pa_status'] === 'ditolak'): ?>
                                        <small class="text-muted">-</small>
                                        <?php else: ?>
                                        <?= formatTanggalSlash($riwayat['pa_tgl_pinjam']) ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Tanggal Kembali -->
                                    <td>
                                        <?php if ($riwayat['pa_status'] === 'disetujui') : ?>
                                        <small class="text-muted">Belum dikembalikan</small>
                                        <?php else : ?>
                                        <?= $riwayat['pa_tgl_kembali']
                                                        ? formatTanggalSlash($riwayat['pa_tgl_kembali'])
                                                        : '-' ?>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= htmlspecialchars($riwayat['pa_alasan_ditolak'] ?? "") ?></td>

                                    <!-- STATUS -->
                                    <?php
                                            $status = $riwayat['pa_status'];
                                            $badgeClass = match ($status) {
                                                'pending' => 'bg-warning text-dark',
                                                'disetujui' => 'bg-success',
                                                'dikembalikan' => 'bg-primary',
                                                'ditolak' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>