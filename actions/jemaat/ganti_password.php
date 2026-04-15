<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";

if (!isset($_SESSION['jemaat_id']) || empty($_SESSION['jemaat_id'])) {
    header("Location: /gerejakristuscibinong/login?error=Silahkan+login+terlebih+dahulu");
    exit();
}

$jemaatId = (int) $_SESSION['jemaat_id'];
$passwordLama = trim($_POST['password_lama'] ?? '');
$passwordBaru = trim($_POST['password_baru'] ?? '');
$konfirmasiPasswordBaru = trim($_POST['konfirmasi_password_baru'] ?? '');

if ($passwordLama === '' || $passwordBaru === '' || $konfirmasiPasswordBaru === '') {
    header("Location: /gerejakristuscibinong/ganti_pasword?error=Semua+field+password+wajib+diisi");
    exit();
}

if ($passwordBaru !== $konfirmasiPasswordBaru) {
    header("Location: /gerejakristuscibinong/ganti_pasword?error=Konfirmasi+password+baru+tidak+sesuai");
    exit();
}

if (strlen($passwordBaru) < 8) {
    header("Location: /gerejakristuscibinong/ganti_pasword?error=Password+baru+minimal+8+karakter");
    exit();
}

if ($passwordLama === $passwordBaru) {
    header("Location: /gerejakristuscibinong/ganti_pasword?error=Password+baru+harus+berbeda+dari+password+lama");
    exit();
}

$stmt = $conn->prepare("SELECT j_password FROM jemaat WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $jemaatId);
$stmt->execute();
$result = $stmt->get_result();
$jemaat = $result->fetch_assoc();
$stmt->close();

if (!$jemaat) {
    header("Location: /gerejakristuscibinong/ganti_pasword?error=Data+jemaat+tidak+ditemukan");
    exit();
}

if (!password_verify($passwordLama, $jemaat['j_password'])) {
    header("Location: /gerejakristuscibinong/ganti_pasword?error=Password+lama+tidak+sesuai");
    exit();
}

$passwordHash = password_hash($passwordBaru, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE jemaat SET j_password = ? WHERE id = ?");
$stmt->bind_param("si", $passwordHash, $jemaatId);
$isUpdated = $stmt->execute();
$stmt->close();

if (!$isUpdated) {
    header("Location: /gerejakristuscibinong/ganti_pasword?error=Gagal+mengubah+password");
    exit();
}

header("Location: /gerejakristuscibinong/ganti_pasword?success=Password+berhasil+diubah");
exit();
