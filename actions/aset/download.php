<?php
$basePath = __DIR__ . '/../../assets/uploads/dokumen_file/';

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("File tidak valid");
}

// Amankan dari ../ (directory traversal)
$fileName = basename($_GET['file']);
$filePath = $basePath . $fileName;

// Cek file ada atau tidak
if (!file_exists($filePath)) {
    die("File tidak ditemukan");
}

// Ambil mime type
$mime = mime_content_type($filePath);

// Header untuk download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

// Output file
readfile($filePath);
exit;
