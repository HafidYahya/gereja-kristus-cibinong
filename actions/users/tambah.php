<?php
include __DIR__ . "/../../config/koneksi.php";
$nama = trim($_POST['nama']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$status = trim($_POST['status']);

$stmt = $conn->prepare("SELECT * FROM users WHERE u_email=?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$email_exist = $result && $result->num_rows > 0;
$stmt->close();
if ($email_exist) {
    header('Location: ../../admin/index.php?page=users&action=tambah&tambah&error=Email+sudah+terdaftar');
    exit();
}
$password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (u_nama, u_email, u_password, u_is_active) VALUES (?,?,?,?)");
$stmt->bind_param('sssi', $nama, $email, $password, $status);
$stmt->execute();
$stmt->close();
header('Location: ../../admin/index.php?page=users&action=tambah&tambah&success=Pengguna+baru+berhasil+ditambahkan');

