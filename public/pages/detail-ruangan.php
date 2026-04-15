<?php
include 'config/koneksi.php';

$idRuangan = isset($_GET['r']) ? (int) $_GET['r'] : 0;
$dataRuangan = null;

if ($idRuangan > 0) {
    $stmt = $conn->prepare("SELECT id, r_nama, r_foto, r_keterangan FROM ruangan WHERE id = ? AND r_is_active = 1 LIMIT 1");
    $stmt->bind_param("i", $idRuangan);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataRuangan = $result->fetch_assoc();
}

$dataJadwalRuangan = [];
if ($idRuangan > 0) {
    // Ambil jadwal booking aktif agar pengguna memilih slot lain.
    $stmt = $conn->prepare("
        SELECT pr_tanggal, pr_jam_mulai, pr_jam_selesai
        FROM peminjaman_ruangan
        WHERE pr_ruangan_id = ?
        AND pr_status IN ('pending', 'approved')
        AND pr_tanggal >= CURDATE()
        ORDER BY pr_tanggal, pr_jam_mulai
    ");
    $stmt->bind_param("i", $idRuangan);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataJadwalRuangan = $result->fetch_all(MYSQLI_ASSOC);
}

function formatTanggalIndonesia(string $tanggal): string
{
    $bulanIndonesia = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $timestamp = strtotime($tanggal);
    if ($timestamp === false) {
        return $tanggal;
    }

    $hari = (int) date('d', $timestamp);
    $bulan = (int) date('m', $timestamp);
    $tahun = date('Y', $timestamp);

    return $hari . ' ' . ($bulanIndonesia[$bulan] ?? $bulan) . ' ' . $tahun;
}


$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<main class="container-detail-ruangan container mt-5 pt-5 mb-5">
    <section id="detail-ruangan">
        <?php if (!$dataRuangan): ?>
            <div class="alert alert-warning text-center" role="alert">
                Data ruangan tidak ditemukan atau sudah tidak tersedia.
            </div>
            <div class="text-center mt-3">
                <a href="ruangan" class="btn btn-outline-secondary rounded-pill px-4">
                    Kembali ke daftar ruangan
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4 align-items-start">
                <div class="col-lg-6 col-md-12">
                    <div class="detail-ruangan__image-wrap shadow-sm">
                        <img src="assets/uploads/ruangan/<?= htmlspecialchars($dataRuangan['r_foto']) ?>"
                            alt="<?= htmlspecialchars($dataRuangan['r_nama']) ?>" class="img-fluid detail-ruangan__image">
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="detail-ruangan__content">
                        <h1 class="mb-3"><?= ucwords(htmlspecialchars($dataRuangan['r_nama'])) ?></h1>
                        <p class="mb-4">
                            <?= nl2br(htmlspecialchars($dataRuangan['r_keterangan'] ?? '-')) ?>
                        </p>
                        <div class="detail-ruangan__jadwal mb-4">
                            <h6 class="mb-2">Jadwal untuk ruangan ini</h6>
                            <?php if (empty($dataJadwalRuangan)): ?>
                                <small class="mb-2">Belum ada jadwal (Silahkan gunakan ruangan ini sesuai kebutuhan
                                    anda.)</small>
                            <?php else: ?>
                                <ul class="mb-2">
                                    <?php foreach ($dataJadwalRuangan as $jadwal): ?>
                                        <li>
                                            <?= formatTanggalIndonesia($jadwal['pr_tanggal']) ?> :
                                            <?= substr($jadwal['pr_jam_mulai'], 0, 5) ?> -
                                            <?= substr($jadwal['pr_jam_selesai'], 0, 5) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($dataJadwalRuangan)): ?>
                                <p class="mb-0">Pinjam ruangan selain di jam dan tanggal tersebut</p>
                            <?php endif; ?>
                        </div>
                        <?php if (!isset($_SESSION['jemaat_id']) || empty($_SESSION['jemaat_id'])): ?>
                            <a href="login" class="btn btn-pinjam-ruangan rounded-pill px-4 py-2">
                                Pinjam Ruangan
                            </a>
                        <?php elseif (isset($_SESSION['jemaat_id']) && !empty($_SESSION['jemaat_id'])): ?>
                            <button type="button" class="btn btn-pinjam-ruangan rounded-pill px-4 py-2" data-bs-toggle="modal"
                                data-bs-target="#modal-peminjaman-ruangan">
                                Pinjam Ruangan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>



    <!-- MODAL PEMINJAMAN -->
    <div class="modal fade" id="modal-peminjaman-ruangan" tabindex="-1" aria-hidden="true"
        aria-labelledby="modal-peminjaman-ruangan-label">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modal-peminjaman-ruangan-label">Pinjam Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="actions/peminjaman_ruangan/tambah.php" method="post" onsubmit="return validateForm()"
                    id="form-peminjaman-ruangan">
                    <div class="modal-body">
                        <div class="row">

                            <!-- HIDDEN INPUTE JEMAAT -->
                            <input type="hidden" name="jemaat_id" value="<?= $_SESSION['jemaat_id'] ?? '' ?>">
                            <!-- HIDDEN INPUT RUANGAN -->
                            <input type="hidden" name="ruangan_id" value="<?= $idRuangan ?? '' ?>">


                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Tanggal*</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal"
                                    min="<?= date('Y-m-d') ?>" required>
                            </div>

                            <!-- JAM BUTTON -->
                            <label class="form-label">Pilih Jam*</label>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php for ($i = 0; $i < 24; $i++):
                                    $start = str_pad($i, 2, '0', STR_PAD_LEFT) . ":00";
                                    $end   = str_pad(($i + 1) % 24, 2, '0', STR_PAD_LEFT) . ":00";
                                ?>
                                    <button disabled type="button" class="btn btn-outline-primary slot-btn"
                                        data-start="<?= $start ?>" data-end="<?= $end ?>">
                                        <?= $start ?> - <?= $end ?>
                                    </button>
                                <?php endfor; ?>
                            </div>

                            <!-- INPUT HIIDEN JAM MULAI DAN SELESAI -->
                            <input type="hidden" name="jam_mulai" id="jam_mulai">
                            <input type="hidden" name="jam_selesai" id="jam_selesai">

                            <!-- HIDDEN INPUT STATUS -->
                            <input type="hidden" name="status" value="pending">

                            <div class="col-12 mb-3">
                                <label class="form-label">Alasan*</label>
                                <textarea name="alasan" class="form-control" rows="3"
                                    placeholder="Masukan alasan peminajam ruangan ini..." required></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn rounded-pill btn-light border border-dark"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn rounded-pill btn-warning">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<!-- VALIDASI HARUS PILIH TANGGAL BARU BISA KLIK JAM -->
<script>
    const tanggalInput = document.getElementById('tanggal');
    const slotButtons = document.querySelectorAll('.slot-btn');

    tanggalInput.addEventListener('change', function() {
        if (this.value) {
            slotButtons.forEach(btn => btn.disabled = false);
        }
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
<script>
    // Validasi form sebelum submit
    function validateForm() {
        const mulai = document.getElementById('jam_mulai').value;
        const selesai = document.getElementById('jam_selesai').value;

        //  kalau belum pilih jam
        if (!mulai || !selesai) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Silakan pilih jam terlebih dahulu!'
            });
            return false; // stop submit
        }

        //  konfirmasi
        Swal.fire({
            title: 'Konfirmasi Booking',
            text: `Booking dari ${mulai} sampai ${selesai}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Booking!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector("form").submit();
            }
        });

        return false; // stop submit dulu
    }
</script>
<script>
    let selectedSlots = [];

    document.querySelectorAll('.slot-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const start = this.dataset.start;

            // kalau belum ada pilihan
            if (selectedSlots.length === 0) {
                selectedSlots.push(start);
                this.classList.add('active');
                return;
            }

            // ambil slot terakhir
            const last = selectedSlots[selectedSlots.length - 1];

            // validasi harus berurutan (selisih 1 jam)
            if (isNextHour(last, start)) {
                selectedSlots.push(start);
                this.classList.add('active');
            } else {
                Swal.fire({
                    title: "Oppss...!",
                    icon: "error",
                    text: "Pilih jam harus berurutan dari kiri ke kanan dengan selisih 1 jam!",
                    draggable: true
                });
            }

            updateTime();
        });
    });

    function isNextHour(last, current) {
        let lastHour = parseInt(last.split(':')[0]);
        let currentHour = parseInt(current.split(':')[0]);

        return currentHour === lastHour + 1;
    }

    function updateTime() {
        if (selectedSlots.length === 0) return;

        let start = selectedSlots[0];
        let endHour = parseInt(selectedSlots[selectedSlots.length - 1].split(':')[0]) + 1;

        let end = String(endHour).padStart(2, '0') + ":00";

        document.getElementById('jam_mulai').value = start;
        document.getElementById('jam_selesai').value = end;
    }
</script>