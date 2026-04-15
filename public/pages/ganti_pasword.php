<?php
if (!isset($_SESSION['jemaat_id']) || empty($_SESSION['jemaat_id'])) {
    header("Location: login?error=Silahkan+login+terlebih+dahulu");
    exit();
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<main class="container-profile container mt-5 pt-5 mb-5">
    <section id="ganti-password-page">
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
                        <li class="list-group-item active">
                            <a href="ganti_pasword" class="text-decoration-none d-block">
                                <i class="fas fa-lock me-2"></i> Ganti Password
                            </a>
                        </li>
                        <li class="list-group-item p-0">
                            <div class="accordion profile-sidebar__accordion" id="accordionRiwayat">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingRiwayat">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseRiwayat"
                                            aria-expanded="false" aria-controls="collapseRiwayat">
                                            <i class="fas fa-clock-rotate-left me-2"></i> Riwayat
                                        </button>
                                    </h2>
                                    <div id="collapseRiwayat" class="accordion-collapse collapse"
                                        aria-labelledby="headingRiwayat" data-bs-parent="#accordionRiwayat">
                                        <div class="accordion-body p-0">
                                            <a href="riwayat-peminjaman-ruangan" class="profile-sidebar__child-link d-block">
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
                    <h1 class="mb-3">Ganti Password</h1>
                    <form action="actions/jemaat/ganti_password.php" method="POST">
                        <div class="mb-3">
                            <label for="password_lama" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                        </div>
                        <div class="mb-4">
                            <label for="konfirmasi_password_baru" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="konfirmasi_password_baru"
                                name="konfirmasi_password_baru" required>
                        </div>

                        <button type="submit" class="btn btn-profile-save rounded-pill px-4">
                            Simpan Password Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    <?php if ($success !== '') : ?>
        Swal.fire({
            title: "Berhasil",
            text: "<?= $success ?>",
            icon: "success",
            draggable: true
        });
    <?php endif; ?>

    <?php if ($error !== '') : ?>
        Swal.fire({
            title: "Gagal!",
            text: "<?= $error ?>",
            icon: "error",
            draggable: true
        });
    <?php endif; ?>
</script>
