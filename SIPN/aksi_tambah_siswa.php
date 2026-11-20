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

// Pastikan data dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username      = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password      = mysqli_real_escape_string($koneksi, trim($_POST['password']));
    $nama          = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $nis           = mysqli_real_escape_string($koneksi, trim($_POST['nis']));
    $kelas         = mysqli_real_escape_string($koneksi, trim($_POST['kelas']));
    $jenis_kelamin = mysqli_real_escape_string($koneksi, trim($_POST['jenis_kelamin']));
    $role          = 'siswa';

    // Validasi data kosong
    if (empty($username) || empty($password) || empty($nama) || empty($nis)) {
        echo "<script>alert('Username, Password, Nama, dan NIS wajib diisi!'); window.history.back();</script>";
        exit;
    }

    // 1. Masukkan data ke tabel USERS terlebih dahulu agar langsung muncul di Daftar Pengguna
    $query_user = "INSERT INTO users (username, password, nama, jenis_kelamin, role) 
                   VALUES ('$username', '$password', '$nama', '$jenis_kelamin', '$role')";
    $insert_user = mysqli_query($koneksi, $query_user);

    if ($insert_user) {
        // Ambil ID user yang baru saja dibuat untuk relasi ke tabel siswa
        $user_id_baru = mysqli_insert_id($koneksi);

        // 2. Masukkan data ke tabel SISWA dengan membawa user_id
        $query_siswa = "INSERT INTO siswa (user_id, nis, nama, kelas) 
                        VALUES ('$user_id_baru', '$nis', '$nama', '$kelas')";
        $insert_siswa = mysqli_query($koneksi, $query_siswa);

        if ($insert_siswa) {
            echo "<script>alert('Siswa baru berhasil ditambahkan dan akun pengguna telah dibuat!'); window.location.href='tabel_siswa.php';</script>";
        } else {
            // Jika tabel siswa gagal, hapus user yang terlanjur dibuat agar database bersih
            mysqli_query($koneksi, "DELETE FROM users WHERE id = '$user_id_baru'");
            echo "<script>alert('Gagal menyimpan data siswa: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Gagal membuat akun pengguna (Username mungkin sudah digunakan): " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
} else {
    // Jika diakses langsung tanpa lewat form submit
    header("Location: tabel_siswa.php");
    exit;
}
?>