<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah sudah login dan rolenya guru
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'guru') {
    header("Location: login.php");
    exit;
}

$username_login = $_SESSION['username'] ?? '';
$username_aman  = mysqli_real_escape_string($koneksi, $username_login);

// 1. Ambil ID dari tabel users
$q_user = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username_aman' LIMIT 1");
$d_user = $q_user ? mysqli_fetch_assoc($q_user) : null;
$user_id_login = $d_user['id'] ?? 0;

// 2. Ambil ID Guru dari tabel guru berdasarkan uer_id
$q_guru = mysqli_query($koneksi, "SELECT id FROM guru WHERE uer_id = '$user_id_login' LIMIT 1");
$d_guru = $q_guru ? mysqli_fetch_assoc($q_guru) : null;
$id_guru_login = $d_guru['id'] ?? 0;

// Jika data guru tidak ditemukan
if (empty($id_guru_login)) {
    echo "<script>alert('Error: Profil guru untuk akun ini belum terdaftar di tabel guru!'); window.location='tabel2.php';</script>";
    exit;
}

// Proses Simpan Mata Pelajaran Baru
if (isset($_POST['submit'])) {
    $kode_mapel = mysqli_real_escape_string($koneksi, $_POST['kode_mapel'] ?? '');
    $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel'] ?? '');

    if (empty($kode_mapel) || empty($nama_mapel)) {
        echo "<script>alert('Kode dan Nama Mata Pelajaran wajib diisi!'); window.history.back();</script>";
        exit;
    }

    // Masukkan ke tabel mapel dengan guru_id otomatis mengarah ke ID guru yang sedang login
    $query_insert = "INSERT INTO mapel (kode_mapel, nama_mapel, guru_id) VALUES ('$kode_mapel', '$nama_mapel', '$id_guru_login')";
    $execute = mysqli_query($koneksi, $query_insert);

    if ($execute) {
        echo "<script>alert('Mata pelajaran berhasil ditambahkan!'); window.location='tambah_nilai.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambah mapel: " . addslashes(mysqli_error($koneksi)) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mata Pelajaran - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR UTAMA GURU -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="tabel2.php"><i class="bi bi-mortarboard-fill me-2"></i>SIPN - Panel Guru</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="tabel2.php"><i class="bi bi-house-door me-1"></i> Data Nilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tambah_nilai.php"><i class="bi bi-plus-circle me-1"></i> Input Nilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="tambah_mapel.php"><i class="bi bi-journal-plus me-1"></i> Tambah Mapel Saya</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0 fw-bold"><i class="bi bi-journal-plus me-2"></i>Tambah Mata Pelajaran Baru</h4>
            <small class="text-white-50">Mata pelajaran ini akan otomatis masuk ke daftar ajar Anda.</small>
        </div>
        <div class="card-body p-4">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kode Mata Pelajaran</label>
                    <input type="text" name="kode_mapel" class="form-control" required placeholder="Contoh: MTK-10">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" class="form-control" required placeholder="Contoh: Matematika Lanjutan">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="tambah_nilai.php" class="btn btn-secondary px-4">Kembali</a>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>