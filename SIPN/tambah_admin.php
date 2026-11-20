<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi hanya admin yang boleh mengakses halaman ini
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin - SIPN Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Panggil Navbar Pusat -->
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Tambah Akun Admin Baru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Form Pengisian Data Admin -->
                    <form action="aksi_tambah_Admin.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username Admin</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username baru..." required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password..." required>
                        </div>

                        <div class="mb-4">
                            <label for="role" class="form-label fw-semibold">Role / Hak Akses</label>
                            <input type="text" class="form-control bg-light" id="role" name="role" value="admin" readonly>
                            <div class="form-text text-muted">Role otomatis diatur sebagai admin.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="daftar_user2.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" name="simpan" class="btn btn-dark btn-sm px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Admin
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>