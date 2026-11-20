<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah tombol simpan ditekan dan user adalah admin
if (isset($_POST['simpan'])) {
    if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
        header("Location: login.php");
        exit;
    }

    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = $_POST['password'];
    $role     = 'admin'; // Paksa role menjadi admin

    // Validasi input tidak boleh kosong
    if (empty($username) || empty($password)) {
        echo "<script>alert('Username dan password tidak boleh kosong!'); window.history.back();</script>";
        exit;
    }

    // Cek apakah username sudah terdaftar sebelumnya
    $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' LIMIT 1");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan, silakan pilih username lain!'); window.history.back();</script>";
        exit;
    }

    // Enskripsi password menggunakan password_hash agar aman
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database tabel users
    $query_insert = mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES ('$username', '$password_hashed', '$role')");

   if ($query_insert) {
        // Langsung arahkan kembali ke halaman daftar user (Home Admin)
        echo "<script>alert('Admin baru berhasil ditambahkan!'); window.location.href='daftar_user2.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan admin: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
} else {
    header("Location: tambah_admin.php");
    exit;
}
?>