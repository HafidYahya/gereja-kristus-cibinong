<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";

if (!isset($_SESSION['jemaat_id']) || empty($_SESSION['jemaat_id'])) {
    header("Location: /gerejakristuscibinong/login?error=Silahkan+login+terlebih+dahulu");
    exit();
}

$jemaatId = (int) $_SESSION['jemaat_id'];
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nama = trim($_POST['nama'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');
$email = trim($_POST['email'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

if ($id <= 0 || $id !== $jemaatId) {
    header("Location: /gerejakristuscibinong/profile?error=Data+tidak+valid");
    exit();
}

if ($nama === '' || $noHp === '' || $email === '' || $alamat === '') {
    header("Location: /gerejakristuscibinong/profile?error=Data+tidak+boleh+kosong");
    exit();
}

// Ambil data foto lama
$stmt = $conn->prepare("SELECT j_foto FROM jemaat WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $jemaatId);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();
$stmt->close();

if (!$currentUser) {
    header("Location: /gerejakristuscibinong/profile?error=Data+pengguna+tidak+ditemukan");
    exit();
}

// Cek email unik
$stmt = $conn->prepare("SELECT id FROM jemaat WHERE j_email = ? AND id <> ? LIMIT 1");
$stmt->bind_param("si", $email, $jemaatId);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

if ($exists) {
    header("Location: /gerejakristuscibinong/profile?error=Email+sudah+terdaftar");
    exit();
}

// Cek nomor HP unik
$stmt = $conn->prepare("SELECT id FROM jemaat WHERE j_no_hp = ? AND id <> ? LIMIT 1");
$stmt->bind_param("si", $noHp, $jemaatId);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

if ($exists) {
    header("Location: /gerejakristuscibinong/profile?error=Nomor+handphone+sudah+terdaftar");
    exit();
}

$fotoName = $currentUser['j_foto'] ?? 'profile-default.jpg';

if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] !== UPLOAD_ERR_NO_FILE) {
    $fotoFile = $_FILES['foto_profile'];
    $allowedExt = ['jpg', 'jpeg', 'png'];

    if ($fotoFile['error'] !== UPLOAD_ERR_OK) {
        header("Location: /gerejakristuscibinong/profile?error=Gagal+upload+foto+profile");
        exit();
    }

    if ($fotoFile['size'] > 2 * 1024 * 1024) {
        header("Location: /gerejakristuscibinong/profile?error=Ukuran+foto+profile+terlalu+besar");
        exit();
    }

    $ext = strtolower(pathinfo($fotoFile['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        header("Location: /gerejakristuscibinong/profile?error=Format+foto+profile+tidak+didukung");
        exit();
    }

    $newFileName = uniqid('profile_', true) . '.' . $ext;
    $uploadPath = __DIR__ . '/../../assets/uploads/jemaat/' . $newFileName;

    if (!move_uploaded_file($fotoFile['tmp_name'], $uploadPath)) {
        header("Location: /gerejakristuscibinong/profile?error=Gagal+menyimpan+foto+profile");
        exit();
    }

    $fotoName = $newFileName;
}

$stmt = $conn->prepare("UPDATE jemaat SET j_nama = ?, j_no_hp = ?, j_email = ?, j_alamat = ?, j_foto = ? WHERE id = ?");
$stmt->bind_param("sssssi", $nama, $noHp, $email, $alamat, $fotoName, $jemaatId);
$isUpdated = $stmt->execute();
$stmt->close();

if (!$isUpdated) {
    header("Location: /gerejakristuscibinong/profile?error=Gagal+memperbarui+profil");
    exit();
}

$_SESSION['jemaat_name'] = $nama;
$_SESSION['jemaat_email'] = $email;
$_SESSION['jemaat_no_hp'] = $noHp;
$_SESSION['jemaat_alamat'] = $alamat;
$_SESSION['jemaat_foto'] = $fotoName;

header("Location: /gerejakristuscibinong/profile?success=Profil+berhasil+diperbarui");
exit();
