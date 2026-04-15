<?php
include 'config/koneksi.php';
$stmt = $conn->prepare("SELECT * FROM ruangan WHERE r_is_active = 1 ORDER BY id DESC ");
$stmt->execute();
$result = $stmt->get_result();
$dataRuangan = $result->fetch_all(MYSQLI_ASSOC);
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<main class="container-ruangan container-fluid pt-4 mb-5 px-0">
    <!-- Carousel -->
    <section id="main">
        <div id="carouselIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-fixed-content text-center text-md-start">
                <h1 class="mb-2">Ruangan Gereja Kristus Cibinong</h1>
                <p class="mb-0">
                    Temukan informasi ruangan yang tersedia untuk kegiatan jemaat, pelayanan, dan acara komunitas.
                </p>
            </div>
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselIndicators" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselIndicators" data-bs-slide-to="1"
                    aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselIndicators" data-bs-slide-to="2"
                    aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="public/assets/carousel_images/carousel1.jpeg" class="d-block w-100" alt="Slide 1">
                </div>
                <div class="carousel-item">
                    <img src="public/assets/carousel_images/carousel2.jpeg" class="d-block w-100" alt="Slide 2">
                </div>
                <div class="carousel-item">
                    <img src="public/assets/carousel_images/carousel3.jpeg" class="d-block w-100" alt="Slide 3">
                </div>
            </div>
        </div>
    </section>

    <!-- RUANGAN -->

    <section id="ruangan" class="section-ruangan mt-5 mb-5 px-3 px-md-4">
        <div class="row mb-3 ">
            <?php if (empty($dataRuangan)): ?>
                <div class="col-lg-12 col-md-12 text-center mb-3">
                    <h3 class="text-danger fw-semi-bold"><i class="fas fa-info-circle"></i> Belum ada ruangan yang tersedia.
                    </h3>
                </div>
            <?php else: ?>
                <?php foreach ($dataRuangan as $ruangan): ?>
                    <div class="col-lg-4 col-md-12 col-sm-12 mb-5">
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