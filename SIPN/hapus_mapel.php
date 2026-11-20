<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id)) {
    echo "<script>alert('ID tidak valid!'); window.location.href='mapel.php';</script>";
    exit;
}

$hapus = mysqli_query($koneksi, "DELETE FROM mapel WHERE id = '$id'");

if ($hapus) {
    echo "<script>alert('Mata pelajaran berhasil dihapus!'); window.location.href='mapel.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus mapel: " . mysqli_error($koneksi) . "'); window.location.href='tabel_mapel.php';</script>";
}
?>