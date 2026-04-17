<?php

$filterKelompok = $_GET['filter_kelompok'] ?? '';
$filterKategori = $_GET['filter_kategori'] ?? '';
$maId = $_GET['ma_id'] ?? '';
$filterStatusAset = $_GET['filter_status_aset'] ?? '';
$filterKondisiAset = $_GET['filter_kondisi_aset'] ?? '';
$filterRuangan = $_GET['filter_ruangan'] ?? '';
$filterIzinPinjam = $_GET['filter_izin'] ?? '';
$search = trim($_GET['search'] ?? '');
// routing
$currentPage = $_GET['page'] ?? 'detail_aset';
// pagination
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$p = max(1, $p);

$limit = 5;
$offset = ($p - 1) * $limit;

// ================== QUERY DATA ==================
$where = [];
$types = '';
$params = [];

if ($filterIzinPinjam !== '') {
    $where[] = "a.a_boleh_dipinjam = ?";
    $types .= "s";
    $params[] = $filterIzinPinjam;
}

if ($filterStatusAset !== '') {
    $where[] = "a.a_status_aset = ?";
    $types .= "s";
    $params[] = $filterStatusAset;
}

if ($filterKondisiAset !== '') {
    $where[] = "a.a_kondisi_aset = ?";
    $types .= "s";
    $params[] = $filterKondisiAset;
}

if ($filterRuangan !== '') {
    $where[] = "a.a_lokasi_ruangan_id = ?";
    $types .= "i";
    $params[] = (int)$filterRuangan;
}

if ($maId !== '') {
    $where[] = "ma.id = ?";
    $types .= "i";
    $params[] = (int)$maId;
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
    a.a_boleh_dipinjam,
    a.a_this_dipinjam,
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
ORDER BY a.a_this_dipinjam DESC, a.a_tgl_perolehan DESC, ma.id DESC
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
    ma.ma_is_active,
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
    <div class="col-12 col-sm mb-3">
        <a href="index.php?page=aset" class="btn btn-white btn-sm border border-primary rounded shadow-sm"><i
                class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">

        <div class="page-header row g-2 align-items-center">
            <div class="col-12 col-sm">
                <h1 class="page-title mb-0">Detail Aset</h1>
            </div>
        </div>
    </div>
    <form method="GET">
        <div class="row mb-3">

            <input type="hidden" name="page" value="detail_aset">
            <input type="hidden" name="filter_kelompok" value="<?= htmlspecialchars($filterKelompok) ?>">
            <input type="hidden" name="filter_kategori" value="<?= htmlspecialchars($filterKategori) ?>">
            <input type="hidden" name="ma_id" value="<?= htmlspecialchars($maId) ?>">

            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Izin Peminjaman</label>
                <select name="filter_izin" class="form-select border border-warning">
                    <option value="">Semua Izin</option>
                    <option value="1" <?= $filterIzinPinjam == 1 ? 'selected' : '' ?>>Boleh Dipinjam</option>
                    <option value="0" <?= $filterIzinPinjam == 0 ? 'selected' : '' ?>>Tidak Boleh Dipinjam
                    </option>
                </select>
            </div>

            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Status Aset</label>
                <select name="filter_status_aset" class="form-select border border-warning">
                    <option value="">Semua Status</option>
                    <option value="terpakai" <?= $filterStatusAset === 'terpakai' ? 'selected' : '' ?>>Terpakai</option>
                    <option value="tidak_terpakai" <?= $filterStatusAset === 'tidak_terpakai' ? 'selected' : '' ?>>Tidak
                        Terpakai</option>
                    <option value="write_off" <?= $filterStatusAset === 'write_off' ? 'selected' : '' ?>>Write Off
                    </option>
                </select>
            </div>

            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Kondisi Aset</label>
                <select name="filter_kondisi_aset" class="form-select border border-warning">
                    <option value="">Semua Kategori</option>
                    <option value="baik" <?= $filterKondisiAset === 'baik' ? 'selected' : '' ?>>Baik</option>
                    <option value="cukup" <?= $filterKondisiAset === 'cukup' ? 'selected' : '' ?>>Cukup</option>
                    <option value="rusak_ringan" <?= $filterKondisiAset === 'rusak_ringan' ? 'selected' : '' ?>>Rusak
                        Ringan</option>
                    <option value="rusak_sedang" <?= $filterKondisiAset === 'rusak_sedang' ? 'selected' : '' ?>>Rusak
                        Sedang</option>
                    <option value="rusak_berat" <?= $filterKondisiAset === 'rusak_berat' ? 'selected' : '' ?>>Rusak
                        Berat</option>
                </select>
            </div>

            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Lokasi (Ruangan)</label>
                <select name="filter_ruangan" class="form-select border border-warning">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($ruangan as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($filterRuangan == $r['id']) ? 'selected' : '' ?>>
                            <?= ucwords(htmlspecialchars($r['r_nama'])) ?>
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
                        <th>Status Izin</th>
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

                                <td><?= date('d/m/Y', strtotime($data['a_tgl_perolehan'] ?? '')) ?></td>

                                <td>Rp. <?= number_format($data['a_harga_perolehan'] ?? 0, 0, ',', '.') ?></td>

                                <td>Rp. <?= number_format($data['a_estimasi_harga'] ?? 0, 0, ',', '.') ?></td>

                                <td><?= ucwords(htmlspecialchars($data['a_lokasi'] ?? '')) ?></td>

                                <td><?= htmlspecialchars($data['r_nama'] ?? '') ?></td>

                                <?php if ((int)$data['a_this_dipinjam'] === 1) : ?>
                                    <td>
                                        <span style="min-width: 80px"
                                            class="badge bg-primary"><?= str_replace(['-', '_'], ' ', ucwords(strtolower((int)$data['a_this_dipinjam'] === 1 ? 'Dipinjam' : ''))) ?></span>
                                    </td>
                                <?php else : ?>
                                    <?php
                                    $status = $data['a_status_aset'];
                                    $badgeClass = match ($status) {
                                        'terpakai' => 'bg-success',
                                        'tidak_terpakai' => 'bg-secondary',
                                        'write_off' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <td><span style="min-width: 80px"
                                            class="badge <?= $badgeClass ?>"><?= str_replace(['-', '_'], ' ', ucwords(strtolower($data['a_status_aset'] ?? ''))) ?></span>
                                    </td>
                                <?php endif; ?>

                                <td><?= str_replace(['-', '_'], ' ', ucwords(strtolower($data['a_kondisi_aset'] ?? ''))) ?></td>

                                <!-- BOLEH DIPINJAM -->
                                <td>
                                    <span style="min-width: 100px"
                                        class="badge badge-sm <?= (int) $data['a_boleh_dipinjam'] === 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= (int) $data['a_boleh_dipinjam'] === 1 ? 'Boleh Dipinjam' : 'Tidak Boleh Dipinjam' ?>
                                    </span>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-sm shadow-md " data-bs-toggle="modal"
                                        data-bs-target="#modal-detail-aset-<?= $data['a_id'] ?>">
                                        <i class="far fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm shadow-md" data-bs-toggle="modal"
                                        data-bs-target="#modal-edit-aset-<?= $data['a_id'] ?>">
                                        <i class="far fa-pen-to-square"></i>
                                    </button>
                                    <form action="../actions/aset/delete.php" method="post"
                                        class="d-inline form-confirm-delete">
                                        <input type="hidden" name="id" value="<?= (int) $data['a_id'] ?>">
                                        <input type="hidden" name="p" value="<?= $p ?>">
                                        <input type="hidden" name="ma_id" value="<?= $maId ?>">
                                        <input type="hidden" name="filter_kelompok" value="<?= $filterKelompok ?>">
                                        <input type="hidden" name="filter_kategori" value="<?= $filterKategori ?>">
                                        <input type="hidden" name="filter_izin" value="<?= $filterIzinPinjam ?>">
                                        <button type="submit" class="btn btn-sm shadow-md text-danger">
                                            <i class="far fa-trash-can"></i>
                                        </button>
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
                    href="index.php?page=detail_aset&p=<?= $p - 1 ?>&filter_izin=<?= urlencode($filterIzinPinjam) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&ma_id=<?= urlencode($maId) ?>&filter_status_aset=<?= urlencode($filterStatusAset) ?>&filter_kondisi_aset=<?= urlencode($filterKondisiAset) ?>&filter_ruangan=<?= urlencode($filterRuangan) ?>&search=<?= urlencode($search) ?>">
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
                        href="index.php?page=detail_aset&p=<?= $i ?>&filter_izin=<?= urlencode($filterIzinPinjam) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&ma_id=<?= urlencode($maId) ?>&filter_status_aset=<?= urlencode($filterStatusAset) ?>&filter_kondisi_aset=<?= urlencode($filterKondisiAset) ?>&filter_ruangan=<?= urlencode($filterRuangan) ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=detail_aset&p=<?= $p + 1 ?>&filter_izin=<?= urlencode($filterIzinPinjam) ?>&filter_kelompok=<?= urlencode($filterKelompok) ?>&filter_kategori=<?= urlencode($filterKategori) ?>&ma_id=<?= urlencode($maId) ?>&filter_status_aset=<?= urlencode($filterStatusAset) ?>&filter_kondisi_aset=<?= urlencode($filterKondisiAset) ?>&filter_ruangan=<?= urlencode($filterRuangan) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>


    <!-- MODAL DETAIL -->
    <?php foreach ($aset as $data) : ?>
        <div class="modal fade" id="modal-detail-aset-<?= $data['a_id'] ?>" tabindex="-1" aria-hidden="true"
            aria-labelledby="modal-detail-aset-label">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="modal-detail-aset-label">Detail Aset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 border rounded bg-light">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <h6 class="mb-0 fw-bold">
                                            <?= htmlspecialchars($data['ma_nama'] ?? '-') ?>
                                        </h6>
                                        <span class="badge bg-secondary">
                                            <?= ucwords(htmlspecialchars($data['nama_kelompok'] ?? '-')) ?>
                                        </span>
                                        <span class="badge bg-info text-dark">
                                            <?= ucwords(htmlspecialchars($data['nama_kategori'] ?? '-')) ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        Merk: <?= htmlspecialchars($data['ma_merk'] ?? '-') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Informasi Aset</h6>
                                    <div class="mb-2">
                                        <div class="text-muted small">Status</div>
                                        <div>
                                            <?= str_replace(['-', '_'], ' ', ucwords(strtolower($data['a_status_aset'] ?? '-'))) ?>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="text-muted small">Kondisi</div>
                                        <div>
                                            <?= str_replace(['-', '_'], ' ', ucwords(strtolower($data['a_kondisi_aset'] ?? '-'))) ?>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="text-muted small">Lokasi</div>
                                        <div><?= ucwords(htmlspecialchars($data['a_lokasi'] ?? '-')) ?></div>
                                    </div>
                                    <div class="mb-0">
                                        <div class="text-muted small">Lokasi (Ruangan)</div>
                                        <div><?= htmlspecialchars($data['r_nama'] ?? '-') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Informasi Perolehan</h6>
                                    <div class="mb-2">
                                        <div class="text-muted small">Tanggal Perolehan</div>
                                        <div>
                                            <?= $data['a_tgl_perolehan'] ? date('d/m/Y', strtotime($data['a_tgl_perolehan'])) : '-' ?>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="text-muted small">Harga Perolehan</div>
                                        <div>Rp. <?= number_format($data['a_harga_perolehan'] ?? 0, 0, ',', '.') ?></div>
                                    </div>
                                    <div class="mb-0">
                                        <div class="text-muted small">Estimasi Harga (<?= date('Y') ?>)</div>
                                        <div>Rp. <?= number_format($data['a_estimasi_harga'] ?? 0, 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h6 class="fw-semibold mb-2">Spesifikasi</h6>
                                    <div class="bg-white border rounded p-2" style="min-height:80px;">
                                        <?= $data['ma_spesifikasi'] ? $data['ma_spesifikasi'] : '<span class="text-muted">-</span>' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h6 class="fw-semibold mb-2">Keterangan</h6>
                                    <div class="text-muted">
                                        <?= $data['a_keterangan'] ? htmlspecialchars($data['a_keterangan']) : '-' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h6 class="fw-semibold mb-2">File Dokumen</h6>
                                    <?php if (!empty($data['a_file_dokumen'])): ?>
                                        <button class="btn btn-sm btn-warning text-white border" data-bs-toggle="modal"
                                            data-bs-target="#modalFile<?= $data['a_id'] ?>">
                                            Lihat File
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">File tidak tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn rounded-pill btn-light border border-dark"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

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


    <!-- MODAL EDIT -->
    <?php foreach ($aset as $data) : ?>
        <div class="modal fade" id="modal-edit-aset-<?= $data['a_id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Aset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="../actions/aset/edit.php" method="post" enctype="multipart/form-data"
                        class="form-confirm-edit">
                        <div class="modal-body">
                            <div class="row">

                                <!-- HIDDEN ID -->
                                <input type="hidden" name="id" value="<?= $data['a_id'] ?>">

                                <!-- HIDDEN GAMBAR -->
                                <input type="hidden" name="gambar_lama" value="<?= $data['a_file_dokumen'] ?>">

                                <!-- HIDDEN FILTERS -->
                                <input type="hidden" name="p" value="<?= $p ?>">
                                <input type="hidden" name="ma_id" value="<?= $maId ?>">
                                <input type="hidden" name="filter_kelompok" value="<?= $filterKelompok ?>">
                                <input type="hidden" name="filter_kategori" value="<?= $filterKategori ?>">
                                <input type="hidden" name="filter_izin" value="<?= $filterIzinPinjam ?>">

                                <!-- MASTER ASET -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Aset*</label>
                                    <select id="master_aset_id_<?= $data['a_id'] ?>" class="form-control"
                                        name="master_aset_id" required>
                                        <option value="">Pilih Aset</option>
                                        <?php foreach ($master_aset as $ma): ?>
                                            <?php if ($ma['ma_is_active'] == 1): ?>
                                                <option value="<?= $ma['id'] ?>"
                                                    <?= ($ma['id'] == $data['a_master_aset_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ma['ma_nama']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- MERK -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Merk</label>
                                    <input id="merk_<?= $data['a_id'] ?>" type="text" class="form-control"
                                        value="<?= $data['ma_merk'] ?>" disabled>
                                </div>

                                <!-- SPESIFIKASI -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Spesifikasi</label>
                                    <div id="spesifikasi_view_<?= $data['a_id'] ?>" class="form-control"
                                        style="min-height:100px; background-color: #e8ecee">
                                        <?= $data['ma_spesifikasi'] ?>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Kelompok Aset</label>
                                    <input class="form-control kelompok_aset" id="kelompok_aset_<?= $data['a_id'] ?>"
                                        value="<?= $data['nama_kelompok'] ?>" disabled>
                                </div>

                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Kategori Aset</label>
                                    <input class="form-control kategori_aset" id="kategori_aset_<?= $data['a_id'] ?>"
                                        value="<?= $data['nama_kategori'] ?>" disabled>
                                </div>

                                <!-- FILE -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">File Dokumen</label>
                                    <input type="file" class="form-control" name="file_dokumen"
                                        accept=".pdf,.png,.jpg,.jpeg">
                                    <?php if (!empty($data['a_file_dokumen'])): ?>
                                        <div class="mt-1">
                                            <a href="../assets/uploads/dokumen_file/<?= $data['a_file_dokumen'] ?>"
                                                target="_blank" download="<?= $data['a_file_dokumen'] ?>"
                                                style="text-decoration: none;"> <small
                                                    style="display:inline-block; max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                    [Unduh] File saat ini: <?= $data['a_file_dokumen'] ?>
                                                </small>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <span class="form-text fst-italic">Format file: PDF, PNG, JPG, JPEG (Max 20 MB)</span>
                                </div>

                                <!-- TANGGAL -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Tanggal Perolehan*</label>
                                    <input type="date" class="form-control" name="tanggal_perolehan"
                                        value="<?= $data['a_tgl_perolehan'] ?>" required>
                                </div>

                                <!-- HARGA -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Harga Perolehan*</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border border-end-0">Rp.
                                        </span>
                                        <input type="text" class="form-control border border-start-0"
                                            id="harga_perolehan_<?= $data['a_id'] ?>" name="harga_perolehan"
                                            inputmode="numeric" required value="<?= $data['a_harga_perolehan'] ?>">
                                    </div>
                                </div>

                                <!-- ESTIMASI -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Estimasi Harga*</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border border-end-0">Rp.
                                        </span>
                                        <input type="text" class="form-control border border-start-0"
                                            id="estimasi_harga_<?= $data['a_id'] ?>" name="estimasi_harga"
                                            value="<?= $data['a_estimasi_harga'] ?>" inputmode="numeric" required>
                                    </div>
                                </div>

                                <!-- RUANGAN -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Lokasi (Ruangan)</label>
                                    <select class="form-control" name="ruangan_id">
                                        <option value="">Pilih Ruangan</option>
                                        <?php foreach ($ruangan as $r): ?>
                                            <option value="<?= $r['id'] ?>"
                                                <?= ($r['id'] == $data['a_lokasi_ruangan_id']) ? 'selected' : '' ?>>
                                                <?= $r['r_nama'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- LOKASI -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" name="lokasi" value="<?= $data['a_lokasi'] ?>">
                                </div>

                                <!-- STATUS -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Status Aset*</label>
                                    <select class="form-control" name="status_aset" required>
                                        <option value="terpakai"
                                            <?= $data['a_status_aset'] == 'terpakai' ? 'selected' : '' ?>>
                                            Terpakai</option>
                                        <option value="tidak_terpakai"
                                            <?= $data['a_status_aset'] == 'tidak_terpakai' ? 'selected' : '' ?>>Tidak
                                            Terpakai
                                        </option>
                                        <option value="write_off"
                                            <?= $data['a_status_aset'] == 'write_off' ? 'selected' : '' ?>>
                                            Write Off</option>
                                    </select>
                                </div>

                                <!-- KONDISI -->
                                <div class="col-12 col-lg-6 mb-3">
                                    <label class="form-label">Kondisi Aset*</label>
                                    <select class="form-control" name="kondisi_aset" required>
                                        <option value="baik" <?= $data['a_kondisi_aset'] == 'baik' ? 'selected' : '' ?>>
                                            Baik
                                        </option>
                                        <option value="cukup" <?= $data['a_kondisi_aset'] == 'cukup' ? 'selected' : '' ?>>
                                            Cukup
                                        </option>
                                        <option value="rusak_ringan"
                                            <?= $data['a_kondisi_aset'] == 'rusak_ringan' ? 'selected' : '' ?>>Rusak
                                            Ringan
                                        </option>
                                        <option value="rusak_sedang"
                                            <?= $data['a_kondisi_aset'] == 'rusak_sedang' ? 'selected' : '' ?>>Rusak
                                            Sedang
                                        </option>
                                        <option value="rusak_berat"
                                            <?= $data['a_kondisi_aset'] == 'rusak_berat' ? 'selected' : '' ?>>Rusak
                                            Berat
                                        </option>
                                    </select>
                                </div>

                                <!-- KETERANGAN -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control"><?= $data['a_keterangan'] ?></textarea>
                                </div>

                                <!-- RADIO BOLEH PINJAM -->
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                            name="boleh_pinjam_<?= $data['a_id'] ?>"
                                            id="status-boleh-dipinjam-<?= $data['a_id'] ?>" value="1"
                                            <?= (int) $data['a_boleh_dipinjam'] === 1 ? 'checked' : '' ?> required>
                                        <label class="form-check-label text-success fw-bold"
                                            for="status-boleh-dipinjam-<?= $data['a_id'] ?>">Boleh Dipinjam</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                            name="boleh_pinjam_<?= $data['a_id'] ?>"
                                            id="status-tidak-boleh-dipinjam-<?= $data['a_id'] ?>" value="0"
                                            <?= (int)$data['a_boleh_dipinjam'] === 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label text-danger fw-bold"
                                            for="status-tidak-boleh-dipinjam-<?= $data['a_id'] ?>">Tidak Boleh
                                            Dipinjam</label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>




<!-- Auto Fill Modal Tambah Data -->
<script>
    const masterAset = <?= json_encode($master_aset, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    document.querySelectorAll('[id^="master_aset_id_"]').forEach(select => {
        select.addEventListener("change", function() {
            const id = this.value;
            const modal = this.closest('.modal');

            const data = masterAset.find(a => a.id == id);
            if (!data) return;

            modal.querySelector('[id^="merk_"]').value = data.ma_merk || '';
            modal.querySelector('[id^="spesifikasi_view_"]').innerHTML = data.ma_spesifikasi || '';
            modal.querySelector('[id^="kelompok_aset_"]').value = data.nama_kelompok || '';
            modal.querySelector('[id^="kategori_aset_"]').value = data.nama_kategori || '';
        });
    });
</script>

<!-- Validasi Numeric Input -->
<script>
    document.querySelectorAll('[id^="harga_perolehan_"], [id^="estimasi_harga_"]').forEach(function(el) {
        el.addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
</script>

<script>
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