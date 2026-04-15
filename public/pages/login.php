<?php
include 'config/koneksi.php';
if (isset($_SESSION['jemaat_id'])) {
    header("Location: /gerejakristuscibinong/");
    exit();
}
// cek apakah form login telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // query untuk mencari pengguna dengan email yang sesuai
    $stmt = $conn->prepare("SELECT * FROM jemaat WHERE j_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    // Cek apakah pengguna ditemukan
    if ($result->num_rows > 0) {
        $jemaat = $result->fetch_assoc();
        // cek apakah pengguna aktif
        if ($jemaat['j_is_active'] === (int) 0) {
            header("Location: login?error=Akun+anda+tidak+aktif.+Silahkan+hubungi+admin+untuk+mengaktifkan+akun+anda.");
            exit();
        }
        // Verifikasi password
        if (password_verify($password, $jemaat['j_password'])) {
            // Simpan informasi pengguna dalam session
            $_SESSION['jemaat_id'] = $jemaat['id'];
            $_SESSION['jemaat_name'] = $jemaat['j_nama'];
            $_SESSION['jemaat_email'] = $jemaat['j_email'];
            $_SESSION['jemaat_no_hp'] = $jemaat['j_no_hp'];
            $_SESSION['jemaat_alamat'] = $jemaat['j_alamat'];
            $_SESSION['jemaat_foto'] = $jemaat['j_foto'];
            // Redirect ke halaman utama atau dashboard
            header("Location: /gerejakristuscibinong?success=Login+berhasil.+Selamat+datang+di+Gereja+Kristus+Cibinong.");
            exit();
        } else {
            header("Location: login?error=Password+yang+anda+masukan+salah.+Silahkan+coba+lagi.");
            exit();
        }
    } else {
        header("Location: login?error=Email+tidak+ditemukan.+Silahkan+coba+lagi.");
        exit();
    }
}
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<main class="container-login container mt-5 pt-5">
    <section id="main">
        <div class="row">
            <div class="col-lg-6 col-md-12 align-self-center">
                <h1 class="display-4">Selamat Datang Kembali</h1>
                <p>Silahkan login untuk mengakses fitur-fitur yang tersedia. </p>
                <form method="POST">
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
                    <div class="form-check">
                        <input type="checkbox" name="showPassword" class="form-check-input" id="showPassword">
                        <label class="form-check-label" for="showPassword">
                            Tampilkan Password
                        </label>
                    </div>
                    <button type="submit" class="btn btn-login w-100 mt-3 mb-1">Login</button>
                    <a href="register" class="text-primary mb-4" style="text-decoration: none;">Belum punya akun? Daftar
                        sekarang</a>
                </form>
            </div>
            <div class="d-none d-lg-block col-lg-6">
                <img src="public/assets/images/main.jpg" alt="Gereja Kristus Cibinong" class="img-fluid">
            </div>
        </div>
    </section>

</main>
<script>
    const showPasswordCheckbox = document.getElementById('showPassword');
    const passwordInput = document.getElementById('password');

    showPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
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