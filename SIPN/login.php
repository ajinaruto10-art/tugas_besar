<?php
session_start();

// Jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: tabel2.php");
    exit;
}

// Ambil pesan error atau sukses dari URL jika ada
$error   = $_GET['error'] ?? '';
$sukses  = $_GET['sukses'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIPN SMK Sangkuriang 1 Cimahi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-login { max-width: 420px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="d-flex align-items-center vh-100">

<div class="container">
    <div class="card card-login mx-auto border-0">
        <div class="card-header bg-primary text-white text-center py-3" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
            <h4 class="mb-0 fw-bold"><i class="bi bi-mortarboard-fill me-2"></i>SIPN LOGIN</h4>
            <small>Sistem Informasi Pengelolaan Nilai</small>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center py-2 mb-3" role="alert">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($sukses)): ?>
                <div class="alert alert-success text-center py-2 mb-3" role="alert">
                    <?= htmlspecialchars($sukses); ?>
                </div>
            <?php endif; ?>

            <form action="aksi_login.php" method="POST">
                <!-- USERNAME -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>

                <!-- PASSWORD -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 py-2 mt-2 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sistem
                </button>
            </form>

            <!-- BAGIAN REGISTER KHUSUS SISWA -->
            <div class="text-center mt-3 border-top pt-3">
                <small class="text-muted">Siswa belum punya akun?</small><br>
                <a href="register.php" class="text-decoration-none fw-bold text-primary">
                    <i class="bi bi-person-plus-fill me-1"></i>Daftar Akun Siswa
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>