<div class="sidebar bg-dark">
    <div class="sidebar-brand text-start d-flex align-items-center">
        <span class="brand-name"><i class="fa fa-chart-pie me-3"></i>AMS</span>
    </div>
    <div class="sidebar-nav <?= $page === 'dashboard' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=dashboard"><i class="fas fa-house"></i><span>Dashboard</span></a>
    </div>

    <small class="section-title fw-semibold text-secondary">Master Data</small>
    <div class="sidebar-nav <?= $page === 'master_kelompok_aset' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=master_kelompok_aset"><i class="fas fa-layer-group"></i><span>Master
                Kelompok Aset</span></a>
    </div>
    <div class="sidebar-nav <?= $page === 'master_kategori_aset' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=master_kategori_aset"><i class="fas fa-tags"></i><span>Master Kategori
                Aset</span></a>
    </div>
    <div class="sidebar-nav <?= $page === 'ruangan' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=ruangan"><i class="fas fa-door-open"></i><span>Ruangan</span></a>
    </div>
    <div class="sidebar-nav <?= $page === 'jemaat' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=jemaat"><i class="fas fa-people-group"></i><span>Jemaat</span></a>
    </div>


    <small class="section-title fw-semibold text-secondary">Transaksi</small>
    <div class="sidebar-nav <?= $page === 'aset' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=aset"><i class="fas fa-boxes-stacked"></i><span>Aset Gereja</span></a>
    </div>
    <div class="sidebar-nav <?= $page === 'approval_ruangan' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=approval_ruangan"><i class="fas fa-calendar-check"></i><span>Approval
                Ruangan</span></a>
    </div>
    <div class="sidebar-nav <?= $page === 'approval_aset' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=approval_aset"><i class="fas fa-clipboard-list"></i><span>Approval
                Aset</span></a>
    </div>


    <small class="section-title fw-semibold text-secondary">Pengaturan</small>
    <div class="sidebar-nav <?= $page === 'users' ? 'active' : '' ?>">
        <a class="nav-item" href="index.php?page=users"><i class="fas fa-users"></i><span>Pengguna</span></a>
    </div>

</div>
<div class="sidebar-overlay" aria-hidden="true"></div>