<?php
session_start();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!-- ICON -->
    <link rel="icon" href="../../assets/img/logo_gkc.png" type="image/png">

    <!-- TITLE -->
    <title>Gereja Kristus Cibinong</title>

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap"
        rel="stylesheet">
    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <!-- NAVBAR START-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../../assets/img/logo_gkc.png" alt="Gereja Kristus Cibinong" width="24"
                    class="d-inline-block align-text-center">
                Gereja Kristus Cibinong
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <!-- Jika Jemaat belum Login -->
                <?php if (!isset($_SESSION['jemaat']) && empty($_SESSION['jemaat'])): ?>
                    <span class="navbar-toggler-icon"></span>
                <?php else: ?>
                    <!-- Jika sudah Login -->
                    <img src="../assets/images/<?= $_SESSION['foto-jemaat'] ?? 'profile-default.jpg' ?>" alt="Profile"
                        class="profile-jemaat d-inline-block align-text-center">
                <?php endif; ?>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- MENU (tengah) -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link text-center active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-center" href="#">Aset Gereja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-center" href="#">Ruangan</a>
                    </li>
                </ul>

                <!-- BUTTON (kanan) -->
                <!-- Jika Jemaat belum Login -->
                <?php if (!isset($_SESSION['jemaat']) && empty($_SESSION['jemaat'])): ?>
                    <div class="d-flex gap-2 justify-content-center">
                        <a class="btn-login btn btn-sm rounded-pill" href="#">Masuk</a>
                        <a class="btn-register btn btn-sm rounded-pill" href="#">Daftar</a>
                    </div>
                <?php else: ?>
                    <!-- Jika sudah Login -->
                    <div class="d-flex gap-2 justify-content-center">
                        <a class="btn text-white" href="#"><?= $_SESSION['nama-jemaat'] ?? 'Profil' ?><img
                                src="../assets/images/<?= $_SESSION['foto-jemaat'] ?? 'profile-default.jpg' ?>"
                                alt="Profile" class="d-none profile-jemaat d-md-inline-block align-text-center ms-3"></a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </nav>
    <!-- NAVBAR END -->



    <!-- Sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>
</body>

</html>