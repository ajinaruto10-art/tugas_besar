<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: daftar_mengajar.php");
    exit;
}

$mapel_id_param = trim($_POST['mapel_id'] ?? '');
$id_asal_param  = trim($_POST['id_asal'] ?? '');

$target_mapel_id = '';
if ($mapel_id_param !== '') {
    $target_mapel_id = mysqli_real_escape_string($koneksi, $mapel_id_param);
} elseif ($id_asal_param !== '') {
    $id_esc = mysqli_real_escape_string($koneksi, $id_asal_param);
    $q_mg = mysqli_query($koneksi, "SELECT mapel_id FROM mengajar WHERE id = '$id_esc' LIMIT 1");
    if ($q_mg && $rmg = mysqli_fetch_assoc($q_mg)) {
        $target_mapel_id = mysqli_real_escape_string($koneksi, (string)($rmg['mapel_id'] ?? ''));
    }
    if ($target_mapel_id === '') {
        $q_m = mysqli_query($koneksi, "SELECT ID FROM mapel WHERE ID = '$id_esc' LIMIT 1");
        if ($q_m && $rm = mysqli_fetch_assoc($q_m)) {
            $target_mapel_id = mysqli_real_escape_string($koneksi, (string)($rm['ID'] ?? ''));
        }
    }
}

if ($target_mapel_id === '') {
    echo "<script>alert('Data mengajar tidak ditemukan!'); window.location.href='daftar_mengajar.php';</script>";
    exit;
}

$hapus_relasi = mysqli_query($koneksi, "DELETE FROM mengajar WHERE mapel_id = '$target_mapel_id'");
$update_mapel = mysqli_query($koneksi, "UPDATE mapel SET kelas = NULL, id_guru = NULL WHERE ID = '$target_mapel_id'");

if ($hapus_relasi && $update_mapel) {
    echo "<script>alert('Data mengajar berhasil dihapus!'); window.location.href='daftar_mengajar.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data mengajar: " . addslashes(mysqli_error($koneksi)) . "'); window.location.href='daftar_mengajar.php';</script>";
}