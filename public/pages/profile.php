<?php
if (!isset($_SESSION['jemaat_id']) || empty($_SESSION['jemaat_id'])) {
    header("Location: login?error=Silahkan+login+terlebih+dahulu");
    exit();
}

$jemaatId = (int) $_SESSION['jemaat_id'];
$dataProfile = null;

$stmt = $conn->prepare("SELECT id, j_nama, j_no_hp, j_email, j_alamat, j_foto FROM jemaat WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $jemaatId);
$stmt->execute();
$result = $stmt->get_result();
$dataProfile = $result->fetch_assoc();
$stmt->close();

if (!$dataProfile) {
    header("Location: login?error=Data+profile+tidak+ditemukan");
    exit();
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<main class="container-profile container mt-5 pt-5 mb-5">
    <section id="profile-page">
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
                        <li class="list-group-item active">
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
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseRiwayat"
                                            aria-expanded="false" aria-controls="collapseRiwayat">
                                            <i class="fas fa-clock-rotate-left me-2"></i> Riwayat
                                        </button>
                                    </h2>
                                    <div id="collapseRiwayat" class="accordion-collapse collapse"
                                        aria-labelledby="headingRiwayat" data-bs-parent="#accordionRiwayat">
                                        <div class="accordion-body p-0">
                                            <a href="riwayat-peminjaman-ruangan"
                                                class="profile-sidebar__child-link d-block">
                                                Peminjaman Ruangan
                                            </a>
                                            <a href="riwayat-peminjaman-aset"
                                                class="profile-sidebar__child-link d-block">
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
                    <h1 class="mb-3">Informasi Pribadi</h1>
                    <form action="actions/jemaat/edit_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= (int) $dataProfile['id'] ?>">

                        <div class="profile-form__photo mb-3">
                            <img id="foto_preview" class="profile-form__preview"
                                src="assets/uploads/jemaat/<?= htmlspecialchars($dataProfile['j_foto'] ?? 'profile-default.jpg') ?>"
                                alt="Preview Foto Profile">
                            <label class="btn btn-outline-primary mt-2 mb-0">
                                Ubah Foto
                                <input id="foto_profile" name="foto_profile" type="file" accept="image/*"
                                    class="d-none">
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="<?= htmlspecialchars($dataProfile['j_nama'] ?? '') ?>" required>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label for="no_hp" class="form-label">Nomor Handphone</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp"
                                    value="<?= htmlspecialchars($dataProfile['j_no_hp'] ?? '') ?>" required>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($dataProfile['j_email'] ?? '') ?>" required>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                    required><?= htmlspecialchars($dataProfile['j_alamat'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-profile-save rounded-pill px-4">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById("no_hp").addEventListener("input", function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
const fotoInput = document.getElementById("foto_profile");
const fotoPreview = document.getElementById("foto_preview");

if (fotoInput) {
    fotoInput.addEventListener("change", function() {
        const file = this.files[0];
        if (file) {
            fotoPreview.src = URL.createObjectURL(file);
        }
    });
}
</script>
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