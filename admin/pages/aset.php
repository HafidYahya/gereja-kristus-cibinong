<?php

$filterStatus = $_GET['filter'] ?? '';
$filterKelompok = $_GET['filter_kelompok'] ?? '';
$filterKategori = $_GET['filter_kategori'] ?? '';
$search = trim($_GET['search'] ?? '');
// routing
$currentPage = $_GET['page'] ?? 'aset';
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

if ($search !== '') {
    $where[] = "(ma_nama LIKE ? OR ma_merk LIKE ?)";
    $types .= "ss";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$sql = "
SELECT 
    a.id AS a_id,
    a.a_master_aset_id,
    a.a_file_dokumen,
    a.a_tgl_perolehan,
    a.a_harga_perolehan,
    a.a_estimasi_harga,
    a.a_lokasi_ruangan_id,
    a.a_lokasi,
    a.a_status_aset,
    a.a_kondisi_aset,
    a.a_keterangan,
    ma.id AS ma_id,
    ma.ma_nama,
    ma.ma_merk,
    ma.ma_spesifikasi,
    ma.ma_master_kelompok_id,
    ma.ma_master_kategori_id,
    ma.ma_is_active,
    mkel.kel_nama AS nama_kelompok,
    mkat.kat_nama AS nama_kategori,
    r.r_nama AS r_nama
FROM aset a
LEFT JOIN master_aset ma 
    ON a.a_master_aset_id = ma.id
LEFT JOIN master_kelompok_aset mkel 
    ON ma.ma_master_kelompok_id = mkel.id
LEFT JOIN master_kategori_aset mkat 
    ON ma.ma_master_kategori_id = mkat.id
LEFT JOIN ruangan r 
    ON a.a_lokasi_ruangan_id = r.id
$whereSql
ORDER BY ma.ma_is_active DESC, ma.id DESC
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
FROM aset a
LEFT JOIN master_aset ma 
    ON a.a_master_aset_id = ma.id
LEFT JOIN master_kelompok_aset mkel 
    ON ma.ma_master_kelompok_id = mkel.id
LEFT JOIN master_kategori_aset mkat 
    ON ma.ma_master_kategori_id = mkat.id
LEFT JOIN ruangan r 
    ON a.a_lokasi_ruangan_id = r.id
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

$aset = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// ================== SELECT MASTER KELOMPOK , KATEGORI ASET, & MASTER ASET ==================
$sqlKelompok = "SELECT * FROM master_kelompok_aset WHERE kel_is_active = 1";
$stmtKelompok = $conn->prepare($sqlKelompok);
$stmtKelompok->execute();
$master_kelompok = $stmtKelompok->get_result()->fetch_all(MYSQLI_ASSOC);

$sqlKategori = "SELECT * FROM master_kategori_aset WHERE kat_is_active = 1";
$stmtKategori = $conn->prepare($sqlKategori);
$stmtKategori->execute();
$master_kategori = $stmtKategori->get_result()->fetch_all(MYSQLI_ASSOC);

$sqlMasterAset = "
SELECT 
    ma.id,
    ma.ma_nama,
    ma.ma_merk,
    ma.ma_spesifikasi,
    mkel.kel_nama AS nama_kelompok,
    mkat.kat_nama AS nama_kategori
FROM master_aset ma
LEFT JOIN master_kelompok_aset mkel 
    ON ma.ma_master_kelompok_id = mkel.id
LEFT JOIN master_kategori_aset mkat 
    ON ma.ma_master_kategori_id = mkat.id
WHERE ma.ma_is_active = 1
";
$stmtMasterAset = $conn->prepare($sqlMasterAset);
$stmtMasterAset->execute();
$master_aset = $stmtMasterAset->get_result()->fetch_all(MYSQLI_ASSOC);

$sqlRuangan = "SELECT * FROM ruangan WHERE r_is_active = 1";
$stmtRuangan = $conn->prepare($sqlRuangan);
$stmtRuangan->execute();
$ruangan = $stmtRuangan->get_result()->fetch_all(MYSQLI_ASSOC);

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="p-4">
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">
        <div class="page-header row g-2 align-items-center">
            <div class="col-12 col-sm">
                <h1 class="page-title mb-0">Aset</h1>
            </div>
            <div class="col-12 col-sm-auto">
                <button type="button"
                    class="btn btn-white border border-warning rounded-pill shadow-sm w-100 d-flex align-items-center justify-content-center"
                    data-bs-toggle="modal" data-bs-target="#modal-tambah-aset"><i class="fas fa-plus me-2"></i>Tambah
                    Data</button>
            </div>
        </div>
    </div>
    <form method="GET">
        <div class="row mb-3">
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Status</label>
                <input type="hidden" name="page" value="aset">
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
                            <?= htmlspecialchars($k['kel_nama']) ?>
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
                            <?= htmlspecialchars($k['kat_nama']) ?>
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
                        <th>File Dokumen</th>
                        <th>Tanggal Perolehan</th>
                        <th>Harga Perolehan</th>
                        <th>Estimasi Harga (<?= date('Y') ?>)</th>
                        <th>Lokasi</th>
                        <th>Lokasi (Ruangan)</th>
                        <th>Status Aset</th>
                        <th>Kondisi Aset</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php if (empty($aset)) : ?>
                        <tr>
                            <td colspan="14" class="text-center text-muted">
                                <i class="fas fa-file-circle-xmark me-3"></i>Data tidak ditemukan
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($aset as $data) : ?>
                            <tr>
                                <td><?= $no++ ?></td>

                                <td><?= htmlspecialchars($data['ma_nama'] ?? '-') ?></td>

                                <td><?= htmlspecialchars($data['ma_merk'] ?? '-') ?></td>

                                <td><?= ucwords(htmlspecialchars($data['nama_kelompok'] ?? '-')) ?></td>

                                <td><?= ucwords(htmlspecialchars($data['nama_kategori'] ?? '-')) ?></td>

                                <td>
                                    <button class="btn btn-sm btn-warning text-white border" data-bs-toggle="modal"
                                        data-bs-target="#modalFile<?= $data['a_id'] ?>">
                                        Lihat File
                                    </button>
                                </td>

                                <td><?= date('d/m/Y', strtotime($data['a_tgl_perolehan'] ?? '-')) ?></td>

                                <td>Rp. <?= number_format($data['a_harga_perolehan'] ?? 0, 0, ',', '.') ?></td>

                                <td>Rp. <?= number_format($data['a_estimasi_harga'] ?? 0, 0, ',', '.') ?></td>

                                <td><?= htmlspecialchars($data['a_lokasi'] ?? '-') ?></td>

                                <td><?= htmlspecialchars($data['r_nama'] ?? '-') ?></td>

                                <td><?= ucwords(htmlspecialchars($data['a_status_aset'] ?? '-')) ?></td>

                                <td><?= ucwords(htmlspecialchars($data['a_kondisi_aset'] ?? '-')) ?></td>

                                <td>


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
                    href="index.php?page=aset&p=<?= $p - 1 ?>&filter=<?= urlencode($filterStatus) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
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
                        href="index.php?page=aset&p=<?= $i ?>&filter=<?= urlencode($filterStatus) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=aset&p=<?= $p + 1 ?>&filter=<?= urlencode($filterStatus) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>

    <!-- MODAL FILE DOKUMEN -->
    <?php foreach ($aset as $data) : ?>
        <?php
        $file = $data['a_file_dokumen'] ?? null;
        $ext = strtolower(pathinfo($file ?? '', PATHINFO_EXTENSION));
        ?>

        <div class="modal fade" id="modalFile<?= $data['a_id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- HEADER -->
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">Preview Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body text-center">

                        <?php if ($file): ?>

                            <?php if ($ext === 'pdf'): ?>
                                <iframe src="../assets/uploads/dokumen_file/<?= $file ?>" width="100%" height="500px"></iframe>

                            <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                <img src="../assets/uploads/dokumen_file/<?= $file ?>" class="img-fluid rounded">

                            <?php else: ?>
                                <p class="text-muted">Preview tidak tersedia untuk format ini</p>
                            <?php endif; ?>

                        <?php else: ?>
                            <p class="text-muted">
                                <i class="fas fa-file-circle-xmark me-2"></i>
                                File tidak tersedia
                            </p>
                        <?php endif; ?>

                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer">
                        <?php if ($file): ?>
                            <a href="../actions/aset/download.php?file=<?= urlencode($file) ?>" class="btn btn-success">
                                <i class="fas fa-download me-1"></i> Download
                            </a>

                            <a href="../assets/uploads/dokumen_file/<?= $file ?>" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-1"></i> Buka di Tab Baru
                            </a>
                        <?php endif; ?>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>


    <!-- MODAL TAMBAH -->
    <div class="modal fade modal-lg" id="modal-tambah-aset" tabindex="-1" aria-hidden="true"
        aria-labelledby="modal-tambah-master-aset-label">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modal-tambah-aset-label">Tambah Aset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../actions/aset/tambah.php" method="post" class="form-confirm-tambah"
                    id="form-tambah-aset" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Aset*</label>
                                <select class="form-control" name="master_aset_id" id="master_aset_id" required>
                                    <option value="">Pilih Aset</option>
                                    <?php foreach ($master_aset as $aset): ?>
                                        <option value="<?= $aset['id'] ?>" data-merk="<?= $aset['ma_merk'] ?>"
                                            data-spesifikasi="<?= $aset['ma_spesifikasi'] ?>"
                                            data-kelompok="<?= $aset['nama_kelompok'] ?>"
                                            data-kategori="<?= $aset['nama_kategori'] ?>">
                                            <?= $aset['ma_nama'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Merk</label>
                                <input type="text" class="form-control" id="merk" disabled>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Spesifikasi</label>
                                <div id="spesifikasi_view" class="form-control"
                                    style="min-height:100px; background-color: #e8ecee"></div>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Kelompok Aset</label>
                                <input class="form-control" id="kelompok_aset" disabled>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Kategori Aset</label>
                                <input class="form-control" id="kategori_aset" disabled>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">File Dokumen</label>
                                <input type="file" class="form-control" name="file_dokumen">
                                <span class="form-text fst-italic">Format file: PDF, PNG, JPG, JPEG (Max 20 MB)</span>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Tanggal Perolehan*</label>
                                <input type="date" class="form-control" name="tanggal_perolehan" required>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Harga Perolehan*</label>
                                <input type="text" class="form-control" id="harga_perolehan" name="harga_perolehan"
                                    required>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Estimasi Harga (<?= date('Y') ?>)*</label>
                                <input type="text" class="form-control" id="estimasi_harga" name="estimasi_harga"
                                    required>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Lokasi (Ruangan)</label>
                                <select class="form-control" name="ruangan_id">
                                    <option value="">Pilih Ruangan</option>
                                    <?php foreach ($ruangan as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= $r['r_nama'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Lokasi</label>
                                <input type="text" class="form-control" name="lokasi">
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Status Aset*</label>
                                <select class="form-control" name="status_aset" required>
                                    <option value="">Pilih Status</option>
                                    <option value="terpakai">Terpakai</option>
                                    <option value="tidak_terpakai">Tidak Terpakai</option>
                                    <option value="write_off">Write Off</option>
                                    <option value="dipinjam">Dipinjam</option>
                                </select>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label fw-semibold">Kondisi Aset*</label>
                                <select class="form-control" name="kondisi_aset" required>
                                    <option value="">Pilih Kondisi</option>
                                    <option value="baik">Baik</option>
                                    <option value="cukup">Cukup</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_sedang">Rusak Sedang</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3"></textarea>
                            </div>

                        </div>
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
<!-- Auto Fill Modal Tambah Data -->
<script>
    document.getElementById("master_aset_id").addEventListener("change", function() {
        let selected = this.options[this.selectedIndex];

        document.getElementById("merk").value = selected.dataset.merk || '';
        document.getElementById("spesifikasi_view").innerHTML = selected.dataset.spesifikasi || '';
        document.getElementById("kelompok_aset").value = selected.dataset.kelompok || '';
        document.getElementById("kategori_aset").value = selected.dataset.kategori || '';
    });
</script>
<!-- Validasi Numeric Input -->
<script>
    ['harga_perolehan', 'estimasi_harga'].forEach(function(id) {
        document.getElementById(id).addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
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