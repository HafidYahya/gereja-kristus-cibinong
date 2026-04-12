<?php

$filter = $_POST['filter-status'] ?? $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');
// routing
$currentPage = $_GET['page'] ?? 'ruangan';
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
    $where[] = "r_is_active = ?";
    $types .= "i";
    $params[] = (int) $filter;
}

if ($search !== '') {
    $where[] = "(r_nama LIKE ?)";
    $types .= "s";
    $like = "%" . $search . "%";
    $params[] = $like;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT * FROM ruangan $whereSql ORDER BY r_is_active DESC, id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$typesData = $types . "ii";
$paramsData = array_merge($params, [$limit, $offset]);
$stmt->bind_param($typesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$sqlTotal = "SELECT COUNT(*) as total FROM ruangan $whereSql";
$stmtTotal = $conn->prepare($sqlTotal);
if ($types !== '') {
    $stmtTotal->bind_param($types, ...$params);
}
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalData = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalData / $limit);

$ruangan = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="p-4">
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">
        <div class="page-header row g-2 align-items-center">
            <div class="col-12 col-sm">
                <h1 class="page-title mb-0">Kelola Ruangan</h1>
            </div>
            <div class="col-12 col-sm-auto">
                <button type="button"
                    class="btn btn-white border border-warning rounded-pill shadow-sm w-100 d-flex align-items-center justify-content-center"
                    data-bs-toggle="modal" data-bs-target="#modal-tambah-ruangan"><i class="fas fa-plus me-2"></i>Tambah
                    Ruangan</button>
            </div>
        </div>
    </div>
    <form method="GET">
        <div class="row mb-3">
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Status</label>
                <input type="hidden" name="page" value="ruangan">
                <select name="filter" class="form-select border border-warning">
                    <option value="">Semua Status</option>
                    <option value="1" <?= $filter === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $filter === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-sm-12 col-lg-4">
                <label class="form-label invisible">Seach Invisible</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border border-warning border-end-0" id="search"><i
                            class="fas fa-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 border border-warning"
                        placeholder="Cari nama ruangan" aria-label="Search" aria-describedby="search"
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
                        <th>Nama Ruangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php if (empty($ruangan)) : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-file-circle-xmark me-3"></i>Data tidak ditemukan
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($ruangan as $data) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($data['r_nama']) ?></td>
                                <td>
                                    <span style="min-width: 100px"
                                        class="badge badge-sm <?= (int) $data['r_is_active'] === 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= (int) $data['r_is_active'] === 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm shadow-md" data-bs-toggle="modal"
                                        data-bs-target="#modal-edit-ruangan-<?= $data['id'] ?>">
                                        <i class="far fa-pen-to-square"></i>
                                    </button>
                                    <form action="../actions/ruangan/delete.php" method="post"
                                        class="d-inline form-confirm-delete">
                                        <input type="hidden" name="id" value="<?= (int) $data['id'] ?>">
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
                    href="index.php?page=ruangan&p=<?= $p - 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
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
                        href="index.php?page=ruangan&p=<?= $i ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=ruangan&p=<?= $p + 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>
    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="modal-tambah-ruangan" tabindex="-1" aria-hidden="true"
        aria-labelledby="modal-tambah-ruangan-label">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modal-tambah-ruangan-label">Tambah Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../actions/ruangan/tambah.php" method="post" class="form-confirm-tambah"
                    enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Ruangan*</label>
                            <input type="text" class="form-control" name="nama_ruangan"
                                placeholder="Masukan nama ruangan" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                placeholder="Tambahkan keterangan (Opsional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload Gambar (Landscape 16:9, Max 20MB)</label>

                            <div class="border border-warning rounded bg-light p-2" id="ruanganUploadZone">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center"
                                        style="width:44px;height:44px;">
                                        <i class="fas fa-image text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">Seret & lepas gambar di sini</div>
                                        <div class="text-muted small">Rasio 16:9, JPG/PNG, maks 20MB</div>
                                        <div class="text-muted small" id="ruanganFileName">Belum ada file dipilih</div>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm" type="button"
                                        id="ruanganBrowseBtn">Pilih Gambar</button>
                                </div>

                                <input type="file" id="ruanganFileInput" name="gambar_ruangan" accept="image/*"
                                    class="d-none">
                                <small class="text-danger d-block mt-2" id="ruanganErrorMsg"></small>

                                <div class="mt-2">
                                    <div class="border rounded bg-white overflow-hidden"
                                        style="aspect-ratio:16/9; width:100%; max-height:240px;">
                                        <img id="ruanganImage" class="w-100 h-100 d-none"
                                            style="object-fit:contain; display:block;">
                                        <div id="ruanganPlaceholder"
                                            class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                            Preview gambar
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm d-none"
                                        id="ruanganResetCrop">Ganti Gambar</button>
                                    <div class="d-flex align-items-center gap-2 small text-muted">
                                        <span id="ruanganCropHint"></span>
                                        <span id="ruanganCropLoading" class="d-none">
                                            <span class="spinner-border spinner-border-sm" role="status"
                                                aria-hidden="true"></span>
                                            Memproses...
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <input type="hidden" name="status" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn rounded-pill btn-light border border-dark"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn rounded-pill btn-warning" id="ruanganSubmitBtn">Tambah
                            Ruangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- MODAL EDTI -->
    <?php foreach ($ruangan as $data): ?>
        <?php
        $fotoRuangan = $data['r_foto'] ?? '';
        $hasFotoRuangan = $fotoRuangan !== '' && $fotoRuangan !== 'placeholder_ruangan';
        $fotoRuanganUrl = $hasFotoRuangan ? '../assets/uploads/ruangan/' . $fotoRuangan : '';
        ?>
        <div class="modal fade ruangan-edit-modal" id="modal-edit-ruangan-<?= $data['id'] ?>" tabindex="-1"
            aria-hidden="true" aria-labelledby="modal-edit-ruangan-label" data-ruangan-id="<?= (int) $data['id'] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="modal-edit-ruangan-label-<?= $data['id'] ?>">Ubah Ruangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="../actions/ruangan/edit.php" method="post" class="form-confirm-edit"
                        enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?= (int) $data['id'] ?>">
                            <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($data['r_foto'] ?? '') ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Ruangan*</label>
                                <input type="text" class="form-control" name="nama_ruangan"
                                    placeholder="Masukan nama ruangan" value="<?= htmlspecialchars($data['r_nama']) ?>"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="3"
                                    placeholder="Tambahkan keterangan (Opsional)"><?= htmlspecialchars($data['r_keterangan'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ganti Gambar (Opsional)</label>
                                <div class="border border-warning rounded bg-light p-2 ruangan-edit-upload-zone">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center"
                                            style="width:44px;height:44px;">
                                            <i class="fas fa-image text-warning"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">Seret & lepas gambar di sini</div>
                                            <div class="text-muted small">Rasio 16:9, JPG/PNG, maks 20MB</div>
                                            <div class="text-muted small ruangan-edit-file-name">Belum ada file dipilih
                                            </div>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm ruangan-edit-browse" type="button">
                                            Pilih Gambar</button>
                                    </div>

                                    <input type="file" name="gambar_ruangan" accept="image/*"
                                        class="d-none ruangan-edit-file-input">
                                    <small class="text-danger d-block mt-2 ruangan-edit-error"></small>

                                    <div class="mt-2">
                                        <div class="border rounded bg-white overflow-hidden"
                                            style="aspect-ratio:16/9; width:100%; max-height:240px;">
                                            <img class="w-100 h-100 ruangan-edit-image <?= $hasFotoRuangan ? '' : 'd-none' ?>"
                                                style="object-fit:contain; display:block;"
                                                src="<?= htmlspecialchars($fotoRuanganUrl) ?>">
                                            <div
                                                class="w-100 h-100 d-flex align-items-center justify-content-center text-muted ruangan-edit-placeholder <?= $hasFotoRuangan ? 'd-none' : '' ?>">
                                                Preview gambar
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <button type="button"
                                            class="btn btn-outline-secondary btn-sm d-none ruangan-edit-reset">Ganti
                                            Gambar</button>
                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                            <span class="ruangan-edit-crop-hint"></span>
                                            <span class="d-none ruangan-edit-crop-loading">
                                                <span class="spinner-border spinner-border-sm" role="status"
                                                    aria-hidden="true"></span>
                                                Memproses...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($data['r_foto'])) : ?>
                                    <div class="mt-2 small text-muted">
                                        File saat ini: <?= htmlspecialchars($data['r_foto']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?= (int) $data['r_is_active'] === 1 ? 'selected' : '' ?>>Aktif
                                    </option>
                                    <option value="0" <?= (int) $data['r_is_active'] === 0 ? 'selected' : '' ?>>Tidak
                                        Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn rounded-pill btn-light border border-dark"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn rounded-pill btn-warning ruangan-edit-submit">Simpan
                                Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>
<!-- ===================CROPPER & VALIDASI -->
<script>
    const ruanganBrowseBtn = document.getElementById('ruanganBrowseBtn');
    const ruanganFileInput = document.getElementById('ruanganFileInput');
    const ruanganFileName = document.getElementById('ruanganFileName');
    const ruanganErrorMsg = document.getElementById('ruanganErrorMsg');
    const ruanganImage = document.getElementById('ruanganImage');
    const ruanganPlaceholder = document.getElementById('ruanganPlaceholder');
    const ruanganResetCrop = document.getElementById('ruanganResetCrop');
    const ruanganUploadZone = document.getElementById('ruanganUploadZone');
    const ruanganSubmitBtn = document.getElementById('ruanganSubmitBtn');
    const ruanganCropHint = document.getElementById('ruanganCropHint');
    const ruanganCropLoading = document.getElementById('ruanganCropLoading');

    let ruanganCropper = null;
    let ruanganObjectUrl = null;
    let ruanganPreviewUrl = null;
    let ruanganCropTimeout = null;
    let ruanganLoadingTimeout = null;
    const CropperCtorKey = 'Cro' + 'pper';
    const DataTransferCtorKey = 'Data' + 'Transfer';
    const FileCtorKey = 'F' + 'ile';

    const resetRuanganUpload = () => {
        ruanganErrorMsg.textContent = "";
        ruanganFileName.textContent = "Belum ada file dipilih";
        ruanganPlaceholder.classList.remove("d-none");
        ruanganImage.classList.add("d-none");
        ruanganResetCrop.classList.add("d-none");
        ruanganCropHint.textContent = "";
        ruanganCropLoading.classList.add("d-none");
        if (ruanganLoadingTimeout) {
            clearTimeout(ruanganLoadingTimeout);
            ruanganLoadingTimeout = null;
        }
        if (ruanganObjectUrl) {
            URL.revokeObjectURL(ruanganObjectUrl);
            ruanganObjectUrl = null;
        }
        if (ruanganPreviewUrl) {
            URL.revokeObjectURL(ruanganPreviewUrl);
            ruanganPreviewUrl = null;
        }
        if (ruanganCropper) {
            ruanganCropper.destroy();
            ruanganCropper = null;
        }
    };

    const setCroppedFile = (blob) => {
        const file = new window[FileCtorKey]([blob], `ruangan_${Date.now()}.jpg`, {
            type: "image/jpeg"
        });
        const dataTransfer = new window[DataTransferCtorKey]();
        dataTransfer.items.add(file);
        ruanganFileInput.files = dataTransfer.files;
    };

    const applyAutoCrop = () => {
        if (!ruanganCropper) return;
        if (ruanganLoadingTimeout) {
            clearTimeout(ruanganLoadingTimeout);
            ruanganLoadingTimeout = null;
        }
        const canvas = ruanganCropper.getCroppedCanvas({
            width: 1280,
            height: 720
        });
        canvas.toBlob((blob) => {
            if (!blob) return;
            setCroppedFile(blob);
            if (ruanganPreviewUrl) {
                URL.revokeObjectURL(ruanganPreviewUrl);
            }
            ruanganPreviewUrl = URL.createObjectURL(blob);
            ruanganCropHint.textContent = "Hasil crop tersimpan otomatis.";
            ruanganCropLoading.classList.add("d-none");
            ruanganSubmitBtn.disabled = false;
        }, "image/jpeg", 0.92);
    };

    const scheduleAutoCrop = () => {
        if (ruanganCropTimeout) {
            clearTimeout(ruanganCropTimeout);
        }
        if (ruanganLoadingTimeout) {
            clearTimeout(ruanganLoadingTimeout);
        }
        ruanganLoadingTimeout = setTimeout(() => {
            ruanganCropLoading.classList.remove("d-none");
        }, 200);
        ruanganCropTimeout = setTimeout(applyAutoCrop, 150);
    };

    const initCropper = (srcUrl) => {
        ruanganImage.classList.remove("d-none");
        ruanganPlaceholder.classList.add("d-none");
        ruanganResetCrop.classList.remove("d-none");
        ruanganCropHint.textContent = "Atur area crop, hasil disimpan otomatis.";

        ruanganImage.onload = () => {
            ruanganImage.onload = null;
            if (ruanganCropper) {
                ruanganCropper.destroy();
            }
            ruanganCropper = new window[CropperCtorKey](ruanganImage, {
                aspectRatio: 16 / 9,
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                ready() {
                    scheduleAutoCrop();
                },
                cropend() {
                    scheduleAutoCrop();
                }
            });
        };

        ruanganImage.src = srcUrl;
    };

    const handleRuanganFile = (file) => {
        ruanganErrorMsg.textContent = "";
        if (!file) return;

        if (typeof window[CropperCtorKey] === 'undefined') {
            ruanganErrorMsg.textContent = "Cropper.js belum dimuat. Cek layout/header.";
            return;
        }

        if (!file.type.startsWith("image/")) {
            ruanganErrorMsg.textContent = "File harus berupa gambar.";
            return;
        }
        if (file.size > 20 * 1024 * 1024) {
            ruanganErrorMsg.textContent = "Ukuran maksimal 20MB.";
            return;
        }

        ruanganFileName.textContent = file.name;
        if (ruanganObjectUrl) {
            URL.revokeObjectURL(ruanganObjectUrl);
        }
        ruanganObjectUrl = URL.createObjectURL(file);
        initCropper(ruanganObjectUrl);
        ruanganSubmitBtn.disabled = true;
    };

    ruanganBrowseBtn.addEventListener('click', () => ruanganFileInput.click());

    ruanganFileInput.addEventListener('change', function() {
        const file = this.files[0];
        handleRuanganFile(file);
    });

    ruanganResetCrop.addEventListener('click', function() {
        ruanganFileInput.value = "";
        resetRuanganUpload();
        ruanganSubmitBtn.disabled = false;
    });

    ["dragenter", "dragover"].forEach((evt) => {
        ruanganUploadZone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            ruanganUploadZone.classList.add("border-primary");
        });
    });

    ["dragleave", "drop"].forEach((evt) => {
        ruanganUploadZone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            ruanganUploadZone.classList.remove("border-primary");
        });
    });

    ruanganUploadZone.addEventListener('drop', (e) => {
        const file = e.dataTransfer.files[0];
        if (file) {
            ruanganFileInput.files = e.dataTransfer.files;
            handleRuanganFile(file);
        }
    });

    resetRuanganUpload();

    // =================== CROPPER EDIT PER MODAL ===================
    const initEditCropper = (modalEl) => {
        if (!modalEl || modalEl.dataset.editCropInit === '1') {
            return;
        }
        modalEl.dataset.editCropInit = '1';

        const browseBtn = modalEl.querySelector('.ruangan-edit-browse');
        const fileInput = modalEl.querySelector('.ruangan-edit-file-input');
        const fileName = modalEl.querySelector('.ruangan-edit-file-name');
        const errorMsg = modalEl.querySelector('.ruangan-edit-error');
        const image = modalEl.querySelector('.ruangan-edit-image');
        const placeholder = modalEl.querySelector('.ruangan-edit-placeholder');
        const resetBtn = modalEl.querySelector('.ruangan-edit-reset');
        const uploadZone = modalEl.querySelector('.ruangan-edit-upload-zone');
        const submitBtn = modalEl.querySelector('.ruangan-edit-submit');
        const cropHint = modalEl.querySelector('.ruangan-edit-crop-hint');
        const cropLoading = modalEl.querySelector('.ruangan-edit-crop-loading');

        if (!browseBtn || !fileInput || !fileName || !errorMsg || !image || !placeholder || !resetBtn || !uploadZone) {
            return;
        }

        let cropper = null;
        let objectUrl = null;
        let previewUrl = null;
        let cropTimeout = null;
        let loadingTimeout = null;
        const defaultSrc = image.getAttribute('src') || '';

        const setSubmitState = (disabled) => {
            if (submitBtn) {
                submitBtn.disabled = disabled;
            }
        };

        const resetEditUpload = (toDefault = true) => {
            errorMsg.textContent = "";
            fileName.textContent = "Belum ada file dipilih";
            cropHint.textContent = "";
            cropLoading.classList.add("d-none");
            resetBtn.classList.add("d-none");
            setSubmitState(false);

            if (loadingTimeout) {
                clearTimeout(loadingTimeout);
                loadingTimeout = null;
            }
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            fileInput.value = "";

            if (toDefault && defaultSrc) {
                image.src = defaultSrc;
                image.classList.remove("d-none");
                placeholder.classList.add("d-none");
            } else {
                image.classList.add("d-none");
                placeholder.classList.remove("d-none");
            }
        };

        const setCroppedFile = (blob) => {
            const file = new window[FileCtorKey]([blob], `ruangan_${Date.now()}.jpg`, {
                type: "image/jpeg"
            });
            const dataTransfer = new window[DataTransferCtorKey]();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
        };

        const applyAutoCrop = () => {
            if (!cropper) return;
            if (loadingTimeout) {
                clearTimeout(loadingTimeout);
                loadingTimeout = null;
            }
            const canvas = cropper.getCroppedCanvas({
                width: 1280,
                height: 720
            });
            canvas.toBlob((blob) => {
                if (!blob) return;
                setCroppedFile(blob);
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }
                previewUrl = URL.createObjectURL(blob);
                cropHint.textContent = "Hasil crop tersimpan otomatis.";
                cropLoading.classList.add("d-none");
                setSubmitState(false);
            }, "image/jpeg", 0.92);
        };

        const scheduleAutoCrop = () => {
            if (cropTimeout) {
                clearTimeout(cropTimeout);
            }
            if (loadingTimeout) {
                clearTimeout(loadingTimeout);
            }
            loadingTimeout = setTimeout(() => {
                cropLoading.classList.remove("d-none");
            }, 200);
            cropTimeout = setTimeout(applyAutoCrop, 150);
        };

        const initCropper = (srcUrl) => {
            image.classList.remove("d-none");
            placeholder.classList.add("d-none");
            resetBtn.classList.remove("d-none");
            cropHint.textContent = "Atur area crop, hasil disimpan otomatis.";

            image.onload = () => {
                image.onload = null;
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new window[CropperCtorKey](image, {
                    aspectRatio: 16 / 9,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    ready() {
                        scheduleAutoCrop();
                    },
                    cropend() {
                        scheduleAutoCrop();
                    }
                });
            };

            image.src = srcUrl;
        };

        const handleFile = (file) => {
            errorMsg.textContent = "";
            if (!file) return;

            if (typeof window[CropperCtorKey] === 'undefined') {
                errorMsg.textContent = "Cropper.js belum dimuat. Cek layout/header.";
                return;
            }
            if (!file.type.startsWith("image/")) {
                errorMsg.textContent = "File harus berupa gambar.";
                return;
            }
            if (file.size > 20 * 1024 * 1024) {
                errorMsg.textContent = "Ukuran maksimal 20MB.";
                return;
            }

            fileName.textContent = file.name;
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
            objectUrl = URL.createObjectURL(file);
            initCropper(objectUrl);
            setSubmitState(true);
        };

        browseBtn.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            handleFile(file);
        });
        resetBtn.addEventListener('click', function() {
            resetEditUpload(true);
        });

        ["dragenter", "dragover"].forEach((evt) => {
            uploadZone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.add("border-primary");
            });
        });

        ["dragleave", "drop"].forEach((evt) => {
            uploadZone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.remove("border-primary");
            });
        });

        uploadZone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file) {
                fileInput.files = e.dataTransfer.files;
                handleFile(file);
            }
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
            resetEditUpload(true);
        });
    };

    document.querySelectorAll('.ruangan-edit-modal').forEach((modalEl) => {
        initEditCropper(modalEl);
    });
</script>

<script>
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