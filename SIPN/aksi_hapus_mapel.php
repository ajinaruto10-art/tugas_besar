<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (isset($_POST['update'])) {
    if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
        header("Location: login.php");
        exit;
    }

    $id         = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nama_mapel = mysqli_real_escape_string($koneksi, trim($_POST['nama_mapel']));

    if (empty($nama_mapel)) {
        echo "<script>alert('Nama mapel tidak boleh kosong!'); window.history.back();</script>";
        exit;
    }

    $update = mysqli_query($koneksi, "UPDATE mapel SET nama_mapel = '$nama_mapel' WHERE id = '$id'");

    if ($update) {
        echo "<script>alert('Mata pelajaran berhasil diperbarui!'); window.location.href='tabel_mapel.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui mapel: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
} else {
    header("Location: tabel_mapel.php");
    exit;
}
?>