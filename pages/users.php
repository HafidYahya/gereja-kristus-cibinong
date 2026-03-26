<?php

$filter = $_POST['filter-status'] ?? $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');
// routing
$currentPage = $_GET['page'] ?? 'users';
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
    $where[] = "u_is_active = ?";
    $types .= "i";
    $params[] = (int) $filter;
}

if ($search !== '') {
    $where[] = "(u_nama LIKE ? OR u_email LIKE ?)";
    $types .= "ss";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT * FROM users $whereSql LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$typesData = $types . "ii";
$paramsData = array_merge($params, [$limit, $offset]);
$stmt->bind_param($typesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$sqlTotal = "SELECT COUNT(*) as total FROM users $whereSql";
$stmtTotal = $conn->prepare($sqlTotal);
if ($types !== '') {
    $stmtTotal->bind_param($types, ...$params);
}
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalData = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalData / $limit);

$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="p-4">
    <div class="container border border-warning border-top-0 border-bottom-0 bg-white p-3 rounded shadow mb-4">
        <div class="page-header row g-2 align-items-center">
            <div class="col-12 col-sm">
                <h1 class="page-title mb-0">Kelola Pengguna</h1>
            </div>
            <div class="col-12 col-sm-auto">
                <button type="button"
                    class="btn btn-white border border-warning rounded-pill shadow-sm w-100 d-flex align-items-center justify-content-center"
                    data-bs-toggle="modal" data-bs-target="#modal-tambah"><i class="fas fa-plus me-2"></i>Tambah
                    Pengguna</button>
            </div>
        </div>
    </div>
    <form method="GET">
        <div class="row mb-3">
            <div class="col-sm-12 col-lg-4">
                <label class="form-label" for=""><i class="fas fa-filter"></i> Filter</label>
                <input type="hidden" name="page" value="users">
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
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php if (empty($users)) : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-file-circle-xmark me-3"></i>Data tidak ditemukan
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($user['u_nama']) ?></td>
                                <td><?= htmlspecialchars($user['u_email']) ?></td>
                                <td>
                                    <span
                                        class="badge badge-sm <?= (int) $user['u_is_active'] === 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= (int) $user['u_is_active'] === 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm shadow-md" data-bs-toggle="modal"
                                        data-bs-target="#modal-edit-<?= $user['id'] ?>">
                                        <i class="far fa-pen-to-square"></i>
                                    </button>
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
                    href="index.php?page=users&p=<?= $p - 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
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
                        href="index.php?page=users&p=<?= $i ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next -->
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="index.php?page=users&p=<?= $p + 1 ?>&filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>
    <!-- MODAL TAMBAH -->
    <?php foreach ($users as $user) : ?>
        <div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true" aria-labelledby="modal-tambah-label">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="modal-tambah-label">Tambah Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="actions/users/tambah.php" method="post" class="form-confirm-tambah">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama*</label>
                                <input type="text" class="form-control" name="nama" placeholder="Masukan nama lengkap"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email*</label>
                                <input type="email" class="form-control" name="email" placeholder="Masukan email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password*</label>
                                <input type="password" class="form-control" name="password" placeholder="Masukan password"
                                    id="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="showPassword">
                                <label class="form-check-label" for="showPassword">Show password</label>
                            </div>
                            <input type="hidden" name="status" value="1">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn rounded-pill btn-light border border-dark"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn rounded-pill btn-warning">Tambah Pengguna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <!-- MODAL EDTI -->
    <?php foreach ($users as $user) : ?>
        <div class="modal fade" id="modal-edit-<?= $user['id'] ?>" tabindex="-1" aria-hidden="true"
            aria-labelledby="modal-edit-label-<?= $user['id'] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="modal-edit-label-<?= $user['id'] ?>">Ubah Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="actions/users/edit.php" method="post" class="form-confirm-edit">
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama*</label>
                                <input type="text" class="form-control" name="nama"
                                    value="<?= htmlspecialchars($user['u_nama']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email*</label>
                                <input type="email" class="form-control" name="email"
                                    value="<?= htmlspecialchars($user['u_email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Baru (opsional)</label>
                                <input type="password" class="form-control" name="password" id="passwordEdit"
                                    placeholder="Kosongkan jika tidak diganti">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="showPasswordEdit">
                                <label class="form-check-label" for="showPasswordEdit">Show password</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status*</label>
                                <select class="form-select" name="status">
                                    <option value="1" <?= (int) $user['u_is_active'] === 1 ? 'selected' : '' ?>>
                                        Aktif
                                    </option>
                                    <option value="0" <?= (int) $user['u_is_active'] === 0 ? 'selected' : '' ?>>
                                        Tidak Aktif
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn rounded-pill btn-light border border-dark"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn rounded-pill btn-warning">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<script>
    const showPasswordCheckbox = document.getElementById('showPassword');
    const showPasswordEditCheckbox = document.getElementById('showPasswordEdit');
    const passwordInput = document.getElementById('password');
    const passwordInputEdit = document.getElementById('passwordEdit');

    if (showPasswordCheckbox && passwordInput) {
        showPasswordCheckbox.addEventListener('change', function() {
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    }
    if (showPasswordEditCheckbox && passwordInputEdit) {
        showPasswordEditCheckbox.addEventListener('change', function() {
            passwordInputEdit.type = this.checked ? 'text' : 'password';
        });
    }
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