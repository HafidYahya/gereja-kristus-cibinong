<?php
// Hapus session admin + jemaat dengan unset (tanpa session_destroy).
unset(
    $_SESSION['jemaat_id'],
    $_SESSION['jemaat_name'],
    $_SESSION['jemaat_email'],
    $_SESSION['jemaat_no_hp'],
    $_SESSION['jemaat_alamat'],
    $_SESSION['jemaat_foto']
);

header("Location: login?success=Logout+berhasil");
exit();
