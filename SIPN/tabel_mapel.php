<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role_user = strtolower(trim($_SESSION['role'] ?? ''));
$nama_user = $_SESSION['nama'] ?? ($_SESSION['username'] ?? 'User');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-mortarboard-fill me-2"></i>SIPN - SMK Sangkuriang 1
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSIPN" aria-controls="navbarSIPN" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSIPN">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>

                <?php if ($role_user === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="daftar_user2.php">Daftar User</a></li>
                            <li><a class="dropdown-item" href="tambah_user.php">Tambah User</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Siswa
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="daftar_siswa.php">Daftar Siswa</a></li>
                            <li><a class="dropdown-item" href="tambah_siswa.php">Tambah Siswa</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Guru
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="daftar_guru.php">Daftar Guru</a></li>
                            <li><a class="dropdown-item" href="tambah_guru.php">Tambah Guru</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <!-- Ubah href di bawah ini ke nama file daftar mapel kamu (misalnya mapel.php atau daftar_mapel.php) -->
                        <a class="nav-link dropdown-toggle" href="mapel.php" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mapel
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="mapel.php">Daftar Mapel</a></li>
                            <li><a class="dropdown-item" href="aksi_tambah_mapel.php">Tambah Mapel</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mengajar
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="mengajar.php">Daftar Mengajar</a></li>
                            <li><a class="dropdown-item" href="tambah_mengajar.php">Tambah Mengajar</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear-fill me-1"></i><?= htmlspecialchars($nama_user); ?> (<?= ucfirst($role_user ?: 'Guest'); ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>