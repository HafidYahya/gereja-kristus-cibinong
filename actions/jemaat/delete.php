<?php
include __DIR__ . "/../../config/koneksi.php";

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location:../../admin/index.php?page=jemaat&action=delete&error=Data+tidak+valid');
    exit();
}

$stmt = $conn->prepare('DELETE FROM jemaat WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();


header('Location:../../admin/index.php?page=jemaat&action=delete&success=Data+berhasil+dihapus');
