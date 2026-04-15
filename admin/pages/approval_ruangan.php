<?php

$filter = $_POST['filter-status'] ?? $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');
// routing
$currentPage = $_GET['page'] ?? 'approval_ruangan';
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
    $where[] = "pr_status = ?";
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
            pr.id AS pr_id,
            pr.pr_jemaat_id,
            pr.pr_ruangan_id,
            pr.pr_tanggal,
            pr.pr_jam_mulai,
            pr.pr_jam_selesai,
            pr.pr_alasan,
            pr.pr_status,
            j.id AS j_id,
            j.j_nama,
            j.j_no_hp,
            j.j_email,
            j.j_alamat,
            j.j_foto,
            r.id AS r_id,
            r.r_nama
        FROM peminjaman_ruangan pr
        LEFT JOIN jemaat j ON pr.pr_jemaat_id = j.id
        LEFT JOIN ruangan r ON pr.pr_ruangan_id = r.id
        $whereSql 
        ORDER BY 
            FIELD(pr.pr_status, 'pending', 'approved', 'finish', 'cancel'),
            pr.pr_tanggal ASC,
            pr.id ASC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$typesData = $types . "ii";
$paramsData = array_merge($params, [$limit, $offset]);
$stmt->bind_param($typesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$sqlTotal = "SELECT COUNT(*) as total
FROM peminjaman_ruangan pr
LEFT JOIN jemaat j ON pr.pr_jemaat_id = j.id
LEFT JOIN ruangan r ON pr.pr_ruangan_id = r.id
$whereSql";

$stmtTotal = $conn->prepare($sqlTotal);
if ($types !== '') {
    $stmtTotal->bind_param($types, ...$params);
}
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalData = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalData / $limit);

$peminjaman_ruangan = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="p-4">
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">
        <div class="page-header row g-2 align-items-center">
            <div class="col-12 col-sm">
                <h1 class="page-title mb-0">Approval Peminjaman Ruangan</h1>
            </div>
        </div>
    </div>
    <form method="GET">
        <div class="row mb-3">
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Status</label>
                <input type="hidden" name="page" value="approval_ruangan">
                <select name="filter" class="form-select border border-warning">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="finish" <?= $filter === 'finish' ? 'selected' : '' ?>>Finish</option>
                    <option value="cancel" <?= $filter === 'cancel' ? 'selected' : '' ?>>Cancel</option>
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
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Total Durasi</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php if (empty($peminjaman_ruangan)) : ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="fas fa-file-circle-xmark me-3"></i>Data tidak ditemukan
                        </td>
                    </tr>
                    <?php else : ?>
                    <?php foreach ($peminjaman_ruangan as $pr) : ?>
                    <!-- HITUNG TOTAL JAM -->
                    <?php
                            $mulai = strtotime($pr['pr_jam_mulai']);
                            $selesai = strtotime($pr['pr_jam_selesai']);
                            $durasiJam = ($selesai - $mulai) / 3600;
                            ?>
                    <tr>
                        <td><?= $no++ ?></td>

                        <!-- NAMA JEMAAT -->
                        <td><?= htmlspecialchars($pr['j_nama']) ?></td>

                        <!-- NAMA EMAIL -->
                        <td><?= htmlspecialchars($pr['j_email']) ?></td>

                        <!-- RUANGAAN YANG DIPINJAM -->
                        <td><?= htmlspecialchars($pr['r_nama']) ?></td>

                        <!-- TANGGAL -->
                        <td><?= date('d/m/Y', strtotime($pr['pr_tanggal'])) ?></td>

                        <!-- JAM MULAI -->
                        <td><?= $pr['pr_jam_mulai'] ?></td>

                        <!-- JAM SELESAI -->
                        <td><?= $pr['pr_jam_selesai'] ?></td>

                        <!-- TOTAL JAM -->
                        <td><?= $durasiJam ?> Jam</td>

                        <!-- STATUS -->
                        <td>
                            <?php
                                    $status = $pr['pr_status'];
                                    $badgeClass = match ($status) {
                                        'pending' => 'bg-warning text-dark',
                                        'approved' => 'bg-success',
                                        'finish' => 'bg-primary',
                                        'cancel' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>

                        <!-- BUTTON PROSES & DETAIL -->
                        <td>
                            <?php if ($pr['pr_status'] === 'pending' || $pr['pr_status'] === 'approved'): ?>
                            <button style="min-width: 100px" type="button"
                                class="btn btn-primary btn-sm shadow-md fw-bold" data-bs-toggle="modal"
                                data-bs-target="#modal-proses-peminjaman_ruangan-<?= $pr['pr_id'] ?>">
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
                    href="index.php?page=approval_ruangan&p=<?= $p - 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
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
                    href="index.php?page=approval_ruangan&p=<?= $i ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=approval_ruangan&p=<?= $p + 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>

    <!-- MODAL PROSES -->
    <?php foreach ($peminjaman_ruangan as $pr) : ?>
    <div class="modal fade" id="modal-proses-peminjaman_ruangan-<?= $pr['pr_id'] ?>" tabindex="-1"
        aria-labelledby="modal-proses-label-<?= $pr['pr_id'] ?>" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-sm rounded-4">

                <!-- HEADER -->
                <div class="modal-header" style="background-color: #EF9F27;">
                    <h5 class="modal-title fw-semibold" id="modal-proses-label-<?= $pr['pr_id'] ?>"
                        style="color: #412402;">
                        Proses Peminjaman
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- FORM -->
                <form action="../actions/peminjaman_ruangan/proses.php" method="POST"
                    class="form-confirm-proses d-flex flex-column overflow-hidden">
                    <div class="modal-body px-4 py-3">

                        <!-- HIDDEN INPUT FILTER -->
                        <input type="hidden" name="filter" value="<?= $filter ?>">

                        <!-- HIDDEN ID -->
                        <input type="hidden" name="id" value="<?= $pr['pr_id'] ?>">

                        <!-- SEKSI: INFORMASI PEMINJAM -->
                        <p class="text-uppercase text-secondary fw-semibold mb-2">
                            Informasi Peminjam
                        </p>

                        <!-- HEADER PEMINJAM -->
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <?php $foto = !empty($pr['j_foto']) ? $pr['j_foto'] : 'profile-default.jpg'; ?>
                            <img src="../assets/uploads/jemaat/<?= htmlspecialchars($foto) ?>"
                                style="width:52px; height:52px; object-fit:cover; border-radius:50%;" class="border">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($pr['j_nama']) ?>
                                </div>
                                <div class="text-muted"><?= htmlspecialchars($pr['j_email']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- DETAIL PEMINJAM -->
                        <div class="rounded-3 p-3 mb-3" style="background: var(--bs-secondary-bg);">
                            <div class="row g-2" style="font-size: 13px;">
                                <div class="col-4 text-muted">No. Handphone</div>
                                <div class="col-8"><?= htmlspecialchars($pr['j_no_hp']) ?></div>

                                <div class="col-4 text-muted">Email</div>
                                <div class="col-8"><?= htmlspecialchars($pr['j_email']) ?></div>

                                <div class="col-4 text-muted">Alamat</div>
                                <div class="col-8"><?= nl2br(htmlspecialchars($pr['j_alamat'] ?? '-')) ?></div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- SEKSI: DETAIL PEMINJAMAN -->
                        <p class="text-uppercase text-secondary fw-semibold mb-2">
                            Detail Peminjaman
                        </p>

                        <div class="rounded-3 p-3 mb-3" style="background: var(--bs-secondary-bg);">
                            <div class="row g-2" style="font-size: 13px;">
                                <div class="col-4 text-muted">Nama Ruangan</div>
                                <div class="col-8"><?= htmlspecialchars($pr['r_nama']) ?></div>

                                <div class="col-4 text-muted">Tanggal</div>
                                <div class="col-8"><?= date('d/m/Y', strtotime($pr['pr_tanggal'])) ?></div>

                                <div class="col-4 text-muted">Jam Mulai</div>
                                <div class="col-8"><?= substr($pr['pr_jam_mulai'], 0, 5) ?></div>

                                <div class="col-4 text-muted">Jam Selesai</div>
                                <div class="col-8"><?= substr($pr['pr_jam_selesai'], 0, 5) ?></div>

                                <div class="col-4 text-muted">Total Durasi</div>
                                <div class="col-8">
                                    <?php
                                        $mulai = strtotime($pr['pr_jam_mulai']);
                                        $selesai = strtotime($pr['pr_jam_selesai']);
                                        $durasi = ($selesai - $mulai) / 3600;
                                        echo $durasi . " jam";
                                        ?>
                                </div>

                                <div class="col-4 text-muted">Alasan</div>
                                <div class="col-8"><?= nl2br(htmlspecialchars($pr['pr_alasan'])) ?></div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- SEKSI: UBAH STATUS -->
                        <p class="text-uppercase text-secondary fw-semibold mb-2"
                            style="font-size: 11px; letter-spacing: 0.05em;">
                            Ubah Status
                        </p>
                        <div class="d-flex gap-3">
                            <?php if ($pr['pr_status'] === 'pending'): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status"
                                    id="status-approved-<?= $pr['pr_id'] ?>" value="approved" required>
                                <label class="form-check-label text-success fw-bold"
                                    for="status-approved-<?= $pr['pr_id'] ?>">Setujui</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status"
                                    id="status-cancel-<?= $pr['pr_id'] ?>" value="cancel">
                                <label class="form-check-label text-danger fw-bold"
                                    for="status-cancel-<?= $pr['pr_id'] ?>">Tolak</label>
                            </div>
                            <?php endif; ?>
                            <?php if ($pr['pr_status'] === 'approved') : ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status"
                                    id="status-finish-<?= $pr['pr_id'] ?>" value="finish">
                                <label class="form-check-label fw-bold"
                                    for="status-finish-<?= $pr['pr_id'] ?>">Selesai</label>
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
<!-- Konfirmasi Delete -->
<script>
document.querySelectorAll('.form-confirm-delete').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: "Hapus Data",
            text: "Apakah anda yakin akan menghapus data ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e6b53c",
            cancelButtonColor: "#d33",
            cancelButtonText: "Batal",
            confirmButtonText: "Ya, hapus"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
<script>
const showPasswordCheckbox = document.getElementById('showPassword');
const passwordInput = document.getElementById('password');

if (showPasswordCheckbox && passwordInput) {
    showPasswordCheckbox.addEventListener('change', function() {
        passwordInput.type = this.checked ? 'text' : 'password';
    });
}
document.querySelectorAll('.show-password-edit').forEach((checkbox) => {
    checkbox.addEventListener('change', function() {
        const targetId = this.getAttribute('data-target');
        const input = targetId ? document.getElementById(targetId) : null;
        if (input) {
            input.type = this.checked ? 'text' : 'password';
        }
    });
});
</script>
<script>
document.getElementById("no_hp").addEventListener("input", function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
// ================== CONFIRM SUBMIT ==================
document.querySelectorAll('.form-confirm-edit').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: "Konfirmasi Perubahan Data",
            text: "Apakah anda yakin akan mengubah data ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e6b53c",
            cancelButtonColor: "#d33",
            cancelButtonText: "Batal",
            confirmButtonText: "Ya, simpan perubahan"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

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