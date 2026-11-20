<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Validasi login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = strtolower(trim($_SESSION['role'] ?? ''));
$username = $_SESSION['username'];

// Ambil data tambahan dari database jika diperlukan (misalnya info akun)
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id' LIMIT 1");
$data_user = mysqli_fetch_assoc($query_user);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- PANGGIL NAVBAR PUSAT AGAR SERAGAM DENGAN HOME -->
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-circle me-2"></i>Informasi Profil Akun</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="text-center mb-4">
                        <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-person"></i>
                        </div>
                        <h4 class="fw-bold mt-3 mb-1"><?= htmlspecialchars($username); ?></h4>
                        <span class="badge bg-primary text-uppercase px-3 py-1"><?= htmlspecialchars($role); ?></span>
                    </div>

                    <hr class="my-4">

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="fw-semibold text-muted"><i class="bi bi-shield-lock me-2"></i>ID Pengguna</span>
                            <span class="fw-bold">#<?= $data_user['id'] ?? $user_id; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="fw-semibold text-muted"><i class="bi bi-person-badge me-2"></i>Username</span>
                            <span class="fw-bold"><?= htmlspecialchars($data_user['username'] ?? $username); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="fw-semibold text-muted"><i class="bi bi-briefcase me-2"></i>Hak Akses (Role)</span>
                            <span class="fw-bold text-capitalize"><?= htmlspecialchars($data_user['role'] ?? $role); ?></span>
                        </li>
                    </ul>

                    <div class="d-grid">
                        <a href="daftar_user2.php" class="btn btn-dark fw-semibold py-2">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda (Home)
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>