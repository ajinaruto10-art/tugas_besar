<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek hak akses admin
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (!empty($id)) {
    // Jalankan perintah hapus data secara native
    $query = mysqli_query($koneksi, "DELETE FROM users WHERE id = '$id'");

    if ($query) {
        echo "<script>alert('Pengguna berhasil dihapus!'); window.location.href='daftar_user2.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus pengguna: " . mysqli_error($koneksi) . "'); window.location.href='daftar_user2.php';</script>";
    }
} else {
    header("Location: daftar_user2.php");
    exit;
}
?>