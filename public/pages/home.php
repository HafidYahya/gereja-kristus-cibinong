<?php
include 'config/koneksi.php';
$stmt = $conn->prepare("SELECT * FROM ruangan WHERE r_is_active = 1 ORDER BY id DESC LIMIT 3 ");
$stmt->execute();
$result = $stmt->get_result();
$dataRuangan = $result->fetch_all(MYSQLI_ASSOC);
?>

<main class="container-home container mt-5 pt-5">
    <section id="main">
        <div class="row">
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

    <section id="ruangan" class="section-ruangan mt-5">
        <div class="row mb-3">
            <div class="col-lg-12 col-md-12 align-self-center">
                <h2 class="text-center">RUANGAN</h2>
                <p class="text-center">Berikut adalah daftar ruangan gereja yang tersedia untuk dipinjam: </p>
            </div>
        </div>
        <div class="row mb-3 ">
            <?php foreach ($dataRuangan as $ruangan): ?>
                <div class="col-lg-4 col-md-12 col-sm-12 mb-3">
                    <div class="card shadow-md mx-auto">
                        <img src="assets/uploads/ruangan/<?= $ruangan['r_foto'] ?>" class="card-img-top shadow"
                            alt="<?= $ruangan['r_nama'] ?>">
                        <div class="card-body">
                            <h5 class="card-title text-center"><?= ucwords($ruangan['r_nama']) ?></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mb-3 text-center">
            <a href="ruangan" class="btn btn-selengkapnya rounded-pill">Selengkapnya <i
                    class="fas fa-angles-right"></i></a>
        </div>
    </section>

</main>