<?php
if (!isset($_SESSION['jemaat_id'])) {
    header("Location: /gerejakristuscibinong/login");
    exit();
}
include __DIR__ . "/../../config/koneksi.php";

$query = mysqli_query($conn, "SELECT * FROM master_aset WHERE ma_is_active=1");
$master_aset = mysqli_fetch_all($query, MYSQLI_ASSOC);



$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>


<main class="container-request-aset container mt-5 pt-5 mb-5">
    <section id="main">
        <div class="row">
            <div class="col-12 align-self-center">
                <h1 class="display-4 text-center">Form Peminjaman Aset</h1>
                <p class="text-center">Silakan lengkapi data berikut untuk mengajukan peminjaman aset di Gereja Kristus
                    Cibinong.</p>
            </div>
        </div>
        <div class="row">
            <form method="post" action="actions/peminjaman_aset/tambah.php" class="row g-4">
                <!-- HIDDEN INPUT -->
                <input type="hidden" name="jemaat_id" value="<?= $_SESSION['jemaat_id'] ?>">
                <input type="hidden" name="status" value="pending">

                <div class="mb-3 col-12">
                    <label for="nama" class="form-label">Aset*</label>
                    <select class="form-control" id="master_aset" name="master_aset" required>
                        <option value="">--Pilih Aset--</option>
                        <?php foreach ($master_aset as $ma): ?>
                            <option value="<?= $ma['id'] ?>"><?= $ma['ma_nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 col-lg-6 col-md-12">
                    <label for="merk" class="form-label merk">Merk</label>
                    <input type="text" class="form-control" id="merk" disabled required>
                </div>

                <div class="mb-3 col-lg-6 col-md-12">
                    <label for="qty" class="form-label">QTY*</label>
                    <input type="text" class="form-control qty" id="qty" name="qty" inputmode="numeric"
                        placeholder="Masukan minimal 1" required>
                </div>

                <!-- SPESIFIKASI -->
                <div class="col-12 mb-3">
                    <label class="form-label">Spesifikasi</label>
                    <div id="spesifikasi_view" class="form-control spesifikasi_view"
                        style="min-height:100px; background-color: #e8ecee">
                    </div>
                </div>



                <div class="mb-3 col-lg-12">
                    <button type="submit" class="btn btn-register-now rounded-pill">SUBMIT</button>
                </div>
            </form>
        </div>
    </section>

</main>
<!-- VALIDASI INPUT QTY -->
<script>
    document.querySelector(".qty").addEventListener("input", function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>

<!-- KONFIRMASI -->
<!-- KONFIRMASI SUBMIT -->
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        // Validasi qty minimal 1
        const qty = parseInt(document.querySelector('#qty').value);
        if (!qty || qty < 1) {
            Swal.fire({
                title: "Perhatian!",
                text: "QTY minimal 1 untuk melakukan pengajuan aset ini",
                icon: "warning"
            });
            return;
        }

        // Validasi aset dipilih
        const aset = document.querySelector('#master_aset').value;
        if (!aset) {
            Swal.fire({
                title: "Perhatian!",
                text: "Silakan pilih aset terlebih dahulu",
                icon: "warning"
            });
            return;
        }

        Swal.fire({
            title: "Konfirmasi Peminjaman",
            text: "Apakah anda yakin ingin mengajukan peminjaman aset ini?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#198754",
            cancelButtonColor: "#d33",
            cancelButtonText: "Batal",
            confirmButtonText: "Ya, Ajukan"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
<!-- AUTOFILL MERK DAN SPESIFIKASI -->
<script>
    const masterAset = <?= json_encode($master_aset, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    document.querySelector('#master_aset').addEventListener("change", function() {
        const id = this.value;
        const data = masterAset.find(a => a.id == id);

        if (!data) {
            document.querySelector('#merk').value = '';
            document.querySelector('#spesifikasi_view').innerHTML = '';
            return;
        }

        document.querySelector('#merk').value = data.ma_merk || '';
        document.querySelector('#spesifikasi_view').innerHTML = data.ma_spesifikasi || '';
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