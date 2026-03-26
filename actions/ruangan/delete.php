<?php
include __DIR__ . "/../../config/koneksi.php";

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location:../../index.php?page=ruangan&action=delete&error=Data+tidak+valid');
    exit();
}

$stmt = $conn->prepare('SELECT r_foto FROM ruangan WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$stmt->close();

$stmt = $conn->prepare('DELETE FROM ruangan WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

if ($row && !empty($row['r_foto']) && $row['r_foto'] !== 'placeholder_ruangan') {
    $filePath = __DIR__ . '/../../assets/uploads/ruangan/' . $row['r_foto'];
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

header('Location:../../index.php?page=ruangan&action=delete&success=Data+berhasil+dihapus');
