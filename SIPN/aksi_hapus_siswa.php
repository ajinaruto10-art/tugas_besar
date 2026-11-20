<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi admin
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id)) {
    echo "<script>alert('ID siswa tidak valid!'); window.location.href='tabel_siswa.php';</script>";
    exit;
}

// Ambil user_id terlebih dahulu sebelum data siswa dihapus
$query_cek = mysqli_query($koneksi, "SELECT user_id FROM siswa WHERE id = '$id' LIMIT 1");
if ($data = mysqli_fetch_assoc($query_cek)) {
    $user_id = $data['user_id'];

    // 1. Hapus data dari tabel siswa
    $hapus_siswa = mysqli_query($koneksi, "DELETE FROM siswa WHERE id = '$id'");

    if ($hapus_siswa) {
        // 2. Hapus juga akun login terkait di tabel users (jika ada user_id-nya)
        if (!empty($user_id)) {
            mysqli_query($koneksi, "DELETE FROM users WHERE id = '$user_id'");
        }

        echo "<script>alert('Data siswa berhasil dihapus!'); window.location.href='tabel_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data siswa: " . mysqli_error($koneksi) . "'); window.location.href='tabel_siswa.php';</script>";
    }
} else {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location.href='tabel_siswa.php';</script>";
}
?>