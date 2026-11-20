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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = mysqli_real_escape_string($koneksi, trim($_POST['id'] ?? ''));
    $nama_mapel = mysqli_real_escape_string($koneksi, trim($_POST['nama_mapel'] ?? ''));

    if (empty($id)) {
        echo "<script>alert('ID Mata Pelajaran tidak valid!'); window.location.href='mapel.php';</script>";
        exit;
    }

    if (empty($nama_mapel)) {
        echo "<script>alert('Nama Mata Pelajaran wajib diisi!'); window.history.back();</script>";
        exit;
    }

    // Sesuaikan nama kolom 'id' atau 'nama_mapel' jika di tabel mapel berbeda
    $query_update = "UPDATE mapel SET nama_mapel = '$nama_mapel' WHERE id = '$id'";
    $update = mysqli_query($koneksi, $query_update);

    if ($update) {
        echo "<script>alert('Nama mata pelajaran berhasil diperbarui!'); window.location.href='mapel.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
    }
} else {
    header("Location: mapel.php");
    exit;
}
?>