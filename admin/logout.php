<?php
// End user session and redirect to login
session_start();

// Clear admin + jemaat session keys with unset (tanpa session_destroy).
unset(
    $_SESSION['id'],
    $_SESSION['user'],
);

header('Location: login.php');
exit();
