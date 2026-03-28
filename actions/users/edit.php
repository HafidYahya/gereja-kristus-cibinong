<?php
include __DIR__ . "/../../config/koneksi.php";
$id = trim($_POST['id'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$status = isset($_POST['status']) ? (int) $_POST['status'] : 0;

if ($id === '' || $nama === '' || $email === '') {
    header('Location:../../admin/index.php?page=users&action=update&error=Data+tidak+boleh+kosong');
    exit();
}

$stmt = $conn->prepare('SELECT id FROM users WHERE u_email = ? AND id <> ? LIMIT 1');
$stmt->bind_param('si', $email, $id);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

if ($exists) {
    header('Location:../../admin/index.php?page=users&action=update&error=Email+sudah+terdaftar');
    exit();
}

if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET u_nama = ?, u_email = ?, u_password = ?, u_is_active = ? WHERE id = ?');
    $stmt->bind_param('sssii', $nama, $email, $hash, $status, $id);
} else {
    $stmt = $conn->prepare('UPDATE users SET u_nama = ?, u_email = ?, u_is_active = ? WHERE id = ?');
    $stmt->bind_param('ssii', $nama, $email, $status, $id);
}

$stmt->execute();
$stmt->close();

header('Location:../../admin/index.php?page=users&action=update&success=Data+berhasil+diubah');

