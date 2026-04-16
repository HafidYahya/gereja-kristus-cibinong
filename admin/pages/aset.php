<?php

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
    ma.id AS ma_id,
    ma.ma_nama,
    ma.ma_merk,
    ma.ma_spesifikasi,
    ma.ma_master_kelompok_id,
    ma.ma_master_kategori_id,
    ma.ma_is_active,
    mkel.kel_nama AS nama_kelompok,
    mkat.kat_nama AS nama_kategori,
    COUNT(a.id) AS qty
FROM aset a
LEFT JOIN master_aset ma 
    ON a.a_master_aset_id = ma.id
LEFT JOIN master_kelompok_aset mkel 
    ON ma.ma_master_kelompok_id = mkel.id
LEFT JOIN master_kategori_aset mkat 
    ON ma.ma_master_kategori_id = mkat.id
$whereSql
GROUP BY 
    ma.id, ma.ma_nama, ma.ma_merk, ma.ma_spesifikasi, ma.ma_master_kelompok_id, 
    ma.ma_master_kategori_id, ma.ma_is_active, mkel.kel_nama, mkat.kat_nama
ORDER BY ma.ma_nama ASC
LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$typesData = $types . "ii";
$paramsData = array_merge($params, [$limit, $offset]);
$stmt->bind_param($typesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$sqlTotal = "
SELECT COUNT(DISTINCT ma.id) as total
FROM aset a
LEFT JOIN master_aset ma 
    ON a.a_master_aset_id = ma.id
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
WHERE ma.ma_is_active = 1 ORDER BY ma.ma_nama ASC
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
                <input type="hidden" name="page" value="aset">
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
                <button class="btn w-100 btn-md btn-primary" type="submit">Cari</button>
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
                        <th>Qty</th>
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

                                <td><?= $data['qty'] ?? 0 ?></td>

                                <td>
                                    <a href="index.php?page=detail_aset&filter_kelompok=<?= urlencode($data['ma_master_kelompok_id'] ?? '') ?>&filter_kategori=<?= urlencode($data['ma_master_kategori_id'] ?? '') ?>&ma_id=<?= urlencode($data['ma_id'] ?? '') ?>"
                                        class="btn btn-sm shadow-md fw-bold text-primary" title="Lihat Detail">
                                        Lihat Detail
                                    </a>

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
                    href="index.php?page=aset&p=<?= $p - 1 ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
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
                        href="index.php?page=aset&p=<?= $i ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=aset&p=<?= $p + 1 ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>




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
                                <label class="form-label">Aset*</label>
                                <select class="form-control" name="master_aset_id" id="master_aset_id" required>
                                    <option value="">Pilih Aset</option>
                                    <?php foreach ($master_aset as $ma): ?>
                                        <option value="<?= $ma['id'] ?>">
                                            <?= $ma['ma_nama'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Merk</label>
                                <input type="text" class="form-control" id="merk" disabled>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Spesifikasi</label>
                                <div id="spesifikasi_view" class="form-control"
                                    style="min-height:100px; background-color: #e8ecee"></div>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Kelompok Aset</label>
                                <input class="form-control" id="kelompok_aset" disabled>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Kategori Aset</label>
                                <input class="form-control" id="kategori_aset" disabled>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">File Dokumen</label>
                                <input type="file" class="form-control" name="file_dokumen">
                                <span class="form-text fst-italic">Format file: PDF, PNG, JPG, JPEG (Max 20 MB)</span>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Tanggal Perolehan*</label>
                                <input type="date" class="form-control" name="tanggal_perolehan" required>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Harga Perolehan*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border border-end-0">Rp.
                                    </span>
                                    <input type="text" class="form-control border border-start-0" id="harga_perolehan"
                                        name="harga_perolehan" inputmode="numeric" required>
                                </div>

                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Estimasi Harga (<?= date('Y') ?>)*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border border-end-0">Rp.
                                    </span>
                                    <input type="text" class="form-control border border-start-0" id="estimasi_harga"
                                        name="estimasi_harga" inputmode="numeric" required>
                                </div>

                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Lokasi (Ruangan)</label>
                                <select class="form-control" name="ruangan_id">
                                    <option value="">Pilih Ruangan</option>
                                    <?php foreach ($ruangan as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= $r['r_nama'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control" name="lokasi">
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Status Aset*</label>
                                <select class="form-control" name="status_aset" required>
                                    <option value="">Pilih Status</option>
                                    <option value="terpakai">Terpakai</option>
                                    <option value="tidak_terpakai">Tidak Terpakai</option>
                                    <option value="write_off">Write Off</option>
                                </select>
                            </div>

                            <div class="col-12 col-lg-6 mb-3">
                                <label class="form-label">Kondisi Aset*</label>
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
                                <label class="form-label">Keterangan</label>
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
<!-- Auto Fill Modal Tambah Data -->
<script>
    const masterAset = <?= json_encode($master_aset, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    document.querySelectorAll('#master_aset_id').forEach(select => {
        select.addEventListener("change", function() {
            const id = this.value;
            const modal = this.closest('.modal');

            const data = masterAset.find(a => a.id == id);
            if (!data) return;

            modal.querySelector('#merk').value = data.ma_merk || '';
            modal.querySelector('#spesifikasi_view').innerHTML = data.ma_spesifikasi || '';
            modal.querySelector('#kelompok_aset').value = data.nama_kelompok || '';
            modal.querySelector('#kategori_aset').value = data.nama_kategori || '';
        });
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