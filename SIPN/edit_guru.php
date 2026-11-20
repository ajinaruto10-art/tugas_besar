<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi hak akses admin
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil ID guru dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id)) {
    echo "<script>alert('ID guru tidak valid!'); window.location.href='daftar_guru.php';</script>";
    exit;
}

// Ambil data guru dan antisipasi jika users-nya NULL menggunakan LEFT JOIN & COALESCE
$query = mysqli_query($koneksi, "SELECT guru.*, COALESCE(users.username, '') AS username FROM guru LEFT JOIN users ON guru.user_id = users.id WHERE guru.id = '$id' LIMIT 1");
$guru = mysqli_fetch_assoc($query);

if (!$guru) {
    echo "<script>alert('Data guru tidak ditemukan!'); window.location.href='daftar_guru.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Guru - SIPN Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Panggil Navbar Pusat -->
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Guru & Akun</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="aksi_edit_guru.php" method="POST">
                        <!-- Kirim ID guru dan user_id secara tersembunyi -->
                        <input type="hidden" name="id" value="<?= $guru['id']; ?>">
                        <input type="hidden" name="user_id" value="<?= $guru['user_id'] ?? ''; ?>">

                        <div class="mb-3">
                            <label for="nip" class="form-label fw-semibold">NIP / Kode Guru</label>
                            <input type="text" class="form-control" id="nip" name="nip" value="<?= htmlspecialchars($guru['nip'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label fw-semibold">Nama Lengkap & Gelar</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($guru['nama'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="mapel" class="form-label fw-semibold">Mata Pelajaran</label>
                            <input type="text" class="form-control" id="mapel" name="mapel" value="<?= htmlspecialchars($guru['mapel'] ?? ''); ?>" required>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-shield-lock me-1"></i> Akun Login Guru</h6>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username Login</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($guru['username'] ?? ''); ?>" placeholder="Masukkan username login..." required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password Baru <span class="text-muted fw-normal small">(Kosongkan jika tidak ingin mengubah password)</span></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password baru...">
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="daftar_guru.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" name="update" class="btn btn-dark btn-sm px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>