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

$mapel_id = mysqli_real_escape_string($koneksi, trim($_POST['mapel_id'] ?? ''));
$id_asal  = mysqli_real_escape_string($koneksi, trim($_POST['id_asal'] ?? ''));
$kelas    = mysqli_real_escape_string($koneksi, trim($_POST['kelas'] ?? ''));
$guru_id  = mysqli_real_escape_string($koneksi, trim($_POST['guru_id'] ?? ''));

if ($kelas === '' || $guru_id === '') {
    echo "<script>alert('Pilih Kelas dan Pilih Guru wajib diisi!'); window.history.back();</script>";
    exit;
}

if ($mapel_id === '' && $id_asal !== '') {
    $q_cek_mg = mysqli_query($koneksi, "SELECT mapel_id FROM mengajar WHERE id = '$id_asal' LIMIT 1");
    if ($q_cek_mg && $rmg = mysqli_fetch_assoc($q_cek_mg)) {
        $mapel_id = mysqli_real_escape_string($koneksi, (string)($rmg['mapel_id'] ?? ''));
    }
}

if ($mapel_id === '') {
    echo "<script>alert('ID data mengajar tidak valid!'); window.location.href='daftar_mengajar.php';</script>";
    exit;
}

$update_mapel = mysqli_query(
    $koneksi,
    "UPDATE mapel SET id_guru = '$guru_id', kelas = '$kelas' WHERE ID = '$mapel_id'"
);

if (!$update_mapel) {
    echo "<script>alert('Gagal memperbarui data mengajar: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
    exit;
}

$res_siswa = mysqli_query($koneksi, "SELECT ID FROM siswa WHERE kelas = '$kelas'");
if ($res_siswa && mysqli_num_rows($res_siswa) > 0) {
    mysqli_query($koneksi, "DELETE FROM mengajar WHERE mapel_id = '$mapel_id'");
    while ($s = mysqli_fetch_assoc($res_siswa)) {
        $id_siswa = mysqli_real_escape_string($koneksi, (string)$s['ID']);
        if ($id_siswa !== '') {
            mysqli_query($koneksi, "INSERT INTO mengajar (mapel_id, siswa_id) VALUES ('$mapel_id', '$id_siswa')");
        }
    }
}

echo "<script>alert('Data mengajar berhasil diperbarui!'); window.location.href='daftar_mengajar.php';</script>";