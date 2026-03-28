<?php
session_start();
$page = $_GET['url'] ?? 'home';
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
    <link rel="icon" href="assets/img/logo_gkc.png" type="image/png">

    <!-- TITLE -->
    <title><?= $page === 'home' ? 'Gereja Kristus Cibinong' : $title ?></title>

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/404.css">
</head>

<body>
    <!-- NAVBAR START-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="assets/img/logo_gkc.png" alt="Gereja Kristus Cibinong" width="24"
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
                <img src="public/assets/images/<?= $_SESSION['foto-jemaat'] ?? 'profile-default.jpg' ?>" alt="Profile"
                    class="profile-jemaat d-inline-block align-text-center">
                <?php endif; ?>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- MENU (tengah) -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link text-center <?= $page === 'home' ? 'active' : '' ?>" href="home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-center <?= $page === 'aset-gereja' ? 'active' : '' ?>"
                            href="aset-gereja">Aset Gereja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-center <?= $page === 'ruangan' ? 'active' : '' ?>"
                            href="ruangan">Ruangan</a>
                    </li>
                </ul>

                <!-- BUTTON (kanan) -->
                <!-- Jika Jemaat belum Login -->
                <?php if (!isset($_SESSION['jemaat']) && empty($_SESSION['jemaat'])): ?>
                <div class="d-flex gap-2 justify-content-center">
                    <a class="btn-login btn btn-sm rounded-pill" href="login">Masuk</a>
                    <a class="btn-register btn btn-sm rounded-pill" href="register">Daftar</a>
                </div>
                <?php else: ?>
                <!-- Jika sudah Login -->
                <div class="d-flex gap-2 justify-content-center">
                    <a class="btn text-white" href="#"><?= $_SESSION['nama-jemaat'] ?? 'Profil' ?><img
                            src="public/assets/images/<?= $_SESSION['foto-jemaat'] ?? 'profile-default.jpg' ?>"
                            alt="Profile" class="d-none profile-jemaat d-md-inline-block align-text-center ms-3"></a>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </nav>
    <!-- NAVBAR END -->