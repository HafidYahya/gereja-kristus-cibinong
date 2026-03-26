<?php
session_start();
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header('Location: index.php?page=dashboard');
    exit();
}
include __DIR__ . '/config/koneksi.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare('SELECT * FROM users WHERE u_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row['u_is_active'] == 0) {
            header('Location: login.php?login&error=Akun+anda+tidak+aktif');
            exit();
        }
        if (password_verify($password, $row['u_password'])) {
            session_regenerate_id(true);
            $_SESSION['id'] = $row['id'];
            $_SESSION['user'] = $row['u_nama'];
            header('Location: index.php?page=dashboard');
            exit();
        }

        header('Location: login.php?login&error=Password+salah');
        exit();
    }

    header('Location: login.php?login&error=Email+tidak+terdaftar');
    exit();
}

$error = $_GET['error'] ?? '';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="icon" href="assets/img/logo_gkc.png" type="image/png">
    <title>GKC LOGIN</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">


</head>

<body>
    <div class="container-fluid vh-100">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="card">
                <div class="card-header ">
                    <div class="d-flex align-items-baseline justify-content-start mb-2">
                        <img src="assets/img/logo_gkc.png" alt="GKC Logo" width="30">
                        <h5 class="card-title ms-2">GKC LOGIN</h5>
                    </div>
                    <p class="card-text text-start">Please login to your account.</p>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label text-light">Email*</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukan email"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-light">Password*</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Masukan password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="showPassword" name="showPassword">
                            <label class="form-check-label text-light" for="showPassword">Show Password</label>
                        </div>
                        <button type="submit" class="btn btn-login w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>
    <script>
        const showPasswordCheckbox = document.getElementById('showPassword');
        const passwordInput = document.getElementById('password');

        showPasswordCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
    <!-- Alert Login Gagal -->
    <?php if ($error !== '') : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: '<?= htmlspecialchars($error) ?>',
                confirmButtonText: 'OK'
            });
        </script>
    <?php endif; ?>
</body>

</html>
