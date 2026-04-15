<?php
include 'config/koneksi.php';

$idRuangan = isset($_GET['r']) ? (int) $_GET['r'] : 0;
$dataRuangan = null;

if ($idRuangan > 0) {
    $stmt = $conn->prepare("SELECT id, r_nama, r_foto, r_keterangan FROM ruangan WHERE id = ? AND r_is_active = 1 LIMIT 1");
    $stmt->bind_param("i", $idRuangan);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataRuangan = $result->fetch_assoc();
}
?>

<main class="container-detail-ruangan container mt-5 pt-5 mb-5">
    <section id="detail-ruangan">
        <?php if (!$dataRuangan): ?>
        <div class="alert alert-warning text-center" role="alert">
            Data ruangan tidak ditemukan atau sudah tidak tersedia.
        </div>
        <div class="text-center mt-3">
            <a href="ruangan" class="btn btn-outline-secondary rounded-pill px-4">
                Kembali ke daftar ruangan
            </a>
        </div>
        <?php else: ?>
        <div class="row g-4 align-items-start">
            <div class="col-lg-6 col-md-12">
                <div class="detail-ruangan__image-wrap shadow-sm">
                    <img src="assets/uploads/ruangan/<?= htmlspecialchars($dataRuangan['r_foto']) ?>"
                        alt="<?= htmlspecialchars($dataRuangan['r_nama']) ?>" class="img-fluid detail-ruangan__image">
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="detail-ruangan__content">
                    <h1 class="mb-3"><?= ucwords(htmlspecialchars($dataRuangan['r_nama'])) ?></h1>
                    <p class="mb-4">
                        <?= nl2br(htmlspecialchars($dataRuangan['r_keterangan'] ?? '-')) ?>
                    </p>
                    <?php if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])): ?>
                    <a href="login" class="btn btn-pinjam-ruangan rounded-pill px-4 py-2">
                        Pinjam Ruangan
                    </a>
                    <?php endif; ?>
                    <!-- ELSE MODAL PINJAM -->
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>
</main>