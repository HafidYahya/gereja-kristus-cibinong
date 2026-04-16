<?php
if (!isset($_SESSION['jemaat_id']) || empty($_SESSION['jemaat_id'])) {
    header("Location: login?error=Silahkan+login+terlebih+dahulu");
    exit();
}

$jemaatId = (int) $_SESSION['jemaat_id'];
$dataRiwayat = [];

$stmt = $conn->prepare("
    SELECT pr.id, pr.pr_tanggal, pr.pr_jam_mulai, pr.pr_jam_selesai, pr.pr_status, pr.pr_alasan, r.r_nama
    FROM peminjaman_ruangan pr
    LEFT JOIN ruangan r ON r.id = pr.pr_ruangan_id
    WHERE pr.pr_jemaat_id = ?
    ORDER BY pr.id DESC
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
    <section id="riwayat-peminjaman-ruangan-page">
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
                                                class="profile-sidebar__child-link d-block profile-sidebar__child-link--active">
                                                Peminjaman Ruangan
                                            </a>
                                            <a href="#" class="profile-sidebar__child-link d-block">
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
                    <h1 class="mb-3">Riwayat Peminjaman Ruangan</h1>
                    <small class="text-muted">Hanya menampilkan 7 riwayat terakhir</small>
                    <?php if (empty($dataRiwayat)): ?>
                        <p class="mb-0">Belum ada riwayat peminjaman ruangan.</p>

                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle text-nowrap">
                                <thead>
                                    <tr>
                                        <th scope="col">Ruangan</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Jam</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataRiwayat as $riwayat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($riwayat['r_nama'] ?? '-') ?></td>
                                            <td><?= formatTanggalSlash($riwayat['pr_tanggal']) ?></td>
                                            <td><?= substr($riwayat['pr_jam_mulai'], 0, 5) ?> -
                                                <?= substr($riwayat['pr_jam_selesai'], 0, 5) ?>
                                            </td>

                                            <!-- STATUS -->
                                            <?php
                                            $status = $riwayat['pr_status'];
                                            $badgeClass = match ($status) {
                                                'pending' => 'bg-warning text-dark',
                                                'approved' => 'bg-success',
                                                'finish' => 'bg-primary',
                                                'cancel' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <td>
                                                <span style="min-width: 80px"
                                                    class="badge <?= $badgeClass ?>"><?= ucfirst($riwayat['pr_status']) ?></span>
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