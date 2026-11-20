<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek hak akses admin
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
    <title>Tambah Siswa - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <h3 class="fw-bold mb-3"><i class="bi bi-person-plus-fill me-2"></i>Tambah Siswa Baru</h3>
                <p class="text-muted mb-4">Menambahkan siswa baru akan otomatis mendaftarkannya ke menu Daftar Pengguna (Users).</p>

                <!-- Form mengarah ke file aksi terpisah -->
                <form action="aksi_tambah_siswa.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label fw-semibold">Username Login</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Buat username..." required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="text" class="form-control" id="password" name="password" placeholder="Buat password..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap siswa..." required>
                        <div class="form-text text-muted">Nama ini akan otomatis muncul di tabel Daftar Pengguna.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nis" class="form-label fw-semibold">NIS (Nomor Induk Siswa)</label>
                            <input type="text" class="form-control" id="nis" name="nis" placeholder="Nomor induk siswa..." required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kelas" class="form-label fw-semibold">Kelas</label>
                            <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Contoh: X PPLG 4">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="tabel_siswa.php" class="btn btn-secondary fw-semibold px-4">Kembali</a>
                        <button type="submit" name="simpan_siswa" class="btn btn-dark fw-semibold px-4">Simpan Data Siswa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>