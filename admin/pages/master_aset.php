<?php

$filterStatus = $_GET['filter'] ?? '';
$filterKelompok = $_GET['filter_kelompok'] ?? '';
$filterKategori = $_GET['filter_kategori'] ?? '';
$search = trim($_GET['search'] ?? '');
// routing
$currentPage = $_GET['page'] ?? 'master_aset';
// pagination
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$p = max(1, $p);

$limit = 5;
$offset = ($p - 1) * $limit;

// ================== QUERY DATA ==================
$where = [];
$types = '';
$params = [];

if ($filterStatus !== '') {
    $where[] = "ma.ma_is_active = ?";
    $types .= "i";
    $params[] = (int)$filterStatus;
}

if ($search !== '') {
    $where[] = "(ma.ma_nama LIKE ? OR ma.ma_merk LIKE ?)";
    $types .= "ss";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}

if ($filterKelompok !== '') {
    $where[] = "ma.ma_master_kelompok_id = ?";
    $types .= "i";
    $params[] = (int)$filterKelompok;
}

if ($filterKategori !== '') {
    $where[] = "ma.ma_master_kategori_id = ?";
    $types .= "i";
    $params[] = (int)$filterKategori;
}


$whereSql = '';
if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$sql = "
SELECT 
    ma.*,
    mkel.kel_nama AS nama_kelompok,
    mkat.kat_nama AS nama_kategori
FROM master_aset ma
LEFT JOIN master_kelompok_aset mkel 
    ON ma.ma_master_kelompok_id = mkel.id
LEFT JOIN master_kategori_aset mkat 
    ON ma.ma_master_kategori_id = mkat.id
$whereSql
ORDER BY ma_is_active DESC, id DESC
LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$typesData = $types . "ii";
$paramsData = array_merge($params, [$limit, $offset]);
$stmt->bind_param($typesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$sqlTotal = "
SELECT COUNT(*) as total
FROM master_aset ma
LEFT JOIN master_kelompok_aset mkel 
    ON ma.ma_master_kelompok_id = mkel.id
LEFT JOIN master_kategori_aset mkat 
    ON ma.ma_master_kategori_id = mkat.id
$whereSql
";
$stmtTotal = $conn->prepare($sqlTotal);
if ($types !== '') {
    $stmtTotal->bind_param($types, ...$params);
}
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalData = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalData / $limit);

$master_aset = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// ================== SELECT MASTER KELOMPOK & KATEGORI ASET ==================
$sqlKelompok = "SELECT * FROM master_kelompok_aset WHERE kel_is_active = 1 ORDER BY kel_nama ASC";
$stmtKelompok = $conn->prepare($sqlKelompok);
$stmtKelompok->execute();
$master_kelompok = $stmtKelompok->get_result()->fetch_all(MYSQLI_ASSOC);

$sqlKategori = "SELECT * FROM master_kategori_aset WHERE kat_is_active = 1 ORDER BY kat_nama ASC";
$stmtKategori = $conn->prepare($sqlKategori);
$stmtKategori->execute();
$master_kategori = $stmtKategori->get_result()->fetch_all(MYSQLI_ASSOC);



$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="p-4">
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">
        <div class="page-header row g-2 align-items-center">
            <div class="col-12 col-sm">
                <h1 class="page-title mb-0">Master Aset</h1>
            </div>
            <div class="col-12 col-sm-auto">
                <button type="button"
                    class="btn btn-white border border-warning rounded-pill shadow-sm w-100 d-flex align-items-center justify-content-center"
                    data-bs-toggle="modal" data-bs-target="#modal-tambah-master-aset"><i
                        class="fas fa-plus me-2"></i>Tambah
                    Data</button>
            </div>
        </div>
    </div>
    <form method="GET">
        <div class="row mb-3">
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Status</label>
                <input type="hidden" name="page" value="master_aset">
                <select name="filter" class="form-select border border-warning">
                    <option value="">Semua Status</option>
                    <option value="1" <?= $filterStatus === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $filterStatus === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Kelompok Aset</label>
                <select name="filter_kelompok" class="form-select border border-warning">
                    <option value="">Semua Kelompok</option>
                    <?php foreach ($master_kelompok as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($filterKelompok == $k['id']) ? 'selected' : '' ?>>
                            <?= ucwords(htmlspecialchars($k['kel_nama'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Kategori Aset</label>
                <select name="filter_kategori" class="form-select border border-warning">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($master_kategori as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($filterKategori == $k['id']) ? 'selected' : '' ?>>
                            <?= ucwords(htmlspecialchars($k['kat_nama'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-12 col-lg-4">
                <label class="form-label invisible">Seach Invisible</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border border-warning border-end-0" id="search"><i
                            class="fas fa-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 border border-warning"
                        placeholder="Cari nama atau merk aset" aria-label="Search" aria-describedby="search"
                        value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-lg-4">
                <label class="form-label invisible">Button Invisible</label>
                <button class="btn w-100 btn-md btn-primary" type="submit" onclick="this.form.submit()">Cari</button>
            </div>
        </div>
    </form>
    <div class="table-responsive border border-warning rounded bg-white">
        <div class="table-container">
            <table class="table table-hover text-nowrap shadow-md" style="min-width:700px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Merk</th>
                        <th>Kelompok Aset</th>
                        <th>Kategori Aset</th>
                        <th>Spesifikasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php if (empty($master_aset)) : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fas fa-file-circle-xmark me-3"></i>Data tidak ditemukan
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($master_aset as $data) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($data['ma_nama']) ?></td>
                                <td><?= ucwords(htmlspecialchars($data['ma_merk'])) ?></td>
                                <td><?= ucwords(htmlspecialchars($data['nama_kelompok'])) ?></td>
                                <td><?= ucwords(htmlspecialchars($data['nama_kategori'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-white border border-secondary" data-bs-toggle="modal"
                                        data-bs-target="#modalSpesifikasi<?= $data['id'] ?>">
                                        Lihat Spesifikasi
                                    </button>
                                </td>
                                <td>
                                    <span style="min-width: 100px"
                                        class="badge badge-sm <?= (int) $data['ma_is_active'] === 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= (int) $data['ma_is_active'] === 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="form-confirm-ubah-status d-inline-block"
                                        action="../actions/master_aset/edit_status.php">
                                        <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                        <button style="min-width: 100px" type="submit"
                                            class="btn btn-sm <?= (int) $data['ma_is_active'] === 1 ? 'btn-danger' : 'btn-success' ?>"><?= (int) $data["ma_is_active"] === 1 ? 'Nonaktifkan' : 'Aktifkan'  ?></button>
                                    </form>

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
                    href="index.php?page=master_aset&p=<?= $p - 1 ?>&filter=<?= urlencode($filterStatus) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
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
                        href="index.php?page=master_aset&p=<?= $i ?>&filter=<?= urlencode($filterStatus) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=master_aset&p=<?= $p + 1 ?>&filter=<?= urlencode($filterStatus) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>

    <?php foreach ($master_aset as $data): ?>
        <!-- MODAL SPESIFIKASI -->
        <div class="modal fade" id="modalSpesifikasi<?= $data['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Spesifikasi Aset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <?php if (!empty($data['ma_spesifikasi'])): ?>
                            <?= $data['ma_spesifikasi'] ?>
                        <?php else: ?>
                            <p class="text-muted">Spesifikasi tidak tersedia.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>



    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="modal-tambah-master-aset" tabindex="-1" aria-hidden="true"
        aria-labelledby="modal-tambah-master-aset-label">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modal-tambah-master-aset-label">Tambah Master Aset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../actions/master_aset/tambah.php" method="post" class="form-confirm-tambah"
                    id="form-tambah-aset">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Aset*</label>
                            <input type="text" class="form-control" name="nama" placeholder="Masukan nama aset"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelompok Aset*</label>
                            <select class="form-control" name="master_kelompok_id" required>
                                <option value="">Pilih Kelompok</option>
                                <?php foreach ($master_kelompok as $kelompok): ?>
                                    <option value="<?= $kelompok['id'] ?>"><?= $kelompok['kel_nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori Aset*</label>
                            <select class="form-control" name="master_kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($master_kategori as $kategori): ?>
                                    <option value="<?= $kategori['id'] ?>"><?= $kategori['kat_nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Merk*</label>
                            <input type="text" class="form-control" name="merk" placeholder="Masukan merk aset"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Spesifikasi</label>
                            <!-- Container editor -->
                            <div id="editor"></div>
                            <input type="hidden" name="spesifikasi" id="spesifikasi">
                        </div>
                        <input type="hidden" name="status" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn rounded-pill btn-light border border-dark"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn rounded-pill btn-warning">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Quill Editor -->
<script>
    var quill = new Quill('#editor', {
        theme: 'snow'
    });
</script>
<script>
    // ================== CONFIRM SUBMIT ==================
    document.querySelectorAll('.form-confirm-ubah-status').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: "Konfirmasi",
                text: "Apakah anda yakin ingin mengubah status?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e6b53c",
                cancelButtonColor: "#d33",
                cancelButtonText: "Batal",
                confirmButtonText: "Ya"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.form-confirm-tambah').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: "Konfirmasi Tambah Data",
                text: "Apakah anda yakin akan menambahkan data ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e6b53c",
                cancelButtonColor: "#d33",
                cancelButtonText: "Batal",
                confirmButtonText: "Ya"
            }).then((result) => {
                if (result.isConfirmed) {
                    // inject quill content to hidden input
                    let html = quill.root.innerHTML;

                    if (quill.getText().trim().length === 0 || html === '<p><br></p>') {
                        html = '';
                    }

                    document.querySelector('#spesifikasi').value = html;
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