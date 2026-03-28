<div class="container py-5">
    <div class="text-center">
        <div class="display-1 fw-bold text-warning">404</div>
        <div class="page-title">Halaman Tidak Ditemukan</div>
        <p class="text-muted">
            Halaman yang kamu cari mungkin sudah dipindahkan, dihapus, atau belum pernah ada.
        </p>
        <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
            <a class="btn btn-warning text-dark fw-semibold" href="index.php">Kembali ke Beranda</a>
            <a class="btn btn-outline-secondary" href="javascript:history.back()">Halaman Sebelumnya</a>
        </div>
        <div class="mt-4 small text-muted">
            status: 404 / path: <span id="pth"></span>
        </div>
    </div>
</div>

<script>
    const pathEl = document.getElementById('pth');
    if (pathEl) {
        pathEl.textContent = window.location.pathname;
    }
</script>



