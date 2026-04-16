<?php

$filter = $_POST['filter-status'] ?? $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');
// routing
$currentPage = $_GET['page'] ?? 'approval_aset';
// pagination
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$p = max(1, $p);

$limit = 5;
$offset = ($p - 1) * $limit;

// ================== QUERY DATA ==================
$where = [];
$types = '';
$params = [];

if ($filter !== '') {
    $where[] = "pa_status = ?";
    $types .= "s";
    $params[] = $filter;
}

if ($search !== '') {
    $where[] = "(j_nama LIKE ? OR j_email LIKE ?)";
    $types .= "ss";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT 
    pa.id AS pa_id,
    pa.pa_jemaat_id,
    pa.pa_tgl_pinjam,
    pa.pa_tgl_kembali,
    pa.pa_status,
    pa.pa_alasan_ditolak,

    j.id AS j_id,
    j.j_nama,
    j.j_no_hp,
    j.j_email,
    j.j_alamat,
    j.j_foto,

    ma.id AS ma_id,
    ma.ma_nama,
    COUNT(a.id) AS qty

FROM peminjaman_aset pa

LEFT JOIN jemaat j 
    ON pa.pa_jemaat_id = j.id

INNER JOIN detail_peminjaman_aset dpa 
    ON dpa.dpa_peminjaman_aset_id = pa.id

INNER JOIN aset a 
    ON dpa.dpa_aset_id = a.id

INNER JOIN master_aset ma 
    ON a.a_master_aset_id = ma.id

$whereSql 

GROUP BY pa.id, ma.id

ORDER BY 
    FIELD(pa.pa_status, 'pending', 'disetujui', 'dikembalikan', 'ditolak'),
    pa.id ASC

LIMIT ? OFFSET ?";


$stmt = $conn->prepare($sql);
$typesData = $types . "ii";
$paramsData = array_merge($params, [$limit, $offset]);
$stmt->bind_param($typesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$sqlTotal = "SELECT COUNT(*) as total
FROM peminjaman_aset pa
LEFT JOIN jemaat j ON pa.pa_jemaat_id = j.id
INNER JOIN detail_peminjaman_aset dpa ON dpa.dpa_peminjaman_aset_id = pa.id
$whereSql";

$stmtTotal = $conn->prepare($sqlTotal);
if ($types !== '') {
    $stmtTotal->bind_param($types, ...$params);
}
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalData = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalData / $limit);

$peminjaman_aset = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="p-4">
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">
        <div class="page-header row g-2 align-items-center">
            <div class="col-12 col-sm">
                <h1 class="page-title mb-0">Approval Peminjaman Aset</h1>
            </div>
        </div>
    </div>
    <form method="GET">
        <div class="row mb-3">
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Status</label>
                <input type="hidden" name="page" value="approval_aset">
                <select name="filter" class="form-select border border-warning">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="disetujui" <?= $filter === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="dikembalikan" <?= $filter === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan
                    </option>
                    <option value="ditolak" <?= $filter === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-sm-12 col-lg-4">
                <label class="form-label invisible">Seach Invisible</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border border-warning border-end-0" id="search"><i
                            class="fas fa-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 border border-warning"
                        placeholder="Cari nama atau email" aria-label="Search" aria-describedby="search"
                        value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-lg-4">
                <label class="form-label invisible">Button Invisible</label>
                <button class="btn w-100 btn-md btn-primary" type="submit" onclick="this.form.submit()">Cari</button>
            </div>
        </div>
    </form>
    <div class=" table-responsive border border-warning rounded bg-white">
        <div class="table-container">
            <table class="table table-hover text-nowrap shadow-md" style="min-width:700px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pengaju</th>
                        <th>Email Pengaju</th>
                        <th>Nama Aset</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Alasan Ditolak</th>
                        <th>QTY</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php if (empty($peminjaman_aset)) : ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                <i class="fas fa-file-circle-xmark me-3"></i>Data tidak ditemukan
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($peminjaman_aset as $pa) : ?>
                            <!-- HITUNG TOTAL JAM -->
                            <tr>
                                <td><?= $no++ ?></td>

                                <!-- NAMA JEMAAT -->
                                <td><?= htmlspecialchars($pa['j_nama']) ?></td>

                                <!-- NAMA EMAIL -->
                                <td><?= htmlspecialchars($pa['j_email']) ?></td>

                                <!-- RUANGAAN YANG DIPINJAM -->
                                <td><?= htmlspecialchars($pa['ma_nama']) ?></td>

                                <!-- TANGGAL PINJAM-->
                                <td>
                                    <?= !empty($pa['pa_tgl_pinjam'])
                                        ? date('d/m/Y', strtotime($pa['pa_tgl_pinjam']))
                                        : '-'
                                    ?>
                                </td>

                                <!-- TANGGAL KEMBALI-->
                                <td>
                                    <?= !empty($pa['pa_tgl_kembali'])
                                        ? date('d/m/Y', strtotime($pa['pa_tgl_kembali']))
                                        : '-'
                                    ?>
                                </td>

                                <!-- ALASAN DITOLAK -->
                                <td><?= $pa['pa_alasan_ditolak'] ?? '-' ?></td>

                                <!-- QTY -->
                                <td><?= $pa['qty'] ?></td>

                                <!-- STATUS -->
                                <td>
                                    <?php
                                    $status = $pa['pa_status'];
                                    $badgeClass = match ($status) {
                                        'pending' => 'bg-warning text-dark',
                                        'disetujui' => 'bg-success',
                                        'dikembalikan' => 'bg-primary',
                                        'ditolak' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span style="min-width: 80px" class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>

                                <!-- BUTTON PROSES & DETAIL -->
                                <td>
                                    <?php if ($pa['pa_status'] === 'pending' || $pa['pa_status'] === 'disetujui'): ?>
                                        <button style="min-width: 100px" type="button"
                                            class="btn btn-primary btn-sm shadow-md fw-bold" data-bs-toggle="modal"
                                            data-bs-target="#modal-proses-peminjaman_aset-<?= $pa['pa_id'] ?>">
                                            Proses
                                        </button>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

    </div>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">

            <!-- Previous -->
            <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=approval_aset&p=<?= $p - 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                    Previous
                </a>
            </li>

            <?php
            $start = max(1, $p - 2);
            $end = min($totalPages, $p + 2);
            ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= ($i == $p) ? 'active' : '' ?>">
                    <a class="page-link"
                        href="index.php?page=approval_aset&p=<?= $i ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=approval_aset&p=<?= $p + 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>

    <!-- MODAL PROSES -->
    <?php foreach ($peminjaman_aset as $pa) : ?>
        <div class="modal fade" id="modal-proses-peminjaman_aset-<?= $pa['pa_id'] ?>" tabindex="-1"
            aria-labelledby="modal-proses-label-<?= $pa['pa_id'] ?>" aria-hidden="true">

            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-sm rounded-4">

                    <!-- HEADER -->
                    <div class="modal-header" style="background-color: #EF9F27;">
                        <h5 class="modal-title fw-semibold" id="modal-proses-label-<?= $pa['pa_id'] ?>"
                            style="color: #412402;">
                            Proses Peminjaman
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- FORM -->
                    <form action="../actions/peminjaman_aset/proses.php" method="POST"
                        class="form-confirm-proses d-flex flex-column overflow-hidden">
                        <div class="modal-body px-4 py-3">

                            <!-- HIDDEN INPUT FILTER -->
                            <input type="hidden" name="filter" value="<?= $filter ?>">

                            <!-- HIDDEN ID -->
                            <input type="hidden" name="id" value="<?= $pa['pa_id'] ?>">

                            <!-- SEKSI: INFORMASI PEMINJAM -->
                            <p class="text-uppercase text-secondary fw-semibold mb-2">
                                Informasi Peminjam
                            </p>

                            <!-- HEADER PEMINJAM -->
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <?php $foto = !empty($pa['j_foto']) ? $pa['j_foto'] : 'profile-default.jpg'; ?>
                                <img src="../assets/uploads/jemaat/<?= htmlspecialchars($foto) ?>"
                                    style="width:52px; height:52px; object-fit:cover; border-radius:50%;" class="border">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($pa['j_nama']) ?>
                                    </div>
                                    <div class="text-muted"><?= htmlspecialchars($pa['j_email']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- DETAIL PEMINJAM -->
                            <div class="rounded-3 p-3 mb-3" style="background: var(--bs-secondary-bg);">
                                <div class="row g-2" style="font-size: 13px;">
                                    <div class="col-4 text-muted">No. Handphone</div>
                                    <div class="col-8"><?= htmlspecialchars($pa['j_no_hp']) ?></div>

                                    <div class="col-4 text-muted">Email</div>
                                    <div class="col-8"><?= htmlspecialchars($pa['j_email']) ?></div>

                                    <div class="col-4 text-muted">Alamat</div>
                                    <div class="col-8"><?= nl2br(htmlspecialchars($pa['j_alamat'] ?? '-')) ?></div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- SEKSI: DETAIL PEMINJAMAN -->
                            <p class="text-uppercase text-secondary fw-semibold mb-2">
                                Detail Peminjaman
                            </p>

                            <div class="rounded-3 p-3 mb-3" style="background: var(--bs-secondary-bg);">
                                <div class="row g-2" style="font-size: 13px;">
                                    <div class="col-4 text-muted">Nama Aset</div>
                                    <div class="col-8"><?= htmlspecialchars($pa['ma_nama']) ?></div>

                                    <div class="col-4 text-muted">Tanggal Pinjam</div>
                                    <div class="col-8"><?= !empty($pa['pa_tgl_pinjam'])
                                                            ? date('d/m/Y', strtotime($pa['pa_tgl_pinjam']))
                                                            : '-'
                                                        ?>
                                    </div>

                                    <div class="col-4 text-muted">Tanggal Kembali</div>
                                    <div class="col-8"><?= !empty($pa['pa_tgl_kembali'])
                                                            ? date('d/m/Y', strtotime($pa['pa_tgl_kembali']))
                                                            : '-'
                                                        ?>
                                    </div>

                                    <div class="col-4 text-muted">QTY</div>
                                    <div class="col-8"><?= $pa['qty'] ?></div>

                                    <?php if ($pa['pa_status'] !== 'disetujui') : ?>
                                        <div class="col-4 text-muted">Alasan Ditolak</div>
                                        <div class="col-8">
                                            <textarea name="alasan_ditolak" class="form-control" rows="3"
                                                placeholder="Masukkan alasan jika ditolak..."></textarea>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- SEKSI: UBAH STATUS -->
                            <p class="text-uppercase text-secondary fw-semibold mb-2"
                                style="font-size: 11px; letter-spacing: 0.05em;">
                                Ubah Status
                            </p>
                            <div class="d-flex gap-3">
                                <?php if ($pa['pa_status'] === 'pending'): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status"
                                            id="status-disetujui-<?= $pa['pa_id'] ?>" value="disetujui" required>
                                        <label class="form-check-label text-success fw-bold"
                                            for="status-disetujui-<?= $pa['pa_id'] ?>">Setujui</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status"
                                            id="status-tolak-<?= $pa['pa_id'] ?>" value="ditolak">
                                        <label class="form-check-label text-danger fw-bold"
                                            for="status-tolak-<?= $pa['pa_id'] ?>">Tolak</label>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pa['pa_status'] === 'disetujui') : ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status"
                                            id="status-kembalikan-<?= $pa['pa_id'] ?>" value="dikembalikan" required>
                                        <label class="form-check-label fw-bold"
                                            for="status-kembalikan-<?= $pa['pa_id'] ?>">Dikembalikan</label>
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div class="modal-footer px-4">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn fw-semibold"
                                style="background-color: #EF9F27; color: #412402;">Proses</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    <?php endforeach; ?>


</div>
<!-- VALIDASI MANDATORY ALASAN DITOLAK JIKA STATUSNYA DITOLAK -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="status"]');
        const alasan = document.querySelector('textarea[name="alasan_ditolak"]');

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'ditolak') {
                    alasan.required = true;
                } else {
                    alasan.required = false;
                }
            });
        });
    });
</script>


<!-- Konfirmasi Delete -->
<script>
    document.querySelectorAll('.form-confirm-proses').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: "Konfirmasi Proses",
                text: "Apakah anda yakin untuk proses ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e6b53c",
                cancelButtonColor: "#d33",
                cancelButtonText: "Batal",
                confirmButtonText: "Ya, saya yakin"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
<script>
    // ================== TOAST ==================
    const getToast = () => {
        if (!window._toastInstance) {
            window._toastInstance = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
        }
        return window._toastInstance;
    };

    <?php if ($success !== '') : ?>
        getToast().fire({
            icon: 'success',
            title: '<?= htmlspecialchars($success) ?>'
        });
    <?php endif; ?>

    <?php if ($error !== '') : ?>
        getToast().fire({
            icon: 'error',
            title: '<?= htmlspecialchars($error) ?>'
        });
    <?php endif; ?>
</script>