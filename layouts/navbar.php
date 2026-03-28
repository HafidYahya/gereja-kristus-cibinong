<div class="content flex-grow-1">
    <nav class="topbar navbar navbar-expand-lg navbar-light">
        <div class="container-fluid px-3 shadow-md">
            <button id="sidebar-toggle" class="btn btn-md sidebar-toggle" type="button" aria-label="Toggle sidebar"
                aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <span
                class="identitas-user d-inline-block ms-auto me-2 text-muted"><?= $_SESSION['user'] ?? 'Admin' ?></span>
            <span class="logout d-inline-block me-2"><a class="btn btn-sm btn-danger text-white fw-semibold"
                    href="logout.php"><i class="fas fa-arrow-right-from-bracket me-2"></i> Log out</a></span>

        </div>
    </nav>
    <div class="content-body">
