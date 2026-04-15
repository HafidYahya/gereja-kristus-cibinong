<footer class="footer-gkc bg-dark">
    <div class="container">
        <div class="row footer-gkc__content">
            <div class="col-lg-6 col-md-12">
                <h2 class="footer-gkc__title">Gereja Kristus Cibinong</h2>
                <p class="footer-gkc__address">
                    <i class="fa-solid fa-location-dot footer-gkc__icon"></i>
                    Jl. Raya Jakarta-Bogor No.KM. 42, RW.5, Pabuaran, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16916
                </p>
            </div>
            <div class="col-lg-6 col-md-12 footer-gkc__menu">
                <h3 class="footer-gkc__menu-title">Menu</h3>
                <div class="footer-gkc__links">
                    <a href="home" class="footer-gkc__link">Home</a>
                    <a href="ruangan" class="footer-gkc__link">Ruangan</a>
                    <!-- Hanya muncul ketika sudah login -->
                    <?php if (isset($_SESSION['jemaat_id']) && !empty($_SESSION['jemaat_id'])): ?>
                        <a href="aset-gereja" class="footer-gkc__link">Aset Gereja</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <p class="footer-gkc__copyright">
            &copy; <?= date('Y'); ?> Gereja Kristus Cibinong. All rights reserved.
        </p>
    </div>
</footer>


<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
</script>
</body>

</html>