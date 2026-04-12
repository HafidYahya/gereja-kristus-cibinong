<?php
include __DIR__ . "/../../config/koneksi.php";
$id = trim($_POST['id'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$status = isset($_POST['status']) ? (int) $_POST['status'] : 0;

if ($id === '' || $email === '' || $no_hp === '') {
    header('Location:../../admin/index.php?page=jemaat&action=update&error=Data+tidak+boleh+kosong');
    exit();
}

// Cek apakah email sudah terdaftar untuk jemaat lain
$stmt = $conn->prepare('SELECT id FROM jemaat WHERE j_email = ? AND id <> ? LIMIT 1');
$stmt->bind_param('si', $email, $id);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

if ($exists) {
    header('Location:../../admin/index.php?page=jemaat&action=update&error=Email+sudah+terdaftar');
    exit();
}
// Cek apakah no_hp sudah terdaftar untuk jemaat lain
$stmt = $conn->prepare('SELECT id FROM jemaat WHERE j_no_hp = ? AND id <> ? LIMIT 1');
$stmt->bind_param('si', $no_hp, $id);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

if ($exists) {
    header('Location:../../admin/index.php?page=jemaat&action=update&error=No Hp sudah terdaftar');
    exit();
}

if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE jemaat SET j_email = ?, j_no_hp = ?, j_password = ?, j_is_active = ? WHERE id = ?');
    $stmt->bind_param('sssii', $email, $no_hp, $hash, $status, $id);
} else {
    $stmt = $conn->prepare('UPDATE jemaat SET j_email = ?, j_no_hp = ?, j_is_active = ? WHERE id = ?');
    $stmt->bind_param('ssii', $email, $no_hp, $status, $id);
}

$stmt->execute();
$stmt->close();

header('Location:../../admin/index.php?page=jemaat&action=update&success=Data+berhasil+diubah');
