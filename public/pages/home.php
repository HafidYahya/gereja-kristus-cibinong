<?php
include 'config/koneksi.php';
$stmt = $conn->prepare("SELECT * FROM ruangan WHERE r_is_active = 1 ORDER BY id DESC LIMIT 3 ");
$stmt->execute();
$result = $stmt->get_result();
$dataRuangan = $result->fetch_all(MYSQLI_ASSOC);
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<main class="container-home container mt-5 pt-5 mb-5">
    <section id="main">
        <div class="row mb-3">
            <div class="col-lg-6 col-md-12 align-self-center">
                <h1 class="display-4">Selamat Datang</h1>
                <p>Ini adalah halaman utama situs web Gereja Kristus Cibinong. Di sini Anda dapat menemukan
                    ruangan atau aset yang dapat anda pinjam </p>
            </div>
            <div class="col-lg-6 col-md-12">
                <img src="public/assets/images/main.jpg" alt="Gereja Kristus Cibinong" class="img-fluid">
            </div>
        </div>
    </section>

    <section id="ruangan" class="section-ruangan mt-5 mb-5">
        <div class="row mb-3">
            <div class="col-lg-12 col-md-12 align-self-center">
                <h2 class="text-center">RUANGAN</h2>
                <p class="text-center">Berikut adalah daftar ruangan gereja yang tersedia untuk dipinjam: </p>
            </div>
        </div>
        <div class="row mb-3 ">
            <?php if (empty($dataRuangan)): ?>
                <div class="col-lg-12 col-md-12 text-center mb-3">
                    <h3 class="text-danger fw-semi-bold"><i class="fas fa-info-circle"></i> Belum ada ruangan yang tersedia.
                    </h3>
                </div>
            <?php else: ?>
                <?php foreach ($dataRuangan as $ruangan): ?>
                    <div class="col-lg-4 col-md-12 col-sm-12 mb-3">
                        <div class="card card-ruangan shadow-md mx-auto"
                            onclick="window.location='detail-ruangan?r=<?= $ruangan['id'] ?>'">
                            <img src="assets/uploads/ruangan/<?= $ruangan['r_foto'] ?>" class="card-img-top shadow"
                                alt="<?= $ruangan['r_nama'] ?>">
                            <div class="card-body">
                                <h5 class="card-title text-center"><?= ucwords($ruangan['r_nama']) ?></h5>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mb-3 text-center">
            <a href="ruangan" class="btn btn-selengkapnya rounded-pill">Selengkapnya <i
                    class="fas fa-angles-right"></i></a>
        </div>
    </section>

    <!-- SECTION LOKASI -->
    <section id="lokasi" class="section-lokasi mt-5 mb-5">
        <div class="row align-items-center">

            <!-- MAP -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="ratio ratio-4x3 shadow rounded">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.426963607862!2d106.85099027355814!3d-6.467472863233527!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69c1d0a9671243%3A0x5ef670fe0fcbba9c!2sGereja%20Kristus%20Cibinong!5e0!3m2!1sen!2sid!4v1776173761702!5m2!1sen!2sid"
                        style="border:0;" allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
            </div>

            <!-- INFO -->
            <div class="col-lg-6 col-md-12">
                <h2 class="mb-3">Lokasi Kami</h2>
                <p>
                    Gereja Kristus Cibinong berlokasi strategis dan mudah diakses oleh jemaat maupun pengunjung.
                    Anda dapat menggunakan peta di samping untuk menemukan lokasi kami dengan lebih mudah.
                </p>

                <ul class="lokasi-list mt-3">
                    <li class="lokasi-item">
                        <span class="lokasi-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <div>
                            <div class="lokasi-label">Lokasi</div>
                            <div class="lokasi-text">Jl. Raya Cibinong, Cibinong, Bogor, Jawa Barat</div>
                        </div>
                    </li>
                    <li class="lokasi-item">
                        <span class="lokasi-icon"><i class="fas fa-clock"></i></span>
                        <div>
                            <div class="lokasi-label">Jam Operasional</div>
                            <div class="lokasi-text">Setiap hari menyesuaikan waktu ibadah</div>
                        </div>
                    </li>
                    <li class="lokasi-item">
                        <span class="lokasi-icon"><i class="fas fa-phone"></i></span>
                        <div>
                            <div class="lokasi-label">Kontak</div>
                            <div class="lokasi-text">-</div>
                        </div>
                    </li>
                    <a href="https://www.google.com/maps?q=Gereja+Kristus+Cibinong" target="_blank"
                        class="btn mt-3 rounded-pill btn-selengkapnya">
                        Lihat di Google Maps
                    </a>
                </ul>


            </div>

        </div>
    </section>

</main>
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