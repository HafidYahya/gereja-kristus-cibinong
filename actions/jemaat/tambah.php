<?php
include "../../config/koneksi.php";
$nama = $_POST['nama'];
$email = trim(strtolower($_POST['email']));
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$no_hp = $_POST['no_hp'];
$alamat = $_POST['alamat'];
$fotoProfile = $_FILES['foto_profile'];


// Validasi input
if (empty($nama) || empty($email) || empty($password) || empty($confirm_password) || empty($no_hp) || empty($alamat)) {
    header("Location: /gerejakristuscibinong/register?error=Data+tidak+lengkap");
    exit;
}
// cek upload foto profile
if (empty($fotoProfile) || $fotoProfile['error'] === UPLOAD_ERR_NO_FILE) {
    header("Location: /gerejakristuscibinong/register?error=Foto+profile+tidak+diunggah");
    exit;
}
// Cek apakah password dan konfirmasi password cocok
if ($password !== $confirm_password) {
    header("Location: /gerejakristuscibinong/register?error=Konfirmasi+password+tidak+sesuai");
    exit;
}
// Cek apakah email sudah terdaftar
$stmt = $conn->prepare("SELECT id FROM jemaat WHERE j_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: /gerejakristuscibinong/register?error=Email+sudah+terdaftar");
    exit;
}
// Cek apakah nomor handphone sudah terdaftar
$stmt = $conn->prepare("SELECT id FROM jemaat WHERE j_no_hp = ?");
$stmt->bind_param("s", $no_hp);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: /gerejakristuscibinong/register?error=Nomor+handphone+sudah+terdaftar");
    exit;
}
// Proses upload foto profile
$fotoProfileName = uniqid() . '_' . basename($fotoProfile['name']);
$fotoProfileTmp = $fotoProfile['tmp_name'];
$fotoProfileSize = $fotoProfile['size'];
$fotoProfileError = $fotoProfile['error'];
$fotoProfileExt = strtolower(pathinfo($fotoProfileName, PATHINFO_EXTENSION));
$allowedExt = ['jpg', 'jpeg', 'png'];
// Cek format file
if (!in_array($fotoProfileExt, $allowedExt)) {
    header("Location: /gerejakristuscibinong/register?error=Format+foto+profile+tidak+didukung");
    exit;
}
// Cek ukuran file (maksimal 2MB)
if ($fotoProfileSize > 2 * 1024 * 1024) {
    header("Location: /gerejakristuscibinong/register?error=Ukuran+foto+profile+terlalu+besar");
    exit;
}
if (!move_uploaded_file($fotoProfileTmp, "../../assets/uploads/jemaat/" . $fotoProfileName)) {
    header("Location: /gerejakristuscibinong/register?error=Gagal+mengunggah+foto+profile");
    exit;
}
// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
// Simpan data ke database
$stmt = $conn->prepare("INSERT INTO jemaat (j_nama, j_password, j_no_hp, j_email, j_alamat, j_foto) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $nama, $passwordHash, $no_hp, $email, $alamat, $fotoProfileName);
if ($stmt->execute()) {
    header("Location: /gerejakristuscibinong/login?success=Registrasi+berhasil");
    exit;
} else {
    header("Location: /gerejakristuscibinong/register?error=Gagal+menyimpan+data");
    exit;
}
