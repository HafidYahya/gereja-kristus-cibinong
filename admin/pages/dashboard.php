<?php
// SELECT TOTAL SEMUA ASET
$sql_aset = "
SELECT 
    COUNT(*) AS total_semua_aset,
    SUM(a_status_aset = 'terpakai') AS total_aset_terpakai,
    SUM(a_status_aset = 'tidak_terpakai') AS total_aset_tidak_terpakai,
    SUM(a_status_aset = 'write_off') AS total_aset_writeoff,
    SUM(a_this_dipinjam = 1) AS total_aset_dipinjam
FROM aset
";

$dataAset = $conn->query($sql_aset)->fetch_assoc();

// SAFE NULL
$total_semua_aset         = $dataAset['total_semua_aset'] ?? 0;
$total_aset_terpakai      = $dataAset['total_aset_terpakai'] ?? 0;
$total_aset_tidak_terpakai = $dataAset['total_aset_tidak_terpakai'] ?? 0;
$total_aset_writeoff      = $dataAset['total_aset_writeoff'] ?? 0;
$total_aset_dipinjam      = $dataAset['total_aset_dipinjam'] ?? 0;

// SELECT TOTAL SEMUA RUANGAN
$sql_ruangan = "
SELECT 
    COUNT(*) AS total_semua_ruangan,
    SUM(r_is_active = 1) AS total_ruangan_aktif,
    SUM(r_is_active = 0) AS total_ruangan_nonaktif
FROM ruangan
";

$dataRuangan = $conn->query($sql_ruangan)->fetch_assoc();

$total_semua_ruangan   = $dataRuangan['total_semua_ruangan'] ?? 0;
$total_ruangan_aktif   = $dataRuangan['total_ruangan_aktif'] ?? 0;
$total_ruangan_nonaktif = $dataRuangan['total_ruangan_nonaktif'] ?? 0;

// SELECT TOTAL SEMUA JEMAAT
$sql_jemaat = "
SELECT 
    COUNT(*) AS total_semua_jemaat,
    SUM(j_is_active = 1) AS total_jemaat_aktif,
    SUM(j_is_active = 0) AS total_jemaat_nonaktif
FROM jemaat
";

$dataJemaat = $conn->query($sql_jemaat)->fetch_assoc();

$total_semua_jemaat   = $dataJemaat['total_semua_jemaat'] ?? 0;
$total_jemaat_aktif   = $dataJemaat['total_jemaat_aktif'] ?? 0;
$total_jemaat_nonaktif = $dataJemaat['total_jemaat_nonaktif'] ?? 0;

// SELECT TOTAL SEMUA USER
$sql_user = "
SELECT 
    COUNT(*) AS total_semua_user,
    SUM(u_is_active = 1) AS total_user_aktif,
    SUM(u_is_active = 0) AS total_user_nonaktif
FROM users
";

$dataUser = $conn->query($sql_user)->fetch_assoc();

$total_semua_user   = $dataUser['total_semua_user'] ?? 0;
$total_user_aktif   = $dataUser['total_user_aktif'] ?? 0;
$total_user_nonaktif = $dataUser['total_user_nonaktif'] ?? 0;
?>

<div class="p-4">
    <!-- HEADER -->
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">
        <div class="row">
            <div class="col">
                <h1 class="mb-0">Dashboard</h1>
                <small class="text-muted">Ringkasan data sistem</small>
            </div>
        </div>
    </div>

    <!-- CARDS -->
    <div class="row g-4">

        <!-- ================= ASET ================= -->
        <div class="col-12">
            <h5 class="fw-bold">Data Aset</h5>
        </div>

        <?php
        $cardsAset = [
            ['title' => 'Total Semua Aset', 'value' => $total_semua_aset, 'icon' => 'fa-box'],
            ['title' => 'Aset Terpakai', 'value' => $total_aset_terpakai, 'icon' => 'fa-check-circle'],
            ['title' => 'Aset Tidak Terpakai', 'value' => $total_aset_tidak_terpakai, 'icon' => 'fa-circle'],
            ['title' => 'Aset Write Off', 'value' => $total_aset_writeoff, 'icon' => 'fa-trash'],
            ['title' => 'Aset Dipinjam', 'value' => $total_aset_dipinjam, 'icon' => 'fa-handshake'],
        ];
        ?>

        <?php foreach ($cardsAset as $card): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center"
                            style="width:50px; height:50px; min-width:50px; min-height:50px;">
                            <i class="fas <?= $card['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="text-muted small"><?= $card['title'] ?></div>
                            <h4 class="mb-0 fw-bold"><?= $card['value'] ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>


        <!-- ================= RUANGAN ================= -->
        <div class="col-12 mt-3">
            <h5 class="fw-bold">Data Ruangan</h5>
        </div>

        <?php
        $cardsRuangan = [
            ['title' => 'Total Semua Ruangan', 'value' => $total_semua_ruangan, 'icon' => 'fa-building'],
            ['title' => 'Ruangan Aktif', 'value' => $total_ruangan_aktif, 'icon' => 'fa-check'],
            ['title' => 'Ruangan Non Aktif', 'value' => $total_ruangan_nonaktif, 'icon' => 'fa-ban'],
        ];
        ?>

        <?php foreach ($cardsRuangan as $card): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width:50px; height:50px; min-width:50px; min-height:50px;">
                            <i class="fas <?= $card['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="text-muted small"><?= $card['title'] ?></div>
                            <h4 class="mb-0 fw-bold"><?= $card['value'] ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>


        <!-- ================= JEMAAT ================= -->
        <div class="col-12 mt-3">
            <h5 class="fw-bold">Data Jemaat</h5>
        </div>

        <?php
        $cardsJemaat = [
            ['title' => 'Total Semua Jemaat (Terdaftar)', 'value' => $total_semua_jemaat, 'icon' => 'fa-users'],
            ['title' => 'Jemaat Aktif', 'value' => $total_jemaat_aktif, 'icon' => 'fa-user-check'],
            ['title' => 'Jemaat Non Aktif', 'value' => $total_jemaat_nonaktif, 'icon' => 'fa-user-times'],
        ];
        ?>

        <?php foreach ($cardsJemaat as $card): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width:50px; height:50px; min-width:50px; min-height:50px;">
                            <i class="fas <?= $card['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="text-muted small"><?= $card['title'] ?></div>
                            <h4 class="mb-0 fw-bold"><?= $card['value'] ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>


        <!-- ================= USER ================= -->
        <div class="col-12 mt-3">
            <h5 class="fw-bold">Data User</h5>
        </div>

        <?php
        $cardsUser = [
            ['title' => 'Total Semua User', 'value' => $total_semua_user, 'icon' => 'fa-user'],
            ['title' => 'User Aktif', 'value' => $total_user_aktif, 'icon' => 'fa-user-check'],
            ['title' => 'User Non Aktif', 'value' => $total_user_nonaktif, 'icon' => 'fa-user-slash'],
        ];
        ?>

        <?php foreach ($cardsUser as $card): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width:50px; height:50px; min-width:50px; min-height:50px;">
                            <i class="fas <?= $card['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="text-muted small"><?= $card['title'] ?></div>
                            <h4 class="mb-0 fw-bold"><?= $card['value'] ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>