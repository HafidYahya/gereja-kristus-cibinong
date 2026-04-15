<?php
if (isset($_SESSION['jemaat_id'])) {
    header("Location: /gerejakristuscibinong/");
    exit();
}
include __DIR__ . "/../../config/koneksi.php";
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>


<main class="container-register container mt-5 pt-5">
    <section id="main">
        <div class="row">
            <div class="col-12 align-self-center">
                <h1 class="display-4 text-center">Daftar Akun</h1>
                <p class="text-center">Silahkan isi biodata anda pada form dibawah ini: </p>
            </div>
        </div>
        <div class="row">
            <form method="post" action="actions/jemaat/tambah.php" enctype="multipart/form-data" class="row g-4">
                <div class="col-lg-4 col-md-5">
                    <div class="register-profile">
                        <img id="foto_preview" class="register-profile__preview"
                            src="assets/uploads/jemaat/profile-default.jpg" alt="Preview Foto Profile">
                        <label class="btn btn-outline-primary w-100 register-profile__button">
                            Pilih Foto
                            <input id="foto_profile" name="foto_profile" type="file" accept="image/*"
                                class="register-profile__input">
                        </label>
                        <small class="text-muted text-center">Format JPG/PNG, maksimal 2MB.</small>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama*</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukan nama lengkap"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukan email"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password*</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukan password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password*</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            placeholder="Masukan konfirmasi password" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Nomor Handphone*</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp" inputmode="numeric"
                            placeholder="Contoh: 08123456789" required>

                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat*</label>
                        <textarea class="form-control" id="alamat" name="alamat" placeholder="Masukan alamat lengkap"
                            rows="3" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-register-now rounded-pill">Daftar Sekarang</button>
                </div>
            </form>
        </div>
    </section>

</main>
<!-- Validasi Input No HP -->
<script>
    document.getElementById("no_hp").addEventListener("input", function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    const fotoInput = document.getElementById("foto_profile");
    const fotoPreview = document.getElementById("foto_preview");
    let previewUrl = null;

    fotoInput.addEventListener("change", function() {
        const file = this.files && this.files[0];
        if (!file) {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
            fotoPreview.src = "public/assets/images/profile-default.jpg";
            return;
        }

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        previewUrl = URL.createObjectURL(file);
        fotoPreview.src = previewUrl;
    });
</script>

<!-- Sweet Alert -->
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