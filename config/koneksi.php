<?php
$HOSTNAME = 'localhost';
$USERNAME = 'root';
$PASSWORD = 'Yahya123#';
$DATABASE = 'gereja_kristus_cibinong';

$conn = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);
if (!$conn) {
    die('Koneksi database gagal.');
}
