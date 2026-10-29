<?php
// Nyalakan error sementara untuk melacak jika ada kendala
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
$username_login = $_SESSION['username'] ?? '';

// Ambil data user dari database dengan aman
$q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username_login' LIMIT 1");
$d_user = ($q_user) ? mysqli_fetch_assoc($q_user) : null;
$user_id_val = $d_user['id'] ?? $d_user['ID'] ?? '';

// Jika role guru, ambil data detail dari tabel guru secara fleksibel
$detail_guru = null;
if ($role === 'guru' && !empty($user_id_val)) {
    $q_guru = mysqli_query($koneksi, "SELECT * FROM guru WHERE uer_id = '$user_id_val' LIMIT 1");
    if ($q_guru && mysqli_num_rows($q_guru) > 0) {
        $detail_guru = mysqli_fetch_assoc($q_guru);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #fff; }
        .top-navbar {
            border-bottom: 1px solid #dee2e6;
            padding: 12px 24px;
            background-color: #fff;
        }
        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<div class="top-navbar d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-4">
        <span class="fw-normal text-dark">Navbar</span>
        <a href="tabel2.php?page=nilai" class="text-decoration-none text-dark">Nilai Siswa</a>
        <?php if ($role === 'admin' || $role === 'guru'): ?>
            <a href="tabel2.php?page=guru" class="text-decoration-none text-dark">Daftar Guru</a>
        <?php endif; ?>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-link text-dark text-decoration-none dropdown-toggle p-0 shadow-none" type="button" data-bs-toggle="dropdown">
                <?= htmlspecialchars($username_login); ?> ( <?= htmlspecialchars($role); ?> )
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item fw-bold text-primary" href="pengaturan.php">Pengaturan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="container my-4" style="max-width: 600px;">
    <div class="card card-custom p-4">
        <h3 class="fw-normal text-dark mb-4 text-center">Pengaturan Akun & Profil</h3>
        
        <!-- Bagian Profil -->
        <div class="mb-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-person-badge me-2"></i>Informasi Profil</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between py-3">
                    <span class="text-muted">Username</span>
                    <span class="fw-semibold"><?= htmlspecialchars($d_user['username'] ?? $username_login); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3">
                    <span class="text-muted">Role / Hak Akses</span>
                    <span class="badge bg-primary text-uppercase px-3 py-2"><?= htmlspecialchars($role); ?></span>
                </li>
                <?php if ($role === 'guru' && $detail_guru): ?>
                    <li class="list-group-item d-flex justify-content-between py-3">
                        <span class="text-muted">Nama Lengkap & Gelar</span>
                        <span class="fw-semibold"><?= htmlspecialchars($detail_guru['nama'] ?? '-'); ?></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Tombol Kembali & Logout -->
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <a href="tabel2.php" class="btn btn-secondary px-4">Kembali</a>
            <a href="logout.php" class="btn btn-danger px-4 fw-semibold"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>